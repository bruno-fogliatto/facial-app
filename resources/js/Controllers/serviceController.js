import axios from 'axios';

const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content');

const api = axios.create({
    baseURL: 'http://127.0.0.1:8000',
    timeout: 30000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken })
    },
    withCredentials: true
});

api.interceptors.request.use(
    (config) => {
        const token = sessionStorage.getItem('access_token');

        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }

        return config;
    },
    (error) => Promise.reject(error)
);

const serviceController = {
    get(url, params = {}, config = {}) {
        return api.get(url, { params, ...config });
    },
    post(url, data = {}, config = {}) {
        return api.post(url, data, config);
    },
    put(url, data = {}, config = {}) {
        return api.put(url, data, config);
    },
    delete(url, config = {}) {
        return api.delete(url, config);
    }
};

export default serviceController;
