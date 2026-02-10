import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const token = ref(localStorage.getItem('token'));
    const church = ref(null);
    const isLoading = ref(false);

    const isAuthenticated = computed(() => !!token.value);
    const isAdmin = computed(() => user.value?.role === 'admin' || user.value?.is_admin);

    // Initialize user from localStorage
    const storedUser = localStorage.getItem('user');
    const storedChurch = localStorage.getItem('church');

    if (storedUser) {
        try {
            user.value = JSON.parse(storedUser);
        } catch (e) {
            console.error('Failed to parse stored user:', e);
            localStorage.removeItem('user');
        }
    }

    if (storedChurch) {
        try {
            church.value = JSON.parse(storedChurch);
        } catch (e) {
            console.error('Failed to parse stored church:', e);
            localStorage.removeItem('church');
        }
    }

    // Setup axios headers
    if (token.value) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
    }
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    axios.defaults.headers.common['Accept'] = 'application/json';

    // ADD THIS METHOD: Update user data
    const setUser = (userData) => {
        user.value = userData;
        localStorage.setItem('user', JSON.stringify(userData));
    };

    // ADD THIS METHOD: Update church data
    const setChurch = (churchData) => {
        church.value = churchData;
        if (churchData) {
            localStorage.setItem('church', JSON.stringify(churchData));
        }
    };

    const login = async (credentials) => {
        isLoading.value = true;
        try {
            const response = await axios.post('/api/login', credentials);
            const { token: authToken, user: userData, church: churchData } = response.data;

            token.value = authToken;
            localStorage.setItem('token', authToken);
            axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;

            // Use the new setUser and setChurch methods
            setUser(userData);
            setChurch(churchData);

            return { success: true, data: response.data };
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Login failed. Please check your credentials.',
                errors: error.response?.data?.errors
            };
        } finally {
            isLoading.value = false;
        }
    };

    const register = async (data) => {
        isLoading.value = true;
        try {
            console.log('Sending registration to /api/register/church with data:', data);

            const response = await axios.post('/api/register/church', data);
            console.log('Registration response:', response.data);

            if (response.data.success) {
                const { token: authToken, user: userData, church: churchData } = response.data;

                token.value = authToken;
                localStorage.setItem('token', authToken);
                axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;

                // Use the new setUser and setChurch methods
                setUser(userData);
                setChurch(churchData);

                return {
                    success: true,
                    data: response.data,
                    user: userData,
                    church: churchData
                };
            } else {
                return {
                    success: false,
                    message: response.data.message || 'Registration failed'
                };
            }
        } catch (error) {
            console.error('Registration error:', error);
            console.error('Error response:', error.response);

            if (error.response?.status === 422) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Validation failed',
                    errors: error.response?.data?.errors
                };
            } else if (error.response?.status === 409) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Church name or email already exists'
                };
            } else {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Registration failed. Please try again.'
                };
            }
        } finally {
            isLoading.value = false;
        }
    };

    const logout = async () => {
        try {
            await axios.post('/api/logout');
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            token.value = null;
            user.value = null;
            church.value = null;
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            localStorage.removeItem('church');
            delete axios.defaults.headers.common['Authorization'];
        }
    };

    const fetchUser = async () => {
        if (!token.value) return null;

        try {
            const response = await axios.get('/api/user');
            // Use the new setUser and setChurch methods
            setUser(response.data.user);
            setChurch(response.data.church);

            return user.value;
        } catch (error) {
            console.error('Failed to fetch user:', error);
            // Don't call logout here to avoid circular dependency
            token.value = null;
            user.value = null;
            church.value = null;
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            localStorage.removeItem('church');
            delete axios.defaults.headers.common['Authorization'];
            return null;
        }
    };

    const updateProfile = async (data) => {
        try {
            const response = await axios.put('/api/user/profile', data);
            // Use setUser to update the user in store and localStorage
            setUser(response.data.user);

            return { success: true, user: response.data.user };
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Update failed'
            };
        }
    };

    return {
        user,
        token,
        church,
        isLoading,
        isAuthenticated,
        isAdmin,
        setUser, // Export the setUser method
        setChurch, // Export the setChurch method
        login,
        register,
        logout,
        fetchUser,
        updateProfile
    };
});
