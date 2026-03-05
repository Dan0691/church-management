<template>
  <div>
    <!-- Page Header -->
    <v-breadcrumbs class="mb-4" :items="breadcrumbs" divider=">"></v-breadcrumbs>

    <!-- Page Title -->
    <div class="d-flex justify-space-between align-center mb-6 flex-wrap gap-3">
      <div>
        <h1 class="text-h4 font-weight-bold">Attendance Management</h1>
        <p class="text-body-1 text-medium-emphasis mt-1">
          Track attendance across all events ({{ records.length }} records)
        </p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="openCreateDialog"
          class="mb-1"
        >
          Record Attendance
        </v-btn>
        <v-btn
          variant="outlined"
          prepend-icon="mdi-refresh"
          @click="fetchRecords"
          :loading="loading"
          class="mb-1"
        >
          Refresh
        </v-btn>
        <v-btn variant="outlined" prepend-icon="mdi-download" @click="exportData" class="mb-1">
          Export
        </v-btn>
      </div>
    </div>

    <!-- Error Alert -->
    <v-alert v-if="error" type="error" dismissible closable class="mb-4">
      {{ error }}
    </v-alert>

    <!-- Stats Cards -->
    <v-row class="mb-6">
      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card">
          <v-card-text class="d-flex align-center">
            <v-avatar color="primary" size="56" class="mr-4">
              <v-icon size="32">mdi-account-group</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ stats.totalRecords }}</div>
              <div class="text-caption text-medium-emphasis">Total Records</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card" @click="timeFilter = 'today'">
          <v-card-text class="d-flex align-center">
            <v-avatar color="success" size="56" class="mr-4">
              <v-icon size="32">mdi-calendar-today</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ stats.todayCount }}</div>
              <div class="text-caption text-medium-emphasis">Today's Attendance</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card" @click="timeFilter = 'week'">
          <v-card-text class="d-flex align-center">
            <v-avatar color="info" size="56" class="mr-4">
              <v-icon size="32">mdi-calendar-week</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ stats.weekCount }}</div>
              <div class="text-caption text-medium-emphasis">This Week</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card" @click="timeFilter = 'month'">
          <v-card-text class="d-flex align-center">
            <v-avatar color="warning" size="56" class="mr-4">
              <v-icon size="32">mdi-calendar-month</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ stats.monthCount }}</div>
              <div class="text-caption text-medium-emphasis">This Month</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Filters -->
    <v-card class="mb-6">
      <v-card-text>
        <v-row>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="searchQuery"
              label="Search by member name or event"
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="compact"
              clearable
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="4">
            <v-select
              v-model="selectedEvent"
              label="Filter by event"
              :items="availableEvents"
              item-title="title"
              item-value="id"
              variant="outlined"
              density="compact"
              clearable
            ></v-select>
          </v-col>
          <v-col cols="12" md="4">
            <v-select
              v-model="selectedStatus"
              label="Filter by status"
              :items="statusOptions"
              item-title="label"
              item-value="value"
              variant="outlined"
              density="compact"
              clearable
            ></v-select>
          </v-col>
        </v-row>
        <v-divider class="my-4"></v-divider>
        <div class="d-flex gap-2 flex-wrap">
          <v-chip
            v-for="filter in timeFilters"
            :key="filter.value"
            :color="timeFilter === filter.value ? 'primary' : 'default'"
            @click="timeFilter = filter.value"
            class="cursor-pointer"
          >
            {{ filter.label }}
          </v-chip>
        </div>
      </v-card-text>
    </v-card>

    <!-- Attendance Records Table -->
    <v-card :loading="loading">
      <v-table v-if="filteredRecords.length > 0">
        <thead>
          <tr>
            <th class="text-left">Member</th>
            <th class="text-left">Event</th>
            <th class="text-left">Date</th>
            <th class="text-left">Status</th>
            <th class="text-left">Check-in Time</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="record in filteredRecords" :key="record.id">
            <td>
              <div class="font-weight-bold">{{ record.member?.full_name || 'Unknown' }}</div>
              <div class="text-caption text-medium-emphasis">{{ record.member?.email }}</div>
            </td>
            <td>
              <div class="font-weight-bold">{{ record.event?.title || 'N/A' }}</div>
            </td>
            <td>{{ formatDate(record.created_at) }}</td>
            <td>
              <v-chip
                :color="getStatusColor(record.status)"
                size="small"
                text-color="white"
              >
                {{ record.status }}
              </v-chip>
            </td>
            <td>{{ formatTime(record.check_in_time) }}</td>
            <td class="text-center">
              <v-btn
                icon="mdi-pencil"
                size="x-small"
                variant="text"
                @click="editRecord(record)"
              ></v-btn>
              <v-btn
                icon="mdi-delete"
                size="x-small"
                variant="text"
                color="error"
                @click="deleteRecordConfirm(record)"
              ></v-btn>
            </td>
          </tr>
        </tbody>
      </v-table>
      <v-card-text v-else class="text-center py-8">
        <v-icon size="64" class="mb-4 text-medium-emphasis">mdi-clipboard-list</v-icon>
        <p class="text-body-1 text-medium-emphasis">No attendance records found</p>
      </v-card-text>
    </v-card>

    <!-- Form Dialog -->
    <v-dialog v-model="showDialog" max-width="600px" persistent>
      <v-card>
        <v-card-title class="bg-primary text-white">
          <v-icon left>{{ isEditing ? 'mdi-pencil' : 'mdi-plus' }}</v-icon>
          {{ isEditing ? 'Edit Attendance' : 'Record Attendance' }}
        </v-card-title>

        <v-card-text class="pt-6">
          <v-form ref="formRef" @submit.prevent="submitForm">
            <!-- Error Alert -->
            <v-alert v-if="formError" type="error" dismissible class="mb-4">
              {{ formError }}
            </v-alert>

            <!-- Form Fields -->
            <v-row>
              <v-col cols="12">
                <v-select
                  v-model="formData.member_id"
                  label="Member *"
                  :items="members"
                  item-title="full_name"
                  item-value="id"
                  variant="outlined"
                  :rules="[v => !!v || 'Member is required']"
                  required
                  filterable
                ></v-select>
              </v-col>
            </v-row>

            <v-row>
              <v-col cols="12">
                <v-select
                  v-model="formData.event_id"
                  label="Event *"
                  :items="availableEvents"
                  item-title="title"
                  item-value="id"
                  variant="outlined"
                  :rules="[v => !!v || 'Event is required']"
                  required
                ></v-select>
              </v-col>
            </v-row>

            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formData.check_in_time"
                  label="Check-in Time"
                  type="datetime-local"
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="formData.status"
                  label="Status"
                  :items="statusOptions"
                  item-title="label"
                  item-value="value"
                  variant="outlined"
                ></v-select>
              </v-col>
            </v-row>

            <v-row>
              <v-col cols="12">
                <v-textarea
                  v-model="formData.notes"
                  label="Notes"
                  variant="outlined"
                  rows="2"
                ></v-textarea>
              </v-col>
            </v-row>

            <!-- Form Actions -->
            <v-row class="mt-6">
              <v-col cols="6">
                <v-btn variant="outlined" block @click="closeDialog" :disabled="isSubmitting">
                  Cancel
                </v-btn>
              </v-col>
              <v-col cols="6">
                <v-btn
                  color="primary"
                  block
                  type="submit"
                  :loading="isSubmitting"
                  :disabled="isSubmitting"
                >
                  {{ isSubmitting ? 'Saving...' : (isEditing ? 'Update' : 'Record') }}
                </v-btn>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation -->
    <v-dialog v-model="showDeleteDialog" max-width="400px">
      <v-card>
        <v-card-title>Delete Record?</v-card-title>
        <v-card-text>
          Are you sure you want to delete this attendance record? This action cannot be undone.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="showDeleteDialog = false">Cancel</v-btn>
          <v-btn color="error" @click="confirmDelete">Delete</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAttendanceStore } from '@/stores/moduleStore';
import { useEventStore } from '@/stores/moduleStore';
import { useMemberStore } from '@/stores/moduleStore';

const attendanceStore = useAttendanceStore();
const eventStore = useEventStore();
const memberStore = useMemberStore();

const breadcrumbs = ref([
  { title: 'Dashboard', href: '/' },
  { title: 'Attendance', disabled: true },
]);

const statusOptions = ref([
  { label: 'Present', value: 'present' },
  { label: 'Absent', value: 'absent' },
  { label: 'Late', value: 'late' },
  { label: 'Excused', value: 'excused' },
]);

const timeFilters = ref([
  { label: 'Today', value: 'today' },
  { label: 'This Week', value: 'week' },
  { label: 'This Month', value: 'month' },
  { label: 'All Time', value: 'all' },
]);

const showDialog = ref(false);
const showDeleteDialog = ref(false);
const isEditing = ref(false);
const isSubmitting = ref(false);
const formError = ref(null);
const timeFilter = ref('all');
const searchQuery = ref('');
const selectedEvent = ref(null);
const selectedStatus = ref(null);
const selectedRecord = ref(null);
const formRef = ref(null);

const formData = ref({
  member_id: null,
  event_id: null,
  check_in_time: new Date().toISOString().slice(0, 16),
  status: 'present',
  notes: '',
});

const initialFormData = () => ({
  member_id: null,
  event_id: null,
  check_in_time: new Date().toISOString().slice(0, 16),
  status: 'present',
  notes: '',
});

// Computed
const records = computed(() => attendanceStore.records);
const loading = computed(() => attendanceStore.loading);
const error = computed(() => attendanceStore.error);
const members = computed(() => memberStore.members);
const availableEvents = computed(() => eventStore.events);

const stats = computed(() => {
  const now = new Date();
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  const weekStart = new Date(today);
  weekStart.setDate(weekStart.getDate() - weekStart.getDay());
  const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);

  return {
    totalRecords: records.value.length,
    todayCount: records.value.filter(r =>
      new Date(r.created_at).toDateString() === today.toDateString()
    ).length,
    weekCount: records.value.filter(r =>
      new Date(r.created_at) >= weekStart
    ).length,
    monthCount: records.value.filter(r =>
      new Date(r.created_at) >= monthStart
    ).length,
  };
});

const filteredRecords = computed(() => {
  let filtered = records.value;

  // Time filter
  const now = new Date();
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  const weekStart = new Date(today);
  weekStart.setDate(weekStart.getDate() - weekStart.getDay());
  const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);

  if (timeFilter.value === 'today') {
    filtered = filtered.filter(r =>
      new Date(r.created_at).toDateString() === today.toDateString()
    );
  } else if (timeFilter.value === 'week') {
    filtered = filtered.filter(r => new Date(r.created_at) >= weekStart);
  } else if (timeFilter.value === 'month') {
    filtered = filtered.filter(r => new Date(r.created_at) >= monthStart);
  }

  // Event filter
  if (selectedEvent.value) {
    filtered = filtered.filter(r => r.event_id === selectedEvent.value);
  }

  // Status filter
  if (selectedStatus.value) {
    filtered = filtered.filter(r => r.status === selectedStatus.value);
  }

  // Search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(r =>
      r.member?.full_name?.toLowerCase().includes(query) ||
      r.member?.email?.toLowerCase().includes(query) ||
      r.event?.title?.toLowerCase().includes(query)
    );
  }

  return filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});

// Methods
const fetchRecords = async () => {
  await attendanceStore.fetchAttendanceRecords();
  await eventStore.fetchEvents();
  await memberStore.fetchMembers();
};

const openCreateDialog = () => {
  formData.value = initialFormData();
  isEditing.value = false;
  formError.value = null;
  showDialog.value = true;
};

const editRecord = (record) => {
  selectedRecord.value = record;
  formData.value = { ...record };
  isEditing.value = true;
  formError.value = null;
  showDialog.value = true;
};

const closeDialog = () => {
  showDialog.value = false;
  formData.value = initialFormData();
  isEditing.value = false;
  formError.value = null;
};

const submitForm = async () => {
  if (!await formRef.value?.validate()) return;

  isSubmitting.value = true;
  formError.value = null;

  try {
    if (isEditing.value) {
      await attendanceStore.updateRecord(selectedRecord.value.id, formData.value);
    } else {
      await attendanceStore.createRecord(formData.value);
    }
    closeDialog();
  } catch (err) {
    formError.value = err.message || 'An error occurred';
  } finally {
    isSubmitting.value = false;
  }
};

const deleteRecordConfirm = (record) => {
  selectedRecord.value = record;
  showDeleteDialog.value = true;
};

const confirmDelete = async () => {
  try {
    await attendanceStore.deleteRecord(selectedRecord.value.id);
    showDeleteDialog.value = false;
  } catch (err) {
    formError.value = err.message;
  }
};

const exportData = () => {
  console.log('Exporting attendance data...');
};

const getStatusColor = (status) => {
  const colors = {
    present: 'success',
    absent: 'error',
    late: 'warning',
    excused: 'info',
  };
  return colors[status] || 'default';
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const formatTime = (time) => {
  if (!time) return 'N/A';
  return new Date(time).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
  });
};

// Lifecycle
onMounted(() => {
  fetchRecords();
});
</script>

<style scoped>
.stats-card {
  cursor: pointer;
  transition: all 0.3s ease;
}

.stats-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}
</style>
