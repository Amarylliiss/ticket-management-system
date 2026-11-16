<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppShell from '../components/AppShell.vue';
import Avatar from '../components/Avatar.vue';
import Icon from '../components/Icon.vue';
import TicketPanel from '../components/TicketPanel.vue';
import { api } from '../api';
import { formatDay } from '../format';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();
const tickets = ref([]);
const agents = ref([]);
const categories = ref([]);
const query = ref('');
const showFilters = ref(false);
const category = ref('');
const cards = ref(null);
const loading = ref(true);

const urgency = { urgent: 0, high: 1, medium: 2, low: 3 };
const active = (ticket) => !['resolved', 'closed'].includes(ticket.status.value);

const selectedId = computed(() => route.query.t || null);

const filtered = computed(() => tickets.value.filter((ticket) => {
    if (category.value && ticket.category?.id !== Number(category.value)) {
        return false;
    }

    return true;
}));

const priorityTickets = computed(() => filtered.value
    .filter((ticket) => active(ticket) && ['urgent', 'high'].includes(ticket.priority.value))
    .sort((a, b) => urgency[a.priority.value] - urgency[b.priority.value]));

const inProgress = computed(() => filtered.value.filter((ticket) => {
    if (!['in_progress', 'waiting_for_customer'].includes(ticket.status.value)) {
        return false;
    }

    return auth.isStaff ? ticket.assignee?.id === auth.user.id || auth.isAdmin : true;
}));

const fresh = computed(() => filtered.value.filter((ticket) => ticket.status.value === 'open'));

const finished = computed(() => filtered.value.filter((ticket) => !active(ticket)));

async function load() {
    const params = new URLSearchParams({ per_page: '100' });

    if (query.value) {
        params.set('q', query.value);
    }

    const payload = await api(`/tickets?${params.toString()}`);
    tickets.value = payload.data;
    loading.value = false;

    if (!selectedId.value && tickets.value.length && window.innerWidth > 1200) {
        const first = priorityTickets.value[0] ?? inProgress.value[0] ?? tickets.value[0];
        select(first.id, true);
    }
}

function select(id, replace = false) {
    const target = { path: '/tickets', query: { t: id } };

    if (replace) {
        router.replace(target);
    } else {
        router.push(target);
    }
}

function scrollCards(direction) {
    cards.value?.scrollBy({ left: direction * 230, behavior: 'smooth' });
}

let timer = null;
watch(query, () => {
    clearTimeout(timer);
    timer = setTimeout(load, 250);
});

onMounted(async () => {
    const [categoryPayload] = await Promise.all([
        api('/categories'),
        auth.isStaff ? api('/agents').then((payload) => { agents.value = payload.data ?? payload; }) : null,
    ]);
    categories.value = categoryPayload.data ?? categoryPayload;
    await load();
});
</script>

<template>
    <AppShell v-model:query="query" title="Zgłoszenia" search>
        <div class="workspace">
            <div style="display: flex; flex-direction: column; gap: 30px; min-width: 0;">
                <section>
                    <div class="section-head">
                        <h2>Wymagają pilnej odpowiedzi ({{ priorityTickets.length }})</h2>
                        <div style="display: flex; gap: 6px;">
                            <button class="icon-btn" type="button" aria-label="Wstecz" @click="scrollCards(-1)"><Icon name="left" :size="16" /></button>
                            <button class="icon-btn" type="button" aria-label="Dalej" @click="scrollCards(1)"><Icon name="right" :size="16" /></button>
                        </div>
                    </div>
                    <div ref="cards" class="cards">
                        <button
                            v-for="ticket in priorityTickets"
                            :key="ticket.id"
                            type="button"
                            class="ticket-card"
                            :class="[ticket.priority.value, { selected: ticket.id === selectedId }]"
                            @click="select(ticket.id)"
                        >
                            <div class="top">
                                <span># {{ ticket.reference }}</span>
                                <span class="tag">{{ ticket.priority.label }}</span>
                            </div>
                            <h3>{{ ticket.title }}</h3>
                            <p>{{ ticket.description }}</p>
                            <div class="bottom">
                                <span class="meta"><Icon name="clip" :size="13" />{{ ticket.attachments_count }}</span>
                                <span class="meta"><Icon name="chat" :size="13" />{{ ticket.comments_count }}</span>
                                <span>{{ formatDay(ticket.created_at) }}</span>
                            </div>
                        </button>
                        <p v-if="!loading && priorityTickets.length === 0" class="muted" style="margin: 0;">Nie ma otwartych spraw z wysokim priorytetem.</p>
                    </div>
                </section>

                <section>
                    <div class="section-head">
                        <h2>{{ auth.isStaff && !auth.isAdmin ? 'Przypisane do Ciebie, w toku' : 'Przypisane, w toku' }} ({{ inProgress.length }})</h2>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Numer</th>
                                <th>Temat</th>
                                <th>Pliki i odpowiedzi</th>
                                <th>Priorytet</th>
                                <th>Data</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="ticket in inProgress"
                                :key="ticket.id"
                                class="row"
                                :class="{ selected: ticket.id === selectedId }"
                                @click="select(ticket.id)"
                            >
                                <td>#{{ ticket.reference }}</td>
                                <td class="topic">{{ ticket.title }}</td>
                                <td>
                                    <span class="meta" style="margin-right: 10px;"><Icon name="clip" :size="14" />{{ ticket.attachments_count }}</span>
                                    <span class="meta"><Icon name="chat" :size="14" />{{ ticket.comments_count }}</span>
                                </td>
                                <td><span class="badge" :class="ticket.priority.value">{{ ticket.priority.label }}</span></td>
                                <td>{{ formatDay(ticket.created_at) }}</td>
                                <td><span class="badge" :class="ticket.status.value">{{ ticket.status.label }}</span></td>
                            </tr>
                            <tr v-if="!loading && inProgress.length === 0">
                                <td colspan="6" class="empty">Nic nie jest teraz w toku.</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <section>
                    <div class="section-head">
                        <h2>Nowe ({{ fresh.length }})</h2>
                        <div style="position: relative; display: flex; gap: 8px; align-items: center;">
                            <select v-if="showFilters" v-model="category" class="search" style="width: 180px;">
                                <option value="">Wszystkie kategorie</option>
                                <option v-for="item in categories" :key="item.id" :value="item.id">{{ item.name }}</option>
                            </select>
                            <button class="icon-btn" type="button" aria-label="Filtry" @click="showFilters = !showFilters">
                                <Icon name="filter" :size="16" />
                            </button>
                        </div>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Numer</th>
                                <th>Temat</th>
                                <th>{{ auth.isStaff ? 'Klient' : 'Kategoria' }}</th>
                                <th>Priorytet</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="ticket in fresh"
                                :key="ticket.id"
                                class="row"
                                :class="{ selected: ticket.id === selectedId }"
                                @click="select(ticket.id)"
                            >
                                <td>#{{ ticket.reference }}</td>
                                <td class="topic">{{ ticket.title }}</td>
                                <td>
                                    <span v-if="auth.isStaff" class="person">
                                        <Avatar :name="ticket.customer?.name || ''" :size="24" />
                                        {{ ticket.customer?.name }}
                                    </span>
                                    <span v-else>{{ ticket.category?.name }}</span>
                                </td>
                                <td><span class="badge" :class="ticket.priority.value">{{ ticket.priority.label }}</span></td>
                                <td>{{ formatDay(ticket.created_at) }}</td>
                            </tr>
                            <tr v-if="!loading && fresh.length === 0">
                                <td colspan="5" class="empty">Brak nowych zgłoszeń.</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <section v-if="finished.length">
                    <div class="section-head">
                        <h2>Zakończone ({{ finished.length }})</h2>
                    </div>
                    <table class="table">
                        <tbody>
                            <tr
                                v-for="ticket in finished"
                                :key="ticket.id"
                                class="row"
                                :class="{ selected: ticket.id === selectedId }"
                                @click="select(ticket.id)"
                            >
                                <td>#{{ ticket.reference }}</td>
                                <td class="topic">{{ ticket.title }}</td>
                                <td>
                                    <span v-if="ticket.assignee" class="person">
                                        <Avatar :name="ticket.assignee.name" :size="24" />
                                        {{ ticket.assignee.name }}
                                    </span>
                                    <span v-else class="muted">—</span>
                                </td>
                                <td><span class="badge" :class="ticket.status.value">{{ ticket.status.label }}</span></td>
                                <td>{{ formatDay(ticket.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            </div>

            <TicketPanel v-if="selectedId" :ticket-id="selectedId" :agents="agents" @changed="load" />
            <section v-else class="panel" style="justify-content: center;">
                <p class="empty">Wybierz zgłoszenie, żeby zobaczyć rozmowę.</p>
            </section>
        </div>
    </AppShell>
</template>
