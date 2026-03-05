import axios from 'axios';
import { ref } from 'vue';

/**
 * Composable for handling API requests with loading state and error handling
 * @returns {Object} API utilities
 */
export function useApi() {
  const loading = ref(false);
  const error = ref(null);

  const getApiClient = () => {
    const token = localStorage.getItem('auth_token');
    return axios.create({
      baseURL: '/api',
      headers: {
        'Content-Type': 'application/json',
        ...(token && { Authorization: `Bearer ${token}` }),
      },
    });
  };

  const execute = async (request) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await request;
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  return { loading, error, getApiClient, execute };
}

/**
 * Composable for pagination handling
 */
export function usePagination() {
  const currentPage = ref(1);
  const pageSize = ref(15);
  const totalItems = ref(0);

  const totalPages = () => Math.ceil(totalItems.value / pageSize.value);
  const hasNextPage = () => currentPage.value < totalPages();
  const hasPrevPage = () => currentPage.value > 1;

  const nextPage = () => {
    if (hasNextPage()) currentPage.value++;
  };

  const prevPage = () => {
    if (hasPrevPage()) currentPage.value--;
  };

  const goToPage = (page) => {
    const pageNum = Math.max(1, Math.min(page, totalPages()));
    currentPage.value = pageNum;
  };

  const resetPagination = () => {
    currentPage.value = 1;
  };

  return {
    currentPage,
    pageSize,
    totalItems,
    totalPages,
    hasNextPage,
    hasPrevPage,
    nextPage,
    prevPage,
    goToPage,
    resetPagination,
  };
}

/**
 * Composable for search and filter handling
 */
export function useSearch() {
  const searchQuery = ref('');
  const filters = ref({});

  const applySearch = (data, fields) => {
    if (!searchQuery.value) return data;

    const query = searchQuery.value.toLowerCase();
    return data.filter(item =>
      fields.some(field => {
        const value = String(item[field] || '').toLowerCase();
        return value.includes(query);
      })
    );
  };

  const applyFilters = (data, filterConfig) => {
    return data.filter(item => {
      return Object.entries(filters.value).every(([key, value]) => {
        if (!value) return true;
        return item[key] === value || item[key]?.toString().includes(value);
      });
    });
  };

  const clearSearch = () => {
    searchQuery.value = '';
  };

  const clearFilters = () => {
    filters.value = {};
  };

  return {
    searchQuery,
    filters,
    applySearch,
    applyFilters,
    clearSearch,
    clearFilters,
  };
}

/**
 * Composable for dialog management
 */
export function useDialog() {
  const showDialog = ref(false);
  const editingItem = ref(null);
  const isEditing = ref(false);

  const openCreateDialog = () => {
    editingItem.value = null;
    isEditing.value = false;
    showDialog.value = true;
  };

  const openEditDialog = (item) => {
    editingItem.value = { ...item };
    isEditing.value = true;
    showDialog.value = true;
  };

  const closeDialog = () => {
    showDialog.value = false;
    editingItem.value = null;
    isEditing.value = false;
  };

  return {
    showDialog,
    editingItem,
    isEditing,
    openCreateDialog,
    openEditDialog,
    closeDialog,
  };
}

/**
 * Composable for form handling
 */
export function useForm(initialValue = {}) {
  const form = ref({ ...initialValue });
  const errors = ref({});
  const isSubmitting = ref(false);

  const updateForm = (updates) => {
    form.value = { ...form.value, ...updates };
    // Clear error for updated field
    Object.keys(updates).forEach(key => {
      delete errors.value[key];
    });
  };

  const setErrors = (newErrors) => {
    errors.value = newErrors || {};
  };

  const resetForm = () => {
    form.value = { ...initialValue };
    errors.value = {};
  };

  return {
    form,
    errors,
    isSubmitting,
    updateForm,
    setErrors,
    resetForm,
  };
}

/**
 * Composable for notification handling
 */
export function useNotification() {
  const notify = (message, type = 'info', duration = 3000) => {
    // This would integrate with your toast notification system
    console.log(`[${type.toUpperCase()}] ${message}`);
  };

  const success = (message) => notify(message, 'success');
  const error = (message) => notify(message, 'error');
  const warning = (message) => notify(message, 'warning');
  const info = (message) => notify(message, 'info');

  return { notify, success, error, warning, info };
}
