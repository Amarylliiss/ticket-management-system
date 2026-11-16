<script setup>
import { onMounted, ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import Avatar from './Avatar.vue';
import Icon from './Icon.vue';
import { formatDate } from '../format';
import { useAuthStore } from '../stores/auth';
import { useNotificationStore } from '../stores/notifications';

defineProps({
    title: { type: String, required: true },
    search: { type: Boolean, default: false },
});

const query = defineModel('query', { type: String, default: '' });

const auth = useAuthStore();
const notifications = useNotificationStore();
const router = useRouter();
const bellOpen = ref(false);
const menuOpen = ref(false);

async function openNote(note) {
    if (!note.read_at) {
        await notifications.read(note.id);
    }

    bellOpen.value = false;

    if (note.data?.ticket_id) {
        router.push({ path: '/tickets', query: { t: note.data.ticket_id } });
    }
}

async function logout() {
    await auth.logout();
    router.push('/login');
}

onMounted(() => notifications.load());
</script>

<template>
    <div class="frame">
        <div class="shell">
            <aside class="rail">
                <div class="logo">
                    <b>hd</b>
                    <span>helpdesk</span>
                </div>

                <nav class="nav">
                    <RouterLink class="nav-link" to="/">
                        <span class="nav-icon"><Icon name="grid" /></span>
                        Pulpit
                    </RouterLink>
                    <RouterLink class="nav-link" to="/tickets" :class="{ active: $route.path.startsWith('/tickets') && $route.path !== '/tickets/new' }">
                        <span class="nav-icon"><Icon name="ticket" /></span>
                        Zgłoszenia
                    </RouterLink>
                    <RouterLink class="nav-link" to="/inbox">
                        <span class="nav-icon"><Icon name="inbox" /></span>
                        Skrzynka
                        <span v-if="notifications.unread" class="count">{{ notifications.unread }}</span>
                    </RouterLink>
                    <RouterLink class="nav-link" to="/tickets/new">
                        <span class="nav-icon"><Icon name="plus" /></span>
                        Nowe zgłoszenie
                    </RouterLink>
                    <RouterLink v-if="auth.isAdmin" class="nav-link" to="/categories">
                        <span class="nav-icon"><Icon name="tag" /></span>
                        Kategorie
                    </RouterLink>
                </nav>

                <div class="promo">
                    <p v-if="auth.isStaff">Odpowiedz publicznie, a licznik czasu pierwszej odpowiedzi zatrzyma się sam.</p>
                    <p v-else>Masz nowy problem? Opisz go, a zespół wróci z odpowiedzią.</p>
                    <RouterLink class="btn light" to="/tickets/new" style="text-align: center; text-decoration: none;">
                        Zgłoś problem
                    </RouterLink>
                </div>
            </aside>

            <main class="main">
                <header class="topbar">
                    <h1>{{ title }}</h1>

                    <label v-if="search" class="search">
                        <Icon name="search" :size="16" />
                        <input v-model="query" placeholder="Szukaj zgłoszenia">
                    </label>

                    <div style="position: relative;">
                        <button class="icon-btn" :class="{ dot: notifications.unread }" type="button" aria-label="Powiadomienia" @click="bellOpen = !bellOpen; menuOpen = false">
                            <Icon name="bell" :size="17" />
                        </button>
                        <div v-if="bellOpen" class="dropdown">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <strong>Powiadomienia</strong>
                                <button class="ghost-icon" type="button" style="font-size: 12px;" @click="notifications.readAll()">Oznacz wszystkie</button>
                            </div>
                            <p v-if="notifications.items.length === 0" class="muted">Brak powiadomień.</p>
                            <button
                                v-for="note in notifications.items"
                                :key="note.id"
                                type="button"
                                style="display: block; width: 100%; text-align: left; background: transparent; border: 0; padding: 10px 0; border-top: 1px solid var(--color-line); cursor: pointer;"
                                @click="openNote(note)"
                            >
                                <div :style="{ fontWeight: note.read_at ? 400 : 700 }">{{ note.data.message }}</div>
                                <small class="muted">{{ formatDate(note.created_at) }}</small>
                            </button>
                        </div>
                    </div>

                    <div style="position: relative;">
                        <button class="me" type="button" @click="menuOpen = !menuOpen; bellOpen = false">
                            <Avatar :name="auth.user?.name || ''" :size="34" />
                            <span>{{ auth.user?.name }}</span>
                            <Icon name="down" :size="14" />
                        </button>
                        <div v-if="menuOpen" class="dropdown" style="width: 220px;">
                            <div class="muted" style="font-size: 12px;">{{ auth.user?.email }}</div>
                            <div style="margin: 4px 0 12px;">{{ auth.user?.role?.label }}</div>
                            <button class="btn outline" type="button" style="width: 100%; display: flex; gap: 8px; justify-content: center;" @click="logout">
                                <Icon name="logout" :size="16" /> Wyloguj
                            </button>
                        </div>
                    </div>
                </header>

                <slot />
            </main>
        </div>
    </div>
</template>
