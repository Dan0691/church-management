import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { EventService, AttendanceService, DepartmentService, MemberService } from '@/services/ApiServices';

/**
 * Events Store
 */
export const useEventStore = defineStore('events', () => {
  const events = ref([]);
  const loading = ref(false);
  const error = ref(null);
  const selectedEvent = ref(null);

  const upcomingEvents = computed(() =>
    events.value.filter(e => new Date(e.start_date) > new Date()).sort((a, b) =>
      new Date(a.start_date) - new Date(b.start_date)
    )
  );

  const pastEvents = computed(() =>
    events.value.filter(e => new Date(e.start_date) <= new Date()).sort((a, b) =>
      new Date(b.start_date) - new Date(a.start_date)
    )
  );

  const fetchEvents = async (filters = {}) => {
    loading.value = true;
    try {
      const response = await EventService.getAll(filters);
      // Handle both array and paginated responses
      events.value = Array.isArray(response.data) ? response.data : (response.data?.data || []);
      error.value = null;
    } catch (err) {
      error.value = err.message || 'Failed to fetch events';
      events.value = [];
      console.error('Error fetching events:', err);
    } finally {
      loading.value = false;
    }
  };

  const fetchEvent = async (id) => {
    loading.value = true;
    try {
      const response = await EventService.getById(id);
      selectedEvent.value = response.data;
      error.value = null;
    } catch (err) {
      error.value = err.message || 'Failed to fetch event';
    } finally {
      loading.value = false;
    }
  };

  const createEvent = async (data) => {
    loading.value = true;
    try {
      const response = await EventService.create(data);
      events.value.push(response.data);
      error.value = null;
      return response.data;
    } catch (err) {
      error.value = err.message || 'Failed to create event';
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  const updateEvent = async (id, data) => {
    loading.value = true;
    try {
      const response = await EventService.update(id, data);
      const index = events.value.findIndex(e => e.id === id);
      if (index !== -1) events.value[index] = response.data;
      error.value = null;
      return response.data;
    } catch (err) {
      error.value = err.message || 'Failed to update event';
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  const deleteEvent = async (id) => {
    loading.value = true;
    try {
      await EventService.delete(id);
      events.value = events.value.filter(e => e.id !== id);
      error.value = null;
    } catch (err) {
      error.value = err.message || 'Failed to delete event';
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  const fetchUpcomingEvents = async (limit = 10) => {
    try {
      const response = await EventService.getUpcoming(limit);
      return response.data || [];
    } catch (err) {
      error.value = err.message;
      return [];
    }
  };

  return {
    events,
    loading,
    error,
    selectedEvent,
    upcomingEvents,
    pastEvents,
    fetchEvents,
    fetchEvent,
    createEvent,
    updateEvent,
    deleteEvent,
    fetchUpcomingEvents,
  };
});

/**
 * Attendance Store
 */
export const useAttendanceStore = defineStore('attendance', () => {
  const records = ref([]);
  const loading = ref(false);
  const error = ref(null);
  const selectedRecord = ref(null);

  const fetchAttendanceRecords = async (filters = {}) => {
    loading.value = true;
    try {
      const response = await AttendanceService.getAll(filters);
      // Handle both array and paginated responses
      records.value = Array.isArray(response.data) ? response.data : (response.data?.data || []);
      error.value = null;
    } catch (err) {
      error.value = err.message || 'Failed to fetch attendance records';
      records.value = [];
      console.error('Error fetching attendance:', err);
    } finally {
      loading.value = false;
    }
  };

  const fetchRecord = async (id) => {
    loading.value = true;
    try {
      const response = await AttendanceService.getById(id);
      selectedRecord.value = response.data;
      error.value = null;
    } catch (err) {
      error.value = err.message;
    } finally {
      loading.value = false;
    }
  };

  const createRecord = async (data) => {
    loading.value = true;
    try {
      const response = await AttendanceService.create(data);
      records.value.push(response.data);
      error.value = null;
      return response.data;
    } catch (err) {
      error.value = err.message || 'Failed to create attendance record';
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  const bulkCreateRecords = async (data) => {
    loading.value = true;
    try {
      const response = await AttendanceService.bulkCreate(data);
      records.value.push(...response.data);
      error.value = null;
      return response.data;
    } catch (err) {
      error.value = err.message || 'Failed to create attendance records';
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  const updateRecord = async (id, data) => {
    loading.value = true;
    try {
      const response = await AttendanceService.update(id, data);
      const index = records.value.findIndex(r => r.id === id);
      if (index !== -1) records.value[index] = response.data;
      error.value = null;
      return response.data;
    } catch (err) {
      error.value = err.message || 'Failed to update attendance record';
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  const deleteRecord = async (id) => {
    loading.value = true;
    try {
      await AttendanceService.delete(id);
      records.value = records.value.filter(r => r.id !== id);
      error.value = null;
    } catch (err) {
      error.value = err.message || 'Failed to delete attendance record';
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  const fetchTodayAttendance = async () => {
    try {
      const response = await AttendanceService.getTodayAttendance();
      return response.data || [];
    } catch (err) {
      error.value = err.message;
      return [];
    }
  };

  return {
    records,
    loading,
    error,
    selectedRecord,
    fetchAttendanceRecords,
    fetchRecord,
    createRecord,
    bulkCreateRecords,
    updateRecord,
    deleteRecord,
    fetchTodayAttendance,
  };
});

/**
 * Department Store
 */
export const useDepartmentStore = defineStore('departments', () => {
  const departments = ref([]);
  const loading = ref(false);
  const error = ref(null);
  const selectedDepartment = ref(null);

  const fetchDepartments = async (filters = {}) => {
    loading.value = true;
    try {
      const response = await DepartmentService.getAll(filters);
      // Handle both array and paginated responses
      departments.value = Array.isArray(response.data) ? response.data : (response.data?.data || []);
      error.value = null;
    } catch (err) {
      error.value = err.message || 'Failed to fetch departments';
      departments.value = [];
      console.error('Error fetching departments:', err);
    } finally {
      loading.value = false;
    }
  };

  const fetchDepartment = async (id) => {
    loading.value = true;
    try {
      const response = await DepartmentService.getById(id);
      selectedDepartment.value = response.data;
      error.value = null;
    } catch (err) {
      error.value = err.message;
    } finally {
      loading.value = false;
    }
  };

  const createDepartment = async (data) => {
    loading.value = true;
    try {
      const response = await DepartmentService.create(data);
      departments.value.push(response.data);
      error.value = null;
      return response.data;
    } catch (err) {
      error.value = err.message || 'Failed to create department';
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  const updateDepartment = async (id, data) => {
    loading.value = true;
    try {
      const response = await DepartmentService.update(id, data);
      const index = departments.value.findIndex(d => d.id === id);
      if (index !== -1) departments.value[index] = response.data;
      error.value = null;
      return response.data;
    } catch (err) {
      error.value = err.message || 'Failed to update department';
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  const deleteDepartment = async (id) => {
    loading.value = true;
    try {
      await DepartmentService.delete(id);
      departments.value = departments.value.filter(d => d.id !== id);
      error.value = null;
    } catch (err) {
      error.value = err.message || 'Failed to delete department';
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  const addMember = async (departmentId, memberId, role = null) => {
    try {
      const response = await DepartmentService.addMember(departmentId, memberId, role);
      error.value = null;
      return response.data;
    } catch (err) {
      error.value = err.message;
      throw error.value;
    }
  };

  const removeMember = async (departmentId, memberId) => {
    try {
      await DepartmentService.removeMember(departmentId, memberId);
      error.value = null;
    } catch (err) {
      error.value = err.message;
      throw error.value;
    }
  };

  return {
    departments,
    loading,
    error,
    selectedDepartment,
    fetchDepartments,
    fetchDepartment,
    createDepartment,
    updateDepartment,
    deleteDepartment,
    addMember,
    removeMember,
  };
});

/**
 * Member Store
 */
export const useMemberStore = defineStore('members', () => {
  const members = ref([]);
  const loading = ref(false);
  const error = ref(null);
  const selectedMember = ref(null);
  const stats = ref({ total: 0, active: 0, visitors: 0, new_this_month: 0 });

  const fetchMembers = async (filters = {}) => {
    loading.value = true;
    try {
      const response = await MemberService.getAll(filters);
      // Handle both array and paginated responses
      members.value = Array.isArray(response.data) ? response.data : (response.data?.data || []);
      // Extract stats if available
      if (response.stats) {
        stats.value = response.stats;
      }
      error.value = null;
    } catch (err) {
      error.value = err.message || 'Failed to fetch members';
      members.value = [];
      console.error('Error fetching members:', err);
    } finally {
      loading.value = false;
    }
  };

  const fetchMember = async (id) => {
    loading.value = true;
    try {
      const response = await MemberService.getById(id);
      selectedMember.value = response.data;
      error.value = null;
    } catch (err) {
      error.value = err.message;
    } finally {
      loading.value = false;
    }
  };

  const createMember = async (data) => {
    loading.value = true;
    try {
      const response = await MemberService.create(data);
      members.value.push(response.data);
      error.value = null;
      return response.data;
    } catch (err) {
      error.value = err.message || 'Failed to create member';
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  const updateMember = async (id, data) => {
    loading.value = true;
    try {
      const response = await MemberService.update(id, data);
      const index = members.value.findIndex(m => m.id === id);
      if (index !== -1) members.value[index] = response.data;
      error.value = null;
      return response.data;
    } catch (err) {
      error.value = err.message || 'Failed to update member';
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  const deleteMember = async (id) => {
    loading.value = true;
    try {
      await MemberService.delete(id);
      members.value = members.value.filter(m => m.id !== id);
      error.value = null;
    } catch (err) {
      error.value = err.message || 'Failed to delete member';
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  const searchMembers = async (query) => {
    loading.value = true;
    try {
      const response = await MemberService.search(query);
      return response.data || [];
    } catch (err) {
      error.value = err.message;
      return [];
    } finally {
      loading.value = false;
    }
  };

  return {
    members,
    loading,
    error,
    selectedMember,
    stats,
    fetchMembers,
    fetchMember,
    createMember,
    updateMember,
    deleteMember,
    searchMembers,
  };
});
