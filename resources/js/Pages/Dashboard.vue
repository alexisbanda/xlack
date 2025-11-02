<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Sidebar from '@/Components/Sidebar.vue';
import ChatWindow from '@/Components/ChatWindow.vue';
import MessageInput from '@/Components/MessageInput.vue';
import { computed } from 'vue';

const props = defineProps({
    channels: { type: Array, default: () => [] },
    dms: { type: Array, default: () => [] },
    teamMembers: { type: Array, default: () => [] },
    activeChannel: { type: Object, default: null },
    activeDm: { type: Object, default: null },
});

// Determinar contexto activo
const activeContext = computed(() => props.activeDm || props.activeChannel);
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ activeContext?.type === 'dm' ? 'Direct Messages' : 'Channels' }}
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-12 gap-4">
                    <aside class="col-span-12 md:col-span-3">
                        <Sidebar 
                            :channels="props.channels" 
                            :dms="props.dms"
                            :team-members="props.teamMembers"
                            :active-id="activeContext?.id"
                            :active-type="activeContext?.type"
                        />
                    </aside>
                    <main class="col-span-12 md:col-span-9">
                        <div class="bg-white rounded shadow p-4 min-h-[60vh] flex flex-col">
                            <ChatWindow :context="activeContext" class="flex-1" />
                            <MessageInput v-if="activeContext" :context="activeContext" class="mt-4" />
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </AppLayout>
    
</template>
