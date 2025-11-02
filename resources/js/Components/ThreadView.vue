<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  messageId: { type: Number, required: true },
  onClose: { type: Function, default: null }
})

const thread = ref(null)
const replyBody = ref('')
const loading = ref(true)
const error = ref(null)

async function fetchThread() {
  loading.value = true
  error.value = null
  try {
    const res = await axios.get(`/threads/${props.messageId}`)
    thread.value = res.data
  } catch (e) {
  error.value = 'Could not load thread.'
  } finally {
    loading.value = false
  }
}

async function sendReply() {
  if (!replyBody.value.trim()) return
  try {
    const res = await axios.post(`/threads/${props.messageId}/reply`, { body: replyBody.value })
    thread.value.replies.push(res.data)
    replyBody.value = ''
  } catch (e) {
  alert('Could not send reply')
  }
}

onMounted(fetchThread)
</script>

<template>
  <div class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
    <div class="bg-white rounded shadow-lg w-full max-w-lg p-6 relative">
      <button @click="onClose && onClose()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700">✕</button>
      <h3 class="text-lg font-bold mb-2">Thread</h3>
      <div v-if="loading" class="text-gray-500">Loading...</div>
      <div v-else-if="error" class="text-red-500">{{ error }}</div>
      <div v-else>
        <div class="mb-4 p-3 bg-gray-100 rounded">
          <div class="font-semibold">{{ thread.user?.name ?? 'User' }}</div>
          <div class="text-gray-700">{{ thread.body }}</div>
        </div>
        <div class="mb-2 text-sm text-gray-500">Replies</div>
        <ul class="space-y-2 max-h-48 overflow-y-auto mb-4">
          <li v-for="r in thread.replies" :key="r.id" class="p-2 bg-gray-50 rounded">
            <span class="font-medium">{{ r.user?.name ?? 'User' }}</span>:
            <span>{{ r.body }}</span>
          </li>
        </ul>
        <form @submit.prevent="sendReply" class="flex gap-2">
          <input v-model="replyBody" type="text" placeholder="Reply..." class="flex-1 border rounded px-2 py-1" />
          <button type="submit" class="bg-indigo-600 text-white px-3 py-1 rounded">Send</button>
        </form>
      </div>
    </div>
  </div>
</template>
