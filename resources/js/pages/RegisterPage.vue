<script setup>
import { ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import GuestFrame from '../components/GuestFrame.vue';
import { api, fieldError } from '../api';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const auth = useAuthStore();
const form = ref({ name: '', email: '', password: '', password_confirmation: '' });
const error = ref(null);
const pending = ref(false);

async function submit() {
    pending.value = true;
    error.value = null;

    try {
        const payload = await api('/auth/register', { method: 'POST', body: form.value });
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
        <h2 style="margin: 0 0 22px; letter-spacing: -0.04em; font-size: 30px; font-weight: 800;">Konto klienta</h2>
        <form style="display: flex; flex-direction: column; gap: 14px;" @submit.prevent="submit">
            <label class="field">
                <span>Imię i nazwisko</span>
                <input v-model="form.name" required>
                <small v-if="fieldError(error, 'name')" class="error">{{ fieldError(error, 'name') }}</small>
            </label>
            <label class="field">
                <span>E-mail</span>
                <input v-model="form.email" type="email" required>
                <small v-if="fieldError(error, 'email')" class="error">{{ fieldError(error, 'email') }}</small>
            </label>
            <label class="field">
                <span>Hasło</span>
                <input v-model="form.password" type="password" required>
                <small v-if="fieldError(error, 'password')" class="error">{{ fieldError(error, 'password') }}</small>
            </label>
            <label class="field">
                <span>Powtórz hasło</span>
                <input v-model="form.password_confirmation" type="password" required>
            </label>
            <button class="btn" type="submit" :disabled="pending">{{ pending ? 'Zakładam…' : 'Utwórz konto' }}</button>
        </form>
        <p class="muted" style="margin-top: 18px;">Masz już konto? <RouterLink to="/login">Zaloguj się</RouterLink></p>
    </GuestFrame>
</template>
