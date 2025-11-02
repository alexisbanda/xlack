<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  onClose: { type: Function, default: null },
  context: { type: Object, default: null }
})

const connected = ref(false)
const muted = ref(false)
const camOff = ref(false)
const seconds = ref(0)
let timer = null

function toggleMute() { muted.value = !muted.value }
function toggleCam() { camOff.value = !camOff.value }

onMounted(() => {
  setTimeout(() => { connected.value = true }, 1200)
  timer = setInterval(() => { if (connected.value) seconds.value++ }, 1000)
})
onUnmounted(() => { clearInterval(timer) })

function formatTime(s) {
  const m = Math.floor(s / 60)
  const ss = s % 60
  return `${m}:${ss.toString().padStart(2, '0')}`
}
</script>

<template>
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative flex flex-col items-center">
      <button @click="onClose && onClose()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700">✕</button>
      <h3 class="text-lg font-bold mb-4">Video Call</h3>
      <div v-if="!connected" class="text-indigo-600 mb-4 flex flex-col items-center">
        <span class="animate-pulse">Connecting…</span>
        <span class="mt-2 text-xs text-gray-400">Waiting for response…</span>
      </div>
      <div v-else class="flex flex-col items-center w-full">
        <div class="rounded-full bg-gray-200 w-24 h-24 flex items-center justify-center text-4xl text-gray-400 mb-2">
          <span v-if="camOff">🎥🚫</span>
          <span v-else>👤</span>
        </div>
        <div class="mb-2 text-gray-700 font-semibold">{{ context?.name || 'Call' }}</div>
        <div class="mb-4 text-xs text-gray-500">{{ formatTime(seconds) }}</div>
        <div class="flex gap-4 mb-4">
          <button @click="toggleMute" :class="muted ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-700'" class="rounded-full p-3 focus:outline-none">
            <span v-if="muted">🔇</span>
            <span v-else>🎤</span>
          </button>
          <button @click="toggleCam" :class="camOff ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700'" class="rounded-full p-3 focus:outline-none">
            <span v-if="camOff">📷🚫</span>
            <span v-else>📷</span>
          </button>
        </div>
        <button @click="onClose && onClose()" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700">Hang up</button>
      </div>
    </div>
  </div>
</template>
