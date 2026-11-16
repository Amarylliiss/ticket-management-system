<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AppShell from '../components/AppShell.vue';
import Icon from '../components/Icon.vue';
import { formatDate } from '../format';
import { useNotificationStore } from '../stores/notifications';

const notifications = useNotificationStore();
const router = useRouter();

async function open(note) {
    if (!note.read_at) {
        await notifications.read(note.id);
    }

    if (note.data?.ticket_id) {
        router.push({ path: '/tickets', query: { t: note.data.ticket_id } });
    }
}

onMounted(() => notifications.load());
</script>

<template>
    <AppShell title="Skrzynka">
        <section class="card" style="max-width: 760px;">
            <div class="section-head">
                <h2>Powiadomienia ({{ notifications.unread }} nieprzeczytane)</h2>
                <button class="btn outline" type="button" :disabled="!notifications.unread" @click="notifications.readAll()">Oznacz wszystkie</button>
            </div>
            <p v-if="notifications.items.length === 0" class="empty">Skrzynka jest pusta.</p>
            <button
                v-for="note in notifications.items"
                :key="note.id"
                type="button"
                style="display: flex; gap: 12px; width: 100%; text-align: left; background: transparent; border: 0; border-top: 1px solid var(--color-line); padding: 14px 0; cursor: pointer; align-items: center;"
                @click="open(note)"
            >
                <span class="icon-btn" :class="{ dot: !note.read_at }"><Icon :name="note.data.kind === 'comment' ? 'chat' : 'ticket'" :size="16" /></span>
                <span style="flex: 1;">
                    <span :style="{ fontWeight: note.read_at ? 500 : 700, display: 'block' }">{{ note.data.message }}</span>
                    <small class="muted">{{ formatDate(note.created_at) }}</small>
                </span>
                <Icon name="right" :size="16" />
            </button>
        </section>
    </AppShell>
</template>
