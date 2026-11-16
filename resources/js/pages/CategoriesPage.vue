<script setup>
import { onMounted, ref } from 'vue';
import AppShell from '../components/AppShell.vue';
import { api, fieldError } from '../api';

const categories = ref([]);
const error = ref(null);
const form = ref({ name: '', description: '', is_active: true });

async function load() {
    const payload = await api('/categories');
    categories.value = payload.data ?? payload;
}

async function run(action) {
    error.value = null;

    try {
        await action();
        await load();
    } catch (caught) {
        error.value = caught;
    }
}

function create() {
    return run(async () => {
        await api('/categories', { method: 'POST', body: form.value });
        form.value = { name: '', description: '', is_active: true };
    });
}

function toggle(category) {
    return run(() => api(`/categories/${category.id}`, {
        method: 'PATCH',
        body: { name: category.name, description: category.description, is_active: !category.is_active },
    }));
}

function remove(category) {
    return run(() => api(`/categories/${category.id}`, { method: 'DELETE' }));
}

onMounted(load);
</script>

<template>
    <AppShell title="Kategorie">
        <form class="card" style="display: grid; grid-template-columns: 1fr 1.4fr auto; gap: 12px; align-items: end;" @submit.prevent="create">
            <label class="field">
                <span>Nazwa</span>
                <input v-model="form.name" required>
            </label>
            <label class="field">
                <span>Opis</span>
                <input v-model="form.description">
            </label>
            <button class="btn" type="submit">Dodaj</button>
        </form>
        <p v-if="error" class="error" style="margin: 0;">{{ fieldError(error, 'name') || error.message }}</p>

        <section class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nazwa</th>
                        <th>Zgłoszenia</th>
                        <th>Stan</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="category in categories" :key="category.id">
                        <td class="topic">
                            <strong>{{ category.name }}</strong>
                            <div class="muted">{{ category.description }}</div>
                        </td>
                        <td>{{ category.tickets_count }}</td>
                        <td><span class="badge" :class="category.is_active ? 'resolved' : 'closed'">{{ category.is_active ? 'Aktywna' : 'Wyłączona' }}</span></td>
                        <td style="text-align: right;">
                            <button class="btn outline" type="button" style="margin-right: 8px;" @click="toggle(category)">{{ category.is_active ? 'Wyłącz' : 'Włącz' }}</button>
                            <button class="btn outline" type="button" @click="remove(category)">Usuń</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </AppShell>
</template>
