<script setup>
import { computed, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import AppShell from '../components/AppShell.vue';
import Avatar from '../components/Avatar.vue';
import { api } from '../api';
import { formatDay, formatDuration } from '../format';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const stats = ref(null);
const tickets = ref([]);

const counts = computed(() => ({
    open: tickets.value.filter((ticket) => ticket.status.value === 'open').length,
    progress: tickets.value.filter((ticket) => ['in_progress', 'waiting_for_customer'].includes(ticket.status.value)).length,
    done: tickets.value.filter((ticket) => ['resolved', 'closed'].includes(ticket.status.value)).length,
}));

onMounted(async () => {
    const list = await api('/tickets?per_page=100');
    tickets.value = list.data;

    if (auth.isStaff) {
        stats.value = await api('/statistics/response-times');
    }
});
</script>

<template>
    <AppShell title="Pulpit">
        <p class="muted" style="margin: -10px 0 0;">
            Dzień dobry, {{ auth.user?.name?.split(' ')[0] }}.
            {{ auth.isStaff ? 'Czas odpowiedzi liczy się do pierwszej publicznej odpowiedzi pracownika.' : 'Tu widzisz swoje sprawy i ich status.' }}
        </p>

        <section v-if="stats" class="stats">
            <article class="stat" style="background-color: var(--color-teal);">
                <span>Zgłoszenia w zestawieniu</span>
                <b>{{ stats.totals.tickets }}</b>
            </article>
            <article class="stat" style="background-color: var(--color-coral);">
                <span>Średnia pierwsza odpowiedź</span>
                <b>{{ formatDuration(stats.average_first_response_seconds) }}</b>
            </article>
            <article class="stat" style="background-color: var(--color-olive);">
                <span>Średni czas rozwiązania</span>
                <b>{{ formatDuration(stats.average_resolution_seconds) }}</b>
            </article>
            <article class="stat" style="background-color: var(--color-rail);">
                <span>Poza progiem priorytetu</span>
                <b>{{ stats.totals.breached_sla }}</b>
            </article>
        </section>

        <section v-else class="stats">
            <article class="stat" style="background-color: var(--color-olive);">
                <span>Nowe</span>
                <b>{{ counts.open }}</b>
            </article>
            <article class="stat" style="background-color: var(--color-teal);">
                <span>W toku</span>
                <b>{{ counts.progress }}</b>
            </article>
            <article class="stat" style="background-color: var(--color-slate);">
                <span>Zakończone</span>
                <b>{{ counts.done }}</b>
            </article>
            <article class="stat" style="background-color: var(--color-coral);">
                <span>Masz nowy problem?</span>
                <RouterLink to="/tickets/new" style="color: white; font-weight: 700;">Zgłoś go →</RouterLink>
            </article>
        </section>

        <div v-if="stats" style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 20px;">
            <section class="card">
                <div class="section-head"><h2>Według priorytetu</h2></div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Priorytet</th>
                            <th>Sprawy</th>
                            <th>Próg</th>
                            <th>Średnia</th>
                            <th>Po terminie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in stats.by_priority" :key="row.priority">
                            <td><span class="badge" :class="row.priority">{{ row.label }}</span></td>
                            <td>{{ row.tickets }}</td>
                            <td>{{ formatDuration(row.target_seconds) }}</td>
                            <td>{{ formatDuration(row.average_first_response_seconds) }}</td>
                            <td>{{ row.breached_sla }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="card">
                <div class="section-head"><h2>Zespół</h2></div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Pracownik</th>
                            <th>Sprawy</th>
                            <th>Rozwiązane</th>
                            <th>Pierwsza odp.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in stats.by_agent" :key="row.id">
                            <td><span class="person"><Avatar :name="row.name" :size="24" />{{ row.name }}</span></td>
                            <td>{{ row.assigned }}</td>
                            <td>{{ row.resolved }}</td>
                            <td>{{ formatDuration(row.average_first_response_seconds) }}</td>
                        </tr>
                        <tr v-if="stats.by_agent.length === 0">
                            <td colspan="4" class="empty">Nikt nie ma jeszcze przypisanych spraw.</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>

        <section class="card">
            <div class="section-head">
                <h2>Ostatnie zgłoszenia</h2>
                <RouterLink to="/tickets" class="muted">Wszystkie</RouterLink>
            </div>
            <table class="table">
                <tbody>
                    <RouterLink
                        v-for="ticket in tickets.slice(0, 6)"
                        :key="ticket.id"
                        v-slot="{ navigate }"
                        :to="{ path: '/tickets', query: { t: ticket.id } }"
                        custom
                    >
                        <tr class="row" @click="navigate">
                            <td>#{{ ticket.reference }}</td>
                            <td class="topic">{{ ticket.title }}</td>
                            <td><span class="badge" :class="ticket.priority.value">{{ ticket.priority.label }}</span></td>
                            <td><span class="badge" :class="ticket.status.value">{{ ticket.status.label }}</span></td>
                            <td>{{ formatDay(ticket.created_at) }}</td>
                        </tr>
                    </RouterLink>
                    <tr v-if="tickets.length === 0">
                        <td colspan="5" class="empty">Nie ma jeszcze zgłoszeń.</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </AppShell>
</template>
