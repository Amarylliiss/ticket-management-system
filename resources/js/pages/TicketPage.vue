<script setup>
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import AppShell from '../components/AppShell.vue';
import TicketPanel from '../components/TicketPanel.vue';
import { api } from '../api';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const auth = useAuthStore();
const agents = ref([]);

onMounted(async () => {
    if (auth.isStaff) {
        const payload = await api('/agents');
        agents.value = payload.data ?? payload;
    }
});
</script>

<template>
    <AppShell title="Zgłoszenie">
        <div style="max-width: 760px; width: 100%;">
            <TicketPanel :ticket-id="String(route.params.id)" :agents="agents" wide />
        </div>
    </AppShell>
</template>
