import axios from 'axios';

const BASE_URL = '/api';

const getApiClient = () => {
  const token = localStorage.getItem('token');
  return axios.create({
    baseURL: BASE_URL,
    headers: {
      'Content-Type': 'application/json',
      ...(token && { Authorization: `Bearer ${token}` }),
    },
  });
};

/**
 * Event API Service
 */
export const EventService = {
  async getAll(params = {}) {
    const api = getApiClient();
    const response = await api.get('/events', { params });
    return response.data;
  },

  async getById(id) {
    const api = getApiClient();
    const response = await api.get(`/events/${id}`);
    return response.data;
  },

  async create(data) {
    const api = getApiClient();
    const response = await api.post('/events', data);
    return response.data;
  },

  async update(id, data) {
    const api = getApiClient();
    const response = await api.put(`/events/${id}`, data);
    return response.data;
  },

  async delete(id) {
    const api = getApiClient();
    const response = await api.delete(`/events/${id}`);
    return response.data;
  },

  async getUpcoming(limit = 10) {
    const api = getApiClient();
    const response = await api.get('/events/upcoming', { params: { limit } });
    return response.data;
  },

  async getPast(limit = 10) {
    const api = getApiClient();
    const response = await api.get('/events/past', { params: { limit } });
    return response.data;
  },

  async getAttendance(eventId) {
    const api = getApiClient();
    const response = await api.get(`/events/${eventId}/attendance`);
    return response.data;
  },

  async export(format = 'csv') {
    const api = getApiClient();
    const response = await api.get('/events/export', { params: { format } });
    return response.data;
  },
};

/**
 * Attendance API Service
 */
export const AttendanceService = {
  async getAll(params = {}) {
    const api = getApiClient();
    const response = await api.get('/attendance', { params });
    return response.data;
  },

  async getById(id) {
    const api = getApiClient();
    const response = await api.get(`/attendance/${id}`);
    return response.data;
  },

  async create(data) {
    const api = getApiClient();
    const response = await api.post('/attendance', data);
    return response.data;
  },

  async update(id, data) {
    const api = getApiClient();
    const response = await api.put(`/attendance/${id}`, data);
    return response.data;
  },

  async delete(id) {
    const api = getApiClient();
    const response = await api.delete(`/attendance/${id}`);
    return response.data;
  },

  async getTodayAttendance() {
    const api = getApiClient();
    const response = await api.get('/attendance/today');
    return response.data;
  },

  async getByEvent(eventId) {
    const api = getApiClient();
    const response = await api.get(`/attendance/event/${eventId}`);
    return response.data;
  },

  async getByMember(memberId) {
    const api = getApiClient();
    const response = await api.get(`/attendance/member/${memberId}`);
    return response.data;
  },

  async bulkCreate(data) {
    const api = getApiClient();
    const response = await api.post('/attendance/bulk', data);
    return response.data;
  },

  async export(format = 'csv', filters = {}) {
    const api = getApiClient();
    const response = await api.get('/attendance/export', { params: { format, ...filters } });
    return response.data;
  },
};

/**
 * Department API Service
 */
export const DepartmentService = {
  async getAll(params = {}) {
    const api = getApiClient();
    const response = await api.get('/departments', { params });
    return response.data;
  },

  async getById(id) {
    const api = getApiClient();
    const response = await api.get(`/departments/${id}`);
    return response.data;
  },

  async create(data) {
    const api = getApiClient();
    const response = await api.post('/departments', data);
    return response.data;
  },

  async update(id, data) {
    const api = getApiClient();
    const response = await api.put(`/departments/${id}`, data);
    return response.data;
  },

  async delete(id) {
    const api = getApiClient();
    const response = await api.delete(`/departments/${id}`);
    return response.data;
  },

  async getMembers(departmentId) {
    const api = getApiClient();
    const response = await api.get(`/departments/${departmentId}/members`);
    return response.data;
  },

  async addMember(departmentId, memberId, role = null) {
    const api = getApiClient();
    const response = await api.post(`/departments/${departmentId}/members`, { member_id: memberId, role });
    return response.data;
  },

  async removeMember(departmentId, memberId) {
    const api = getApiClient();
    const response = await api.delete(`/departments/${departmentId}/members/${memberId}`);
    return response.data;
  },

  async getStatistics(departmentId) {
    const api = getApiClient();
    const response = await api.get(`/departments/${departmentId}/statistics`);
    return response.data;
  },
};

/**
 * Member API Service
 */
export const MemberService = {
  async getAll(params = {}) {
    const api = getApiClient();
    const response = await api.get('/members', { params });
    return response.data;
  },

  async getById(id) {
    const api = getApiClient();
    const response = await api.get(`/members/${id}`);
    return response.data;
  },

  async create(data) {
    const api = getApiClient();
    const response = await api.post('/members', data);
    return response.data;
  },

  async update(id, data) {
    const api = getApiClient();
    const response = await api.put(`/members/${id}`, data);
    return response.data;
  },

  async delete(id) {
    const api = getApiClient();
    const response = await api.delete(`/members/${id}`);
    return response.data;
  },

  async getRecent(limit = 5) {
    const api = getApiClient();
    const response = await api.get('/members/recent', { params: { limit } });
    return response.data;
  },

  async search(query) {
    const api = getApiClient();
    const response = await api.get('/members/search', { params: { q: query } });
    return response.data;
  },

  async export(format = 'csv') {
    const api = getApiClient();
    const response = await api.get('/members/export', { params: { format } });
    return response.data;
  },

  async import(file) {
    const api = getApiClient();
    const formData = new FormData();
    formData.append('file', file);
    const response = await api.post('/members/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data;
  },
};

/**
 * Donation API Service
 */
export const DonationService = {
  async getAll(params = {}) {
    const api = getApiClient();
    const response = await api.get('/donations', { params });
    return response.data;
  },

  async getById(id) {
    const api = getApiClient();
    const response = await api.get(`/donations/${id}`);
    return response.data;
  },

  async create(data) {
    const api = getApiClient();
    const response = await api.post('/donations', data);
    return response.data;
  },

  async update(id, data) {
    const api = getApiClient();
    const response = await api.put(`/donations/${id}`, data);
    return response.data;
  },

  async delete(id) {
    const api = getApiClient();
    const response = await api.delete(`/donations/${id}`);
    return response.data;
  },

  async getTotalByPeriod(period = 'month') {
    const api = getApiClient();
    const response = await api.get('/donations/total', { params: { period } });
    return response.data;
  },

  async getStatistics() {
    const api = getApiClient();
    const response = await api.get('/donations/statistics');
    return response.data;
  },

  async export(format = 'csv') {
    const api = getApiClient();
    const response = await api.get('/donations/export', { params: { format } });
    return response.data;
  },
};

/**
 * Prayer Request API Service
 */
export const PrayerRequestService = {
  async getAll(params = {}) {
    const api = getApiClient();
    const response = await api.get('/prayer-requests', { params });
    return response.data;
  },

  async getById(id) {
    const api = getApiClient();
    const response = await api.get(`/prayer-requests/${id}`);
    return response.data;
  },

  async create(data) {
    const api = getApiClient();
    const response = await api.post('/prayer-requests', data);
    return response.data;
  },

  async update(id, data) {
    const api = getApiClient();
    const response = await api.put(`/prayer-requests/${id}`, data);
    return response.data;
  },

  async delete(id) {
    const api = getApiClient();
    const response = await api.delete(`/prayer-requests/${id}`);
    return response.data;
  },

  async markAsAnswered(id) {
    const api = getApiClient();
    const response = await api.put(`/prayer-requests/${id}/mark-answered`);
    return response.data;
  },
};

/**
 * Task API Service
 */
export const TaskService = {
  async getAll(params = {}) {
    const api = getApiClient();
    const response = await api.get('/tasks', { params });
    return response.data;
  },

  async getById(id) {
    const api = getApiClient();
    const response = await api.get(`/tasks/${id}`);
    return response.data;
  },

  async create(data) {
    const api = getApiClient();
    const response = await api.post('/tasks', data);
    return response.data;
  },

  async update(id, data) {
    const api = getApiClient();
    const response = await api.put(`/tasks/${id}`, data);
    return response.data;
  },

  async delete(id) {
    const api = getApiClient();
    const response = await api.delete(`/tasks/${id}`);
    return response.data;
  },

  async markComplete(id) {
    const api = getApiClient();
    const response = await api.put(`/tasks/${id}/complete`);
    return response.data;
  },

  async getByStatus(status) {
    const api = getApiClient();
    const response = await api.get('/tasks/status', { params: { status } });
    return response.data;
  },
};

/**
 * Volunteer API Service
 */
export const VolunteerService = {
  async getAll(params = {}) {
    const api = getApiClient();
    const response = await api.get('/volunteers', { params });
    return response.data;
  },

  async getById(id) {
    const api = getApiClient();
    const response = await api.get(`/volunteers/${id}`);
    return response.data;
  },

  async create(data) {
    const api = getApiClient();
    const response = await api.post('/volunteers', data);
    return response.data;
  },

  async update(id, data) {
    const api = getApiClient();
    const response = await api.put(`/volunteers/${id}`, data);
    return response.data;
  },

  async delete(id) {
    const api = getApiClient();
    const response = await api.delete(`/volunteers/${id}`);
    return response.data;
  },

  async getByEvent(eventId) {
    const api = getApiClient();
    const response = await api.get(`/volunteers/event/${eventId}`);
    return response.data;
  },

  async assignToEvent(eventId, memberId, role = null) {
    const api = getApiClient();
    const response = await api.post(`/volunteers/assign`, { event_id: eventId, member_id: memberId, role });
    return response.data;
  },
};

/**
 * Sermon API Service
 */
export const SermonService = {
  async getAll(params = {}) {
    const api = getApiClient();
    const response = await api.get('/sermons', { params });
    return response.data;
  },

  async getById(id) {
    const api = getApiClient();
    const response = await api.get(`/sermons/${id}`);
    return response.data;
  },

  async create(data) {
    const api = getApiClient();
    const response = await api.post('/sermons', data);
    return response.data;
  },

  async update(id, data) {
    const api = getApiClient();
    const response = await api.put(`/sermons/${id}`, data);
    return response.data;
  },

  async delete(id) {
    const api = getApiClient();
    const response = await api.delete(`/sermons/${id}`);
    return response.data;
  },

  async getRecent(limit = 10) {
    const api = getApiClient();
    const response = await api.get('/sermons/recent', { params: { limit } });
    return response.data;
  },
};

export default {
  EventService,
  AttendanceService,
  DepartmentService,
  MemberService,
  DonationService,
  PrayerRequestService,
  TaskService,
  VolunteerService,
  SermonService,
};
