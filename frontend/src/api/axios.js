import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL;

if (!API_URL) {
    throw new Error(
        'VITE_API_URL must be configured. Example: http://localhost',
    );
}

const api = axios.create({
    baseURL: API_URL,
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        Accept: 'application/json',
    },
});

export const getCsrfCookie = () => api.get('/sanctum/csrf-cookie');

export default api;
