import axios from 'axios';

// Create axios instance with base URL
const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
});

// Request interceptor to add token
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token');
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

// API Methods
export default {
    // Auth
    login: (credentials) => api.post('/login', credentials),
    logout: () => api.post('/logout'),
    getUser: () => api.get('/user'),

    // Dashboard
    getStats: () => api.get('/dashboard/stats'),
    getRecentActivity: (params) => api.get('/dashboard/activity', { params }),
    getUpcomingEvents: (params) => api.get('/dashboard/upcoming-events', { params }),

    // Members
    getMembers: (params) => api.get('/members', { params }),
    getMember: (id) => api.get(`/members/${id}`),
    createMember: (data) => api.post('/members', data),
    updateMember: (id, data) => api.put(`/members/${id}`, data),
    deleteMember: (id) => api.delete(`/members/${id}`),
    getMemberStats: () => api.get('/members/stats'),
    searchMembers: (params) => api.get('/members/search', { params }),

    // Events
    getEvents: (params) => api.get('/events', { params }),
    getEvent: (id) => api.get(`/events/${id}`),
    createEvent: (data) => api.post('/events', data),
    updateEvent: (id, data) => api.put(`/events/${id}`, data),
    deleteEvent: (id) => api.delete(`/events/${id}`),

    // Churches (admin only)
    getChurches: (params) => api.get('/churches', { params }),
    getChurch: (id) => api.get(`/churches/${id}`),
    createChurch: (data) => api.post('/churches', data),
    updateChurch: (id, data) => api.put(`/churches/${id}`, data),
    deleteChurch: (id) => api.delete(`/churches/${id}`),

    // Export as default and named
    ...api
};
