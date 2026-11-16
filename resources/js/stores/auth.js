import { defineStore } from 'pinia';
import { api } from '../api';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('helpdesk_token'),
        user: null,
        ready: false,
    }),
    getters: {
        isStaff: (state) => Boolean(state.user?.is_staff),
        isAdmin: (state) => Boolean(state.user?.is_admin),
    },
    actions: {
        async bootstrap() {
            if (this.ready) {
                return;
            }

            if (!this.token) {
                this.ready = true;
                return;
            }

            try {
                this.user = await api('/auth/me');
            } catch {
                this.clear();
            } finally {
                this.ready = true;
            }
        },
        setSession(token, user) {
            this.token = token;
            this.user = user;
            this.ready = true;
            localStorage.setItem('helpdesk_token', token);
        },
        async logout() {
            try {
                await api('/auth/logout', { method: 'POST' });
            } catch {
                // Sesja i tak znika lokalnie.
            }

            this.clear();
        },
        clear() {
            this.token = null;
            this.user = null;
            localStorage.removeItem('helpdesk_token');
        },
    },
});
