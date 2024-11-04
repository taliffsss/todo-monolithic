import { createRouter, createWebHistory } from 'vue-router';
import TaskList from '@/views/tasks/TaskList.vue';
import Login from '@/views/auth/Login.vue';
import Register from '@/views/auth/Register.vue';
import store from '@/store';

const routes = [
    {
        path: '/',
        name: 'tasks',
        component: TaskList,
        meta: { requiresAuth: true }
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta: { guest: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach((to, from, next) => {
    const isAuthenticated = store.getters['auth/isAuthenticated'];

    if (to.meta.requiresAuth && !isAuthenticated) {
        next('/login');
    } else if (to.meta.guest && isAuthenticated) {
        next('/');
    } else {
        next();
    }
});

export default router;