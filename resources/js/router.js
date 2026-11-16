import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from './stores/auth';
import LoginPage from './pages/LoginPage.vue';
import RegisterPage from './pages/RegisterPage.vue';
import DashboardPage from './pages/DashboardPage.vue';
import TicketListPage from './pages/TicketListPage.vue';
import TicketFormPage from './pages/TicketFormPage.vue';
import TicketPage from './pages/TicketPage.vue';
import CategoriesPage from './pages/CategoriesPage.vue';
import InboxPage from './pages/InboxPage.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/login', component: LoginPage, meta: { guest: true } },
        { path: '/register', component: RegisterPage, meta: { guest: true } },
        { path: '/', component: DashboardPage, meta: { auth: true } },
        { path: '/tickets', component: TicketListPage, meta: { auth: true } },
        { path: '/tickets/new', component: TicketFormPage, meta: { auth: true } },
        { path: '/tickets/:id', component: TicketPage, meta: { auth: true } },
        { path: '/inbox', component: InboxPage, meta: { auth: true } },
        { path: '/categories', component: CategoriesPage, meta: { auth: true, admin: true } },
    ],
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    if (!auth.ready) {
        await auth.bootstrap();
    }

    if (to.meta.auth && !auth.user) {
        return '/login';
    }

    if (to.meta.guest && auth.user) {
        return '/';
    }

    if (to.meta.admin && !auth.isAdmin) {
        return '/';
    }

    return true;
});

export default router;
