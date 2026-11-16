import { defineStore } from 'pinia';
import { api } from '../api';

export const useNotificationStore = defineStore('notifications', {
    state: () => ({
        items: [],
        unread: 0,
    }),
    actions: {
        async load() {
            try {
                const payload = await api('/notifications');
                this.items = payload.data;
                this.unread = payload.unread;
            } catch {
                this.items = [];
                this.unread = 0;
            }
        },
        async read(id) {
            await api(`/notifications/${id}/read`, { method: 'POST' });
            await this.load();
        },
        async readAll() {
            await api('/notifications/read-all', { method: 'POST' });
            await this.load();
        },
    },
});
