<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  context: { type: Object, required: true }
})

const form = useForm({ body: '' })

const endpoint = computed(() => {
  if (props.context.type === 'dm') {
    return `/dm/${props.context.id}/messages`
  }
  return `/channels/${props.context.id}/messages`
})

function submit() {
  if (!form.body.trim()) return
  form.post(endpoint.value, {
    preserveScroll: true,
    onSuccess: () => {
      form.reset('body')
    }
  })
}
</script>

<template>
  <form @submit.prevent="submit" class="flex items-center gap-2">
  <input v-model="form.body" type="text" placeholder="Write a message..." class="flex-1 border rounded px-3 py-2" />
  <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Send</button>
  </form>
</template>
