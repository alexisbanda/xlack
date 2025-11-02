<script setup>
import { Link } from '@inertiajs/vue3'
import { onMounted, watch, computed } from 'vue'

const props = defineProps({
  channels: { type: Array, default: () => [] },
  dms: { type: Array, default: () => [] },
  teamMembers: { type: Array, default: () => [] },
  activeId: { type: Number, default: null },
  activeType: { type: String, default: null }
})

// Conjunto de user_ids con los que ya hay DM para filtrar miembros
const dmUserIds = computed(() => {
  const ids = new Set()
  try {
    (props.dms ?? []).forEach(dm => {
      if (dm && dm.user_id != null) ids.add(dm.user_id)
    })
  } catch {}
  return ids
})

const availableMembers = computed(() => {
  try {
    return (props.teamMembers ?? []).filter(m => m && !dmUserIds.value.has(m.id))
  } catch {
    return []
  }
})

// Debug ligero para verificar props en runtime (se puede retirar luego)
onMounted(() => {
  try {
    console.log('Sidebar props:', { channels: props.channels, dms: props.dms, teamMembers: props.teamMembers })
  } catch {}
})
watch(() => [props.channels, props.dms, props.teamMembers], () => {
  try {
    console.log('Sidebar props updated:', { channels: props.channels, dms: props.dms, teamMembers: props.teamMembers })
  } catch {}
})
</script>

<template>
  <div class="bg-white rounded shadow p-3 space-y-4">
    <!-- Channels -->
    <div>
      <h3 class="text-sm font-semibold text-gray-600 mb-2">Channels</h3>
      <ul class="space-y-1">
        <li v-for="c in channels" :key="c.id">
          <Link :href="`/dashboard?channel=${c.id}`" class="block px-3 py-2 rounded hover:bg-gray-100"
                :class="{ 'bg-indigo-50 text-indigo-700': activeType === 'channel' && activeId === c.id }">
            # {{ c.name }}
          </Link>
        </li>
      </ul>
    </div>

    <!-- Direct Messages -->
    <div v-if="dms.length > 0 || teamMembers.length > 0">
      <h3 class="text-sm font-semibold text-gray-600 mb-2">Direct Messages</h3>
      <ul class="space-y-1">
        <li v-for="dm in dms" :key="'dm-' + dm.id">
          <Link :href="`/dashboard?dm=${dm.id}`" class="block px-3 py-2 rounded hover:bg-gray-100"
                :class="{ 'bg-indigo-50 text-indigo-700': activeType === 'dm' && activeId === dm.id }">
            @ {{ dm.name }}
          </Link>
        </li>
        <!-- Team members to start a DM with -->
        <li v-for="member in availableMembers" :key="'member-' + member.id">
          <Link :href="`/dm/${member.id}`" class="block px-3 py-2 rounded hover:bg-gray-100 text-gray-500">
            + {{ member.name }}
          </Link>
        </li>
      </ul>
    </div>
  </div>
</template>
