<script setup>
import { computed, ref, onMounted, onBeforeUnmount, watch } from 'vue'
import ThreadView from './ThreadView.vue'
import VideoCallModal from './VideoCallModal.vue'
const showVideoCall = ref(false)

function openVideoCall() {
  showVideoCall.value = true
}
function closeVideoCall() {
  showVideoCall.value = false
}
const props = defineProps({
  context: { type: Object, default: null }
})

const messages = ref([])
let subscription = null

const showThread = ref(false)
const threadMessageId = ref(null)

function openThread(id) {
  threadMessageId.value = id
  showThread.value = true
}
function closeThread() {
  showThread.value = false
  threadMessageId.value = null
}

function setupContext() {
  messages.value = (props.context?.messages ?? []).slice().sort((a, b) => a.id - b.id)
  if (subscription) {
    try { subscription.stopListening('.NewMessageSent') } catch (e) {}
    subscription = null
  }
  if (props.context?.id && props.context?.type && window.Echo) {
    const channelName = props.context.type === 'dm' 
      ? `dm.${props.context.id}` 
      : `channel.${props.context.id}`
    subscription = window.Echo.private(channelName)
      .listen('.NewMessageSent', (e) => {
        if (e?.message) {
          messages.value = [...messages.value, e.message]
        }
      })
  }
}

onMounted(setupContext)
watch(() => props.context?.id, setupContext)
watch(() => props.context?.type, setupContext)
onBeforeUnmount(() => {
  if (subscription) {
    try { subscription.stopListening('.NewMessageSent') } catch (e) {}
    subscription = null
  }
})

// Mapear cantidad de replies por mensaje padre
const replyCounts = computed(() => {
  const counts = new Map()
  for (const m of messages.value) {
    if (m && m.parent_message_id) {
      counts.set(m.parent_message_id, (counts.get(m.parent_message_id) || 0) + 1)
    }
  }
  return counts
})
</script>

<template>
  <div class="overflow-y-auto border rounded p-3 h-[50vh] bg-gray-50">
    <template v-if="context">
      <h3 class="text-sm font-semibold text-gray-700 mb-3">
        <span v-if="context.type === 'channel'"># {{ context.name }}</span>
        <span v-else>@ {{ context.name }}</span>
        <button @click="openVideoCall" class="ml-4 px-3 py-1 text-xs rounded bg-indigo-100 text-indigo-700 hover:bg-indigo-200">Start Video Call</button>
      </h3>
  <VideoCallModal v-if="showVideoCall" :on-close="closeVideoCall" :context="context" />
      <div v-if="messages.length === 0" class="text-gray-500 text-sm">
        No messages yet. Be the first to write!
      </div>
      <ul class="space-y-2">
        <li v-for="m in messages" :key="m.id">
          <!-- Mensaje padre -->
          <div v-if="!m.parent_message_id" class="text-sm">
            <div class="flex items-center gap-2">
              <span class="font-medium text-gray-800">{{ m.user?.name ?? 'User' }}</span>
              <span class="text-gray-400"> · </span>
              <span class="text-gray-700">{{ m.body }}</span>
              <button @click="openThread(m.id)" class="ml-2 text-xs text-indigo-600 hover:underline">Reply in thread</button>
              <span v-if="replyCounts.get(m.id)" class="ml-2 inline-flex items-center text-[11px] px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                {{ replyCounts.get(m.id) }} replies
              </span>
            </div>
          </div>

          <!-- Reply (child message) -->
          <div v-else class="text-xs text-gray-600 pl-4 border-l-2 border-indigo-100 ml-4">
            <div class="flex items-center gap-2">
              <span class="font-medium text-gray-700">↳ {{ m.user?.name ?? 'User' }}</span>
              <span class="text-gray-400"> · </span>
              <span>{{ m.body }}</span>
              <button @click="openThread(m.parent_message_id)" class="ml-2 text-[11px] text-indigo-600 hover:underline">View thread</button>
            </div>
          </div>
        </li>
      </ul>
      <ThreadView v-if="showThread" :message-id="threadMessageId" :on-close="closeThread" />
    </template>
    <template v-else>
      <div class="text-gray-500 text-sm">Selecciona un canal o conversación.</div>
    </template>
  </div>
</template>
