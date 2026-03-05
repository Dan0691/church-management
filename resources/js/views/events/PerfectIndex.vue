<template>
  <div>
    <!-- Page Header with Breadcrumbs -->
    <v-breadcrumbs class="mb-4" :items="breadcrumbs" divider=">"></v-breadcrumbs>

    <!-- Page Header with Actions -->
    <div class="d-flex justify-space-between align-center mb-6 flex-wrap gap-3">
      <div>
        <h1 class="text-h4 font-weight-bold">Events</h1>
        <p class="text-body-1 text-medium-emphasis mt-1">
          Manage church events and schedules ({{ events.length }} total)
        </p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <v-btn
          color="primary"
          prepend-icon="mdi-calendar-plus"
          @click="openCreateDialog"
          class="mb-1"
        >
          Add Event
        </v-btn>
        <v-btn
          variant="outlined"
          prepend-icon="mdi-refresh"
          @click="fetchEvents"
          :loading="loading"
          class="mb-1"
        >
          Refresh
        </v-btn>
        <v-btn
          variant="outlined"
          prepend-icon="mdi-download"
          @click="exportEvents"
          class="mb-1"
        >
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
        <v-card class="stats-card" @click="activeFilter = 'all'">
          <v-card-text class="d-flex align-center">
            <v-avatar color="primary" size="56" class="mr-4">
              <v-icon size="32">mdi-calendar</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ events.length }}</div>
              <div class="text-caption text-medium-emphasis">Total Events</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card" @click="activeFilter = 'upcoming'">
          <v-card-text class="d-flex align-center">
            <v-avatar color="success" size="56" class="mr-4">
              <v-icon size="32">mdi-calendar-check</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ upcomingCount }}</div>
              <div class="text-caption text-medium-emphasis">Upcoming</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card" @click="activeFilter = 'past'">
          <v-card-text class="d-flex align-center">
            <v-avatar color="warning" size="56" class="mr-4">
              <v-icon size="32">mdi-calendar-clock</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ pastCount }}</div>
              <div class="text-caption text-medium-emphasis">Past Events</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card">
          <v-card-text class="d-flex align-center">
            <v-avatar color="info" size="56" class="mr-4">
              <v-icon size="32">mdi-people</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ totalAttendance }}</div>
              <div class="text-caption text-medium-emphasis">Total Attendance</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Filters -->
    <v-card class="mb-6">
      <v-card-text>
        <div class="d-flex gap-2 flex-wrap">
          <v-btn
            v-for="filter in filters"
            :key="filter.value"
            :color="activeFilter === filter.value ? 'primary' : 'default'"
            :variant="activeFilter === filter.value ? 'elevated' : 'outlined'"
            size="small"
            @click="activeFilter = filter.value"
          >
            {{ filter.label }}
          </v-btn>
        </div>
        <v-divider class="my-4"></v-divider>
        <v-row>
          <v-col cols="12" sm="6" md="4">
            <v-text-field
              v-model="searchQuery"
              label="Search events"
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="compact"
              clearable
            ></v-text-field>
          </v-col>
          <v-col cols="12" sm="6" md="4">
            <v-select
              v-model="selectedType"
              label="Filter by type"
              :items="eventTypes"
              item-title="label"
              item-value="value"
              variant="outlined"
              density="compact"
              clearable
            ></v-select>
          </v-col>
          <v-col cols="12" sm="6" md="4">
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
      </v-card-text>
    </v-card>

    <!-- Events Table -->
    <v-card :loading="loading">
      <v-table v-if="filteredEvents.length > 0">
        <thead>
          <tr>
            <th class="text-left">Event Name</th>
            <th class="text-left">Date & Time</th>
            <th class="text-left">Location</th>
            <th class="text-left">Type</th>
            <th class="text-left">Attendance</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="event in filteredEvents" :key="event.id">
            <td>
              <div class="font-weight-bold">{{ event.title }}</div>
              <div class="text-caption text-medium-emphasis">{{ event.description }}</div>
            </td>
            <td>
              <div>{{ formatDate(event.start_date) }}</div>
              <div class="text-caption text-medium-emphasis">{{ formatTime(event.start_date) }}</div>
            </td>
            <td>{{ event.location || 'N/A' }}</td>
            <td>
              <v-chip :color="getTypeColor(event.type)" size="small" text-color="white">
                {{ event.type }}
              </v-chip>
            </td>
            <td>
              <v-progress-linear
                :value="event.attendance_count || 0"
                max="event.expected_attendance || 100"
                height="20"
                class="mb-2"
              ></v-progress-linear>
              <div class="text-caption">
                {{ event.attendance_count || 0 }} / {{ event.expected_attendance || '?' }}
              </div>
            </td>
            <td class="text-center">
              <v-btn
                icon="mdi-pencil"
                size="x-small"
                variant="text"
                @click="editEvent(event)"
              ></v-btn>
              <v-btn
                icon="mdi-delete"
                size="x-small"
                variant="text"
                color="error"
                @click="deleteEventConfirm(event)"
              ></v-btn>
            </td>
          </tr>
        </tbody>
      </v-table>
      <v-card-text v-else class="text-center py-8">
        <v-icon size="64" class="mb-4 text-medium-emphasis">mdi-calendar-blank</v-icon>
        <p class="text-body-1 text-medium-emphasis">No events found</p>
      </v-card-text>
    </v-card>

    <!-- Form Dialog -->
    <v-dialog v-model="showDialog" max-width="700px" persistent>
      <v-card>
        <v-card-title class="bg-primary text-white">
          <v-icon left>{{ isEditing ? 'mdi-pencil' : 'mdi-calendar-plus' }}</v-icon>
          {{ isEditing ? 'Edit Event' : 'Add New Event' }}
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
                <v-text-field
                  v-model="formData.title"
                  label="Event Title *"
                  prepend-icon="mdi-text"
                  variant="outlined"
                  :rules="[v => !!v || 'Title is required']"
                  required
                ></v-text-field>
              </v-col>
            </v-row>

            <v-row>
              <v-col cols="12">
                <v-textarea
                  v-model="formData.description"
                  label="Description"
                  variant="outlined"
                  rows="3"
                ></v-textarea>
              </v-col>
            </v-row>

            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formData.start_date"
                  label="Start Date & Time *"
                  type="datetime-local"
                  variant="outlined"
                  :rules="[v => !!v || 'Start date is required']"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formData.end_date"
                  label="End Date & Time"
                  type="datetime-local"
                  variant="outlined"
                ></v-text-field>
              </v-col>
            </v-row>

            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formData.location"
                  label="Location"
                  prepend-icon="mdi-map-marker"
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="formData.type"
                  label="Event Type"
                  :items="eventTypes"
                  item-title="label"
                  item-value="value"
                  variant="outlined"
                ></v-select>
              </v-col>
            </v-row>

            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model.number="formData.expected_attendance"
                  label="Expected Attendance"
                  type="number"
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
                  {{ isSubmitting ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
                </v-btn>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="showDeleteDialog" max-width="400px">
      <v-card>
        <v-card-title>Delete Event?</v-card-title>
        <v-card-text>
          Are you sure you want to delete "<strong>{{ selectedEvent?.title }}</strong>"?
          This action cannot be undone.
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
import { ref, computed, onMounted, watch } from 'vue';
import { useEventStore } from '@/stores/moduleStore';

const eventStore = useEventStore();

// Refs
const breadcrumbs = ref([
  { title: 'Dashboard', href: '/' },
  { title: 'Events', disabled: true },
]);

const eventTypes = ref([
  { label: 'Service', value: 'service' },
  { label: 'Conference', value: 'conference' },
  { label: 'Workshop', value: 'workshop' },
  { label: 'Outreach', value: 'outreach' },
  { label: 'Social', value: 'social' },
  { label: 'Other', value: 'other' },
]);

const statusOptions = ref([
  { label: 'Scheduled', value: 'scheduled' },
  { label: 'Ongoing', value: 'ongoing' },
  { label: 'Completed', value: 'completed' },
  { label: 'Cancelled', value: 'cancelled' },
]);

const filters = ref([
  { label: 'All Events', value: 'all' },
  { label: 'Upcoming', value: 'upcoming' },
  { label: 'Past', value: 'past' },
]);

const showDialog = ref(false);
const showDeleteDialog = ref(false);
const isEditing = ref(false);
const isSubmitting = ref(false);
const formError = ref(null);
const activeFilter = ref('all');
const searchQuery = ref('');
const selectedType = ref(null);
const selectedStatus = ref(null);
const selectedEvent = ref(null);
const formRef = ref(null);

const formData = ref({
  title: '',
  description: '',
  start_date: '',
  end_date: '',
  location: '',
  type: 'service',
  expected_attendance: 0,
  status: 'scheduled',
});

const initialFormData = () => ({
  title: '',
  description: '',
  start_date: '',
  end_date: '',
  location: '',
  type: 'service',
  expected_attendance: 0,
  status: 'scheduled',
});

// Computed
const events = computed(() => eventStore.events);
const loading = computed(() => eventStore.loading);
const error = computed(() => eventStore.error);

const upcomingCount = computed(() =>
  events.value.filter(e => new Date(e.start_date) > new Date()).length
);

const pastCount = computed(() =>
  events.value.filter(e => new Date(e.start_date) <= new Date()).length
);

const totalAttendance = computed(() =>
  events.value.reduce((sum, e) => sum + (e.attendance_count || 0), 0)
);

const filteredEvents = computed(() => {
  let filtered = events.value;

  // Filter by status
  if (activeFilter.value === 'upcoming') {
    filtered = filtered.filter(e => new Date(e.start_date) > new Date());
  } else if (activeFilter.value === 'past') {
    filtered = filtered.filter(e => new Date(e.start_date) <= new Date());
  }

  // Filter by type
  if (selectedType.value) {
    filtered = filtered.filter(e => e.type === selectedType.value);
  }

  // Filter by status
  if (selectedStatus.value) {
    filtered = filtered.filter(e => e.status === selectedStatus.value);
  }

  // Search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(e =>
      e.title?.toLowerCase().includes(query) ||
      e.description?.toLowerCase().includes(query) ||
      e.location?.toLowerCase().includes(query)
    );
  }

  return filtered.sort((a, b) => new Date(a.start_date) - new Date(b.start_date));
});

// Methods
const fetchEvents = async () => {
  await eventStore.fetchEvents();
};

const openCreateDialog = () => {
  formData.value = initialFormData();
  isEditing.value = false;
  formError.value = null;
  showDialog.value = true;
};

const editEvent = (event) => {
  selectedEvent.value = event;
  formData.value = { ...event };
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
      await eventStore.updateEvent(selectedEvent.value.id, formData.value);
    } else {
      await eventStore.createEvent(formData.value);
    }
    closeDialog();
  } catch (err) {
    formError.value = err.message || 'An error occurred';
  } finally {
    isSubmitting.value = false;
  }
};

const deleteEventConfirm = (event) => {
  selectedEvent.value = event;
  showDeleteDialog.value = true;
};

const confirmDelete = async () => {
  try {
    await eventStore.deleteEvent(selectedEvent.value.id);
    showDeleteDialog.value = false;
  } catch (err) {
    formError.value = err.message;
  }
};

const exportEvents = () => {
  // Implement export functionality
  console.log('Exporting events...');
};

const getTypeColor = (type) => {
  const colors = {
    service: 'primary',
    conference: 'info',
    workshop: 'success',
    outreach: 'warning',
    social: 'purple',
  };
  return colors[type] || 'default';
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const formatTime = (date) => {
  return new Date(date).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
  });
};

// Lifecycle
onMounted(() => {
  fetchEvents();
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
