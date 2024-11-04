// resources/js/store/modules/auth.js
import axios from 'axios';

export default {
    namespaced: true,

    state: {
        token: localStorage.getItem('token') || null,
        user: null,
        loading: false,
        error: null
    },

    getters: {
        isAuthenticated: state => !!state.token,
        user: state => state.user,
        loading: state => state.loading,
        error: state => state.error
    },

    mutations: {
        SET_TOKEN(state, token) {
            state.token = token;
            if (token) {
                localStorage.setItem('token', token);
            } else {
                localStorage.removeItem('token');
            }
        },
        SET_USER(state, user) {
            state.user = user;
        },
        SET_LOADING(state, status) {
            state.loading = status;
        },
        SET_ERROR(state, error) {
            state.error = error;
        },
        CLEAR_AUTH(state) {
            state.token = null;
            state.user = null;
            state.error = null;
            localStorage.removeItem('token');
        }
    },

    actions: {
        async register({ commit }, credentials) {
            commit('SET_LOADING', true);
            commit('SET_ERROR', null);
            try {
                const response = await axios.post('/v1/register', credentials);
                const { token, user } = response.data;
                commit('SET_TOKEN', token);
                commit('SET_USER', user);
                return user;
            } catch (e) {
                commit('SET_ERROR', e.response?.data?.message || 'Registration failed');
                throw e;
            } finally {
                commit('SET_LOADING', false);
            }
        },

        async login({ commit }, credentials) {
            commit('SET_LOADING', true);
            commit('SET_ERROR', null);
            try {
                const response = await axios.post('/v1/login', credentials);
                const { token, user } = response.data;
                commit('SET_TOKEN', token);
                commit('SET_USER', user);
                return user;
            } catch (e) {
                commit('SET_ERROR', e.response?.data?.message || 'Login failed');
                throw e;
            } finally {
                commit('SET_LOADING', false);
            }
        },

        async loginAsGuest({ commit }) {
            commit('SET_LOADING', true);
            commit('SET_ERROR', null);
            try {
                const response = await axios.post('/v1/guest');
                const { user, token } = response.data;
                
                commit('SET_USER', user);
                commit('SET_TOKEN', token);
                
                return response.data;
            } catch (e) {
                commit('SET_ERROR', e.response?.data?.message || 'Login failed');
                throw e;
            } finally {
                commit('SET_LOADING', false);
            }
        },

        async logout({ commit }) {
            commit('SET_LOADING', true);
            try {
                await axios.post('/v1/logout');
            } catch (e) {
                console.error('Logout error:', e);
                throw e;
            } finally {
                commit('CLEAR_AUTH');
                commit('SET_LOADING', false);
            }
        },

        async checkAuth({ commit, state }) {
            if (!state.token) return;
            
            commit('SET_LOADING', true);
            try {
                const response = await axios.get('/v1/user');
                commit('SET_USER', response.data.data);
            } catch (e) {
                commit('CLEAR_AUTH');
                throw e;
            } finally {
                commit('SET_LOADING', false);
            }
        }
    }
};