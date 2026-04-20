import axios from 'axios';

const api = axios.create({
    baseURL: '/',
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
    },
});

export async function ensureCsrf(): Promise<void> {
    await api.get('/sanctum/csrf-cookie');
}

export default api;
