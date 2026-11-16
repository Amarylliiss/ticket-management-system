<script setup>
import { ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import GuestFrame from '../components/GuestFrame.vue';
import { api, fieldError } from '../api';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const auth = useAuthStore();
const form = ref({ email: '', password: '' });
const error = ref(null);
const pending = ref(false);

async function submit() {
    pending.value = true;
    error.value = null;

    try {
        const payload = await api('/auth/login', { method: 'POST', body: form.value });
        auth.setSession(payload.token, payload.user);
        router.push('/');
    } catch (caught) {
        error.value = caught;
    } finally {
        pending.value = false;
    }
}
</script>

<template>
    <GuestFrame>
        <h2 style="margin: 0 0 6px; letter-spacing: -0.04em; font-size: 30px; font-weight: 800;">Zaloguj się</h2>
        <p class="muted" style="margin: 0 0 22px;">Wejdź do swoich zgłoszeń.</p>
        <form style="display: flex; flex-direction: column; gap: 14px;" @submit.prevent="submit">
            <label class="field">
                <span>E-mail</span>
                <input v-model="form.email" type="email" autocomplete="username" required>
                <small v-if="fieldError(error, 'email')" class="error">{{ fieldError(error, 'email') }}</small>
            </label>
            <label class="field">
                <span>Hasło</span>
                <input v-model="form.password" type="password" autocomplete="current-password" required>
            </label>
            <p v-if="error && !fieldError(error, 'email')" class="error">{{ error.message }}</p>
            <button class="btn" type="submit" :disabled="pending">{{ pending ? 'Sprawdzam…' : 'Zaloguj' }}</button>
        </form>
        <p class="muted" style="margin-top: 18px;">Nie masz konta? <RouterLink to="/register">Załóż je jako klient</RouterLink></p>
    </GuestFrame>
</template>
