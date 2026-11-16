<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import Avatar from './Avatar.vue';
import Icon from './Icon.vue';
import { api, downloadFile, fieldError } from '../api';
import { formatDate, formatDuration, formatSize, formatTime } from '../format';
import { useAuthStore } from '../stores/auth';

const props = defineProps({
    ticketId: { type: String, required: true },
    agents: { type: Array, default: () => [] },
    wide: { type: Boolean, default: false },
});

const emit = defineEmits(['changed']);

const auth = useAuthStore();
const ticket = ref(null);
const tab = ref('chat');
const error = ref(null);
const message = ref('');
const internal = ref(false);
const sending = ref(false);
const chat = ref(null);
const fileInput = ref(null);

const statuses = [
    ['open', 'Otwarte'],
    ['in_progress', 'W trakcie'],
    ['waiting_for_customer', 'Oczekuje na klienta'],
    ['resolved', 'Rozwiązane'],
    ['closed', 'Zamknięte'],
];

const priorities = [
    ['low', 'Niski'],
    ['medium', 'Średni'],
    ['high', 'Wysoki'],
    ['urgent', 'Pilny'],
];

const tileColors = ['#ee6772', '#116c78', '#c6bd1f', '#4f5d75'];

const messages = computed(() => {
    if (!ticket.value) {
        return [];
    }

    const opening = {
        id: 'opening',
        body: ticket.value.description,
        author: { name: ticket.value.customer?.name, role: 'customer' },
        visibility: { value: 'public' },
        created_at: ticket.value.created_at,
    };

    return [opening, ...ticket.value.comments];
});

const closed = computed(() => ticket.value?.status.value === 'closed');

function isMine(item) {
    const authorIsStaff = item.author?.role && item.author.role !== 'customer';

    return authorIsStaff === auth.isStaff;
}

async function scrollDown() {
    await nextTick();

    if (chat.value) {
        chat.value.scrollTop = chat.value.scrollHeight;
    }
}

async function load() {
    error.value = null;
    ticket.value = await api(`/tickets/${props.ticketId}`);
    scrollDown();
}

async function run(action) {
    error.value = null;

    try {
        await action();
        await load();
        emit('changed');
    } catch (caught) {
        error.value = caught;
    }
}

function patch(path, body) {
    return run(() => api(`/tickets/${ticket.value.id}/${path}`, { method: 'PATCH', body }));
}

async function send() {
    if (!message.value.trim() || sending.value) {
        return;
    }

    sending.value = true;
    await run(async () => {
        await api(`/tickets/${ticket.value.id}/comments`, {
            method: 'POST',
            body: { body: message.value, visibility: internal.value ? 'internal' : 'public' },
        });
        message.value = '';
        internal.value = false;
    });
    sending.value = false;
}

async function upload(event) {
    const file = event.target.files[0];
    event.target.value = '';

    if (!file) {
        return;
    }

    const body = new FormData();
    body.append('file', file);
    await run(() => api(`/tickets/${ticket.value.id}/attachments`, { method: 'POST', body }));
}

onMounted(load);
watch(() => props.ticketId, () => {
    tab.value = 'chat';
    load();
});
</script>

<template>
    <section class="panel" :class="{ wide }">
        <p v-if="!ticket" class="empty">Wczytuję zgłoszenie…</p>

        <template v-else>
            <div class="panel-head">
                <Avatar :name="ticket.customer?.name || ''" :size="46" />
                <div style="min-width: 0;">
                    <h3>{{ ticket.customer?.name }}</h3>
                    <small>
                        <Icon v-if="ticket.priority.value === 'urgent'" name="crown" :size="13" style="color: var(--color-coral);" />
                        {{ ticket.priority.value === 'urgent' ? 'Sprawa pilna' : ticket.customer?.email }}
                    </small>
                </div>
                <span class="badge" :class="ticket.status.value" style="margin-left: auto;">{{ ticket.status.label }}</span>
            </div>

            <div class="tabs">
                <button type="button" :class="{ on: tab === 'chat' }" @click="tab = 'chat'; scrollDown()">
                    <Icon name="chat" :size="16" /> Rozmowa
                </button>
                <button type="button" :class="{ on: tab === 'info' }" @click="tab = 'info'">
                    <Icon name="info" :size="16" /> Informacje
                </button>
                <button type="button" :class="{ on: tab === 'activity' }" @click="tab = 'activity'">
                    <Icon name="activity" :size="16" /> Aktywność
                </button>
            </div>

            <template v-if="tab === 'chat'">
                <div class="summary">
                    <div style="min-width: 0;">
                        <h4>
                            <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ ticket.title }}</span>
                            <span style="white-space: nowrap;"># {{ ticket.reference }}</span>
                        </h4>
                        <dl class="facts">
                            <dt>Kategoria</dt>
                            <dd>{{ ticket.category?.name }}</dd>
                            <dt>Data</dt>
                            <dd>{{ formatDate(ticket.created_at) }}</dd>
                            <dt>Priorytet</dt>
                            <dd>
                                <select v-if="auth.isStaff" :value="ticket.priority.value" class="badge" :class="ticket.priority.value" @change="patch('priority', { priority: $event.target.value })">
                                    <option v-for="[value, label] in priorities" :key="value" :value="value">{{ label }}</option>
                                </select>
                                <span v-else class="badge" :class="ticket.priority.value">{{ ticket.priority.label }}</span>
                            </dd>
                            <dt>Przypisany</dt>
                            <dd>
                                <select v-if="auth.isStaff" :value="ticket.assignee?.id ?? ''" @change="$event.target.value && patch('assign', { assignee_id: Number($event.target.value) })">
                                    <option value="" disabled>Nieprzypisane</option>
                                    <option v-for="agent in agents" :key="agent.id" :value="agent.id">{{ agent.name }}</option>
                                </select>
                                <span v-else>{{ ticket.assignee?.name || 'Czeka na pracownika' }}</span>
                            </dd>
                        </dl>
                    </div>
                    <div v-if="ticket.attachments.length" class="tiles">
                        <button
                            v-for="(item, index) in ticket.attachments.slice(0, 2)"
                            :key="item.id"
                            type="button"
                            class="tile"
                            :class="{ more: index === 1 && ticket.attachments.length > 2 }"
                            :style="{ background: tileColors[index % tileColors.length] }"
                            :title="`${item.original_name} · ${formatSize(item.size)}`"
                            @click="index === 1 && ticket.attachments.length > 2 ? (tab = 'info') : downloadFile(`/attachments/${item.id}`, item.original_name)"
                        >
                            <template v-if="index === 1 && ticket.attachments.length > 2">+ {{ ticket.attachments.length - 1 }}</template>
                            <template v-else>{{ item.original_name }}</template>
                        </button>
                    </div>
                </div>

                <div ref="chat" class="chat">
                    <div
                        v-for="item in messages"
                        :key="item.id"
                        class="msg"
                        :class="{ mine: isMine(item), internal: item.visibility.value === 'internal' }"
                    >
                        <Avatar :name="item.author?.name || ''" :size="30" />
                        <div>
                            <time>
                                {{ formatTime(item.created_at) }}
                                <template v-if="item.visibility.value === 'internal'"> · notatka wewnętrzna</template>
                            </time>
                            <div class="bubble">{{ item.body }}</div>
                        </div>
                    </div>
                </div>

                <p v-if="error" class="error" style="margin: 0;">{{ fieldError(error, 'body') || fieldError(error, 'file') || error.message }}</p>

                <form v-if="!closed || auth.isStaff" class="composer" @submit.prevent="send">
                    <div class="field-box">
                        <button class="ghost-icon" type="button" title="Dołącz plik" @click="fileInput.click()">
                            <Icon name="clip" :size="16" />
                        </button>
                        <input ref="fileInput" type="file" hidden @change="upload">
                        <input v-model="message" type="text" :placeholder="internal ? 'Notatka widoczna tylko dla zespołu' : 'Napisz wiadomość…'">
                        <button v-if="auth.isStaff" class="ghost-icon" :class="{ on: internal }" type="button" title="Notatka wewnętrzna" @click="internal = !internal">
                            <Icon name="lock" :size="16" />
                        </button>
                    </div>
                    <button class="send" type="submit" :disabled="sending" aria-label="Wyślij">
                        <Icon name="send" :size="17" />
                    </button>
                </form>
                <p v-else class="muted" style="margin: 0; text-align: center;">Zgłoszenie jest zamknięte.</p>
            </template>

            <template v-else-if="tab === 'info'">
                <div class="summary" style="grid-template-columns: 1fr;">
                    <dl class="facts" style="grid-template-columns: 140px minmax(0, 1fr);">
                        <dt>Numer</dt>
                        <dd>{{ ticket.reference }}</dd>
                        <dt>Klient</dt>
                        <dd>{{ ticket.customer?.name }}</dd>
                        <dt>E-mail</dt>
                        <dd>{{ ticket.customer?.email }}</dd>
                        <dt>Próg odpowiedzi</dt>
                        <dd>{{ formatDuration(ticket.priority.target_minutes * 60) }}</dd>
                        <dt>Pierwsza odpowiedź</dt>
                        <dd>{{ ticket.first_response_seconds === null ? 'jeszcze nie ma' : formatDuration(ticket.first_response_seconds) }}</dd>
                        <dt>Rozwiązano</dt>
                        <dd>{{ formatDate(ticket.resolved_at) }}</dd>
                        <dt>Zamknięto</dt>
                        <dd>{{ formatDate(ticket.closed_at) }}</dd>
                        <dt>Status</dt>
                        <dd>
                            <select v-if="auth.isStaff" :value="ticket.status.value" @change="patch('status', { status: $event.target.value })">
                                <option v-for="[value, label] in statuses" :key="value" :value="value">{{ label }}</option>
                            </select>
                            <span v-else>{{ ticket.status.label }}</span>
                        </dd>
                    </dl>
                </div>

                <div>
                    <div class="muted" style="font-size: 12px; margin-bottom: 6px;">Opis</div>
                    <p style="margin: 0; white-space: pre-wrap; line-height: 1.55;">{{ ticket.description }}</p>
                </div>

                <div>
                    <div class="muted" style="font-size: 12px; margin-bottom: 6px;">Załączniki</div>
                    <p v-if="ticket.attachments.length === 0" class="muted" style="margin: 0;">Brak plików.</p>
                    <button
                        v-for="item in ticket.attachments"
                        :key="item.id"
                        type="button"
                        class="ghost-icon"
                        style="display: flex; gap: 8px; color: var(--color-ink); padding: 6px 0;"
                        @click="downloadFile(`/attachments/${item.id}`, item.original_name)"
                    >
                        <Icon name="clip" :size="14" /> {{ item.original_name }}
                        <span class="muted">{{ formatSize(item.size) }}</span>
                    </button>
                </div>

                <p v-if="error" class="error" style="margin: 0;">{{ error.message }}</p>

                <button v-if="!auth.isStaff && !closed" class="btn outline" type="button" style="margin-top: auto;" @click="patch('status', { status: 'closed' })">
                    Zamknij zgłoszenie
                </button>
            </template>

            <div v-else class="timeline">
                <article v-for="item in ticket.activities" :key="item.id">
                    <span class="dot" />
                    <div>
                        <div>{{ item.summary }}</div>
                        <small class="muted">{{ item.actor?.name || 'System' }} · {{ formatDate(item.created_at) }}</small>
                    </div>
                </article>
            </div>
        </template>
    </section>
</template>
