<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppShell from '../components/AppShell.vue';
import { api, fieldError } from '../api';

const router = useRouter();
const categories = ref([]);
const error = ref(null);
const pending = ref(false);
const form = ref({
    title: '',
    description: '',
    category_id: '',
    priority: 'medium',
});

const priorities = [
    ['low', 'Niski', '8 godz.'],
    ['medium', 'Średni', '4 godz.'],
    ['high', 'Wysoki', '1 godz.'],
    ['urgent', 'Pilny', '15 min'],
];

onMounted(async () => {
    const payload = await api('/categories');
    categories.value = (payload.data ?? payload).filter((category) => category.is_active);
});

async function submit() {
    pending.value = true;
    error.value = null;

    try {
        const ticket = await api('/tickets', {
            method: 'POST',
            body: { ...form.value, category_id: Number(form.value.category_id) },
        });
        router.push({ path: '/tickets', query: { t: ticket.id } });
    } catch (caught) {
        error.value = caught;
    } finally {
        pending.value = false;
    }
}
</script>

<template>
    <AppShell title="Nowe zgłoszenie">
        <form class="card" style="display: flex; flex-direction: column; gap: 16px; max-width: 720px;" @submit.prevent="submit">
            <label class="field">
                <span>Temat</span>
                <input v-model="form.title" maxlength="160" placeholder="Np. Nie mogę zalogować się do poczty" required>
                <small v-if="fieldError(error, 'title')" class="error">{{ fieldError(error, 'title') }}</small>
            </label>

            <label class="field">
                <span>Co się dzieje</span>
                <textarea v-model="form.description" rows="7" placeholder="Opisz, co próbujesz zrobić i co widzisz zamiast tego." required />
                <small v-if="fieldError(error, 'description')" class="error">{{ fieldError(error, 'description') }}</small>
            </label>

            <label class="field">
                <span>Kategoria</span>
                <select v-model="form.category_id" required>
                    <option value="" disabled>Wybierz</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                </select>
                <small v-if="fieldError(error, 'category_id')" class="error">{{ fieldError(error, 'category_id') }}</small>
            </label>

            <div class="field">
                <span>Priorytet i czas na pierwszą odpowiedź</span>
                <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px;">
                    <button
                        v-for="[value, label, target] in priorities"
                        :key="value"
                        type="button"
                        class="btn outline"
                        :style="form.priority === value ? { borderColor: 'var(--color-ink)', boxShadow: 'inset 0 0 0 1px var(--color-ink)' } : {}"
                        @click="form.priority = value"
                    >
                        <span class="badge" :class="value">{{ label }}</span>
                        <div class="muted" style="font-size: 12px; margin-top: 6px; font-weight: 500;">{{ target }}</div>
                    </button>
                </div>
            </div>

            <p v-if="error && !Object.keys(error.errors).length" class="error">{{ error.message }}</p>
            <button class="btn" type="submit" :disabled="pending" style="align-self: flex-start;">{{ pending ? 'Wysyłam…' : 'Wyślij zgłoszenie' }}</button>
        </form>
    </AppShell>
</template>
