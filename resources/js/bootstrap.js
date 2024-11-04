// resources/js/bootstrap.js
import axios from 'axios';
import { useRouter } from 'vue-router';

window.axios = axios;

// Set default headers
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.baseURL = '/api';

// Add auth token to requests
axios.interceptors.request.use(config => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Handle response errors
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token');
            if (window.router) {
                window.router.push('/login');
            }
        }
        return Promise.reject(error);
    }
);