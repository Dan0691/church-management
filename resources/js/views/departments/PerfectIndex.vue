<template>
  <div>
    <!-- Breadcrumbs -->
    <v-breadcrumbs class="mb-4" :items="breadcrumbs" divider=">"></v-breadcrumbs>

    <!-- Page Header -->
    <div class="d-flex justify-space-between align-center mb-6 flex-wrap gap-3">
      <div>
        <h1 class="text-h4 font-weight-bold">Departments</h1>
        <p class="text-body-1 text-medium-emphasis mt-1">
          Manage church departments and ministry groups ({{ departments.length }} total)
        </p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreateDialog" class="mb-1">
          Add Department
        </v-btn>
        <v-btn
          variant="outlined"
          prepend-icon="mdi-refresh"
          @click="fetchDepartments"
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
              <v-icon size="32">mdi-folder</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ departments.length }}</div>
              <div class="text-caption text-medium-emphasis">Total Departments</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card">
          <v-card-text class="d-flex align-center">
            <v-avatar color="success" size="56" class="mr-4">
              <v-icon size="32">mdi-account-group</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ totalMembers }}</div>
              <div class="text-caption text-medium-emphasis">Total Members</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card">
          <v-card-text class="d-flex align-center">
            <v-avatar color="info" size="56" class="mr-4">
              <v-icon size="32">mdi-account-tie</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ totalLeaders }}</div>
              <div class="text-caption text-medium-emphasis">Department Leaders</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card">
          <v-card-text class="d-flex align-center">
            <v-avatar color="warning" size="56" class="mr-4">
              <v-icon size="32">mdi-star</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ avgMembersPerDept }}</div>
              <div class="text-caption text-medium-emphasis">Avg Members/Dept</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Filters -->
    <v-card class="mb-6">
      <v-card-text>
        <v-row>
          <v-col cols="12" md="6">
            <v-text-field
              v-model="searchQuery"
              label="Search departments"
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="compact"
              clearable
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
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

    <!-- Departments Grid -->
    <v-row v-if="filteredDepartments.length > 0" class="mb-6">
      <v-col v-for="dept in filteredDepartments" :key="dept.id" cols="12" md="6" lg="4">
        <v-card class="h-100 d-flex flex-column" hover>
          <v-card-title class="bg-primary text-white d-flex justify-space-between align-center">
            <div>{{ dept.name }}</div>
            <v-icon>{{ getDeptIcon(dept.type) }}</v-icon>
          </v-card-title>

          <v-card-text class="flex-grow-1">
            <!-- Description -->
            <p class="text-body-2 text-medium-emphasis mb-4">{{ dept.description }}</p>

            <!-- Leader Info -->
            <div v-if="dept.leader" class="mb-4">
              <div class="text-caption font-weight-bold text-secondary mb-1">Department Leader</div>
              <div class="d-flex align-center gap-2">
                <v-avatar size="36" :color="dept.leader.color">
                  {{ getInitials(dept.leader.name) }}
                </v-avatar>
                <div>
                  <div class="text-caption font-weight-bold">{{ dept.leader.name }}</div>
                  <div class="text-caption text-medium-emphasis">{{ dept.leader.email }}</div>
                </div>
              </div>
            </div>

            <!-- Stats -->
            <v-divider class="my-2"></v-divider>
            <div class="d-flex justify-space-around text-center py-2">
              <div>
                <div class="text-h6 font-weight-bold">{{ dept.members_count || 0 }}</div>
                <div class="text-caption text-medium-emphasis">Members</div>
              </div>
              <div>
                <div class="text-h6 font-weight-bold">{{ dept.sub_departments_count || 0 }}</div>
                <div class="text-caption text-medium-emphasis">Sub-depts</div>
              </div>
              <div>
                <div class="text-h6 font-weight-bold">{{ dept.budget || '$0' }}</div>
                <div class="text-caption text-medium-emphasis">Budget</div>
              </div>
            </div>
          </v-card-text>

          <v-divider></v-divider>

          <!-- Card Actions -->
          <v-card-actions class="py-3">
            <v-btn
              variant="text"
              size="small"
              icon="mdi-eye"
              @click="viewDepartment(dept)"
            ></v-btn>
            <v-btn
              variant="text"
              size="small"
              icon="mdi-pencil"
              @click="editDepartment(dept)"
            ></v-btn>
            <v-spacer></v-spacer>
            <v-btn
              variant="text"
              size="small"
              icon="mdi-delete"
              color="error"
              @click="deleteDepartmentConfirm(dept)"
            ></v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <!-- Empty State -->
    <v-card v-else class="text-center py-12">
      <v-icon size="80" class="mb-4 text-medium-emphasis">mdi-folder-open</v-icon>
      <p class="text-body-1 text-medium-emphasis">No departments found</p>
    </v-card>

    <!-- Form Dialog -->
    <v-dialog v-model="showDialog" max-width="700px" persistent>
      <v-card>
        <v-card-title class="bg-primary text-white">
          <v-icon left>{{ isEditing ? 'mdi-pencil' : 'mdi-plus' }}</v-icon>
          {{ isEditing ? 'Edit Department' : 'Add New Department' }}
        </v-card-title>

        <v-card-text class="pt-6">
          <v-form ref="formRef" @submit.prevent="submitForm">
            <!-- Error Alert -->
            <v-alert v-if="formError" type="error" dismissible class="mb-4">
              {{ formError }}
            </v-alert>

            <!-- Form Fields -->
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formData.name"
                  label="Department Name *"
                  variant="outlined"
                  :rules="[v => !!v || 'Name is required']"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="formData.type"
                  label="Department Type"
                  :items="departmentTypes"
                  item-title="label"
                  item-value="value"
                  variant="outlined"
                ></v-select>
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
                <v-select
                  v-model="formData.leader_id"
                  label="Department Leader"
                  :items="members"
                  item-title="full_name"
                  item-value="id"
                  variant="outlined"
                  filterable
                ></v-select>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formData.budget"
                  label="Budget"
                  variant="outlined"
                  prefix="$"
                ></v-text-field>
              </v-col>
            </v-row>

            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="formData.meeting_day"
                  label="Regular Meeting Day"
                  variant="outlined"
                ></v-text-field>
              </v-col>
            </v-row>

            <v-row>
              <v-col cols="12">
                <v-switch
                  v-model="formData.is_active"
                  label="Active"
                ></v-switch>
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

    <!-- Delete Confirmation -->
    <v-dialog v-model="showDeleteDialog" max-width="400px">
      <v-card>
        <v-card-title>Delete Department?</v-card-title>
        <v-card-text>
          Are you sure you want to delete "<strong>{{ selectedDept?.name }}</strong>"?
          This action cannot be undone.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="showDeleteDialog = false">Cancel</v-btn>
          <v-btn color="error" @click="confirmDelete">Delete</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- View Details Dialog -->
    <v-dialog v-model="showDetailsDialog" max-width="600px">
      <v-card v-if="selectedDept">
        <v-card-title class="bg-primary text-white">{{ selectedDept.name }}</v-card-title>

        <v-card-text class="pt-6">
          <div class="mb-4">
            <h4>Department Information</h4>
            <v-divider class="mb-4"></v-divider>
            <v-row>
              <v-col cols="6">
                <div class="text-caption text-medium-emphasis">Type</div>
                <div class="font-weight-bold">{{ selectedDept.type }}</div>
              </v-col>
              <v-col cols="6">
                <div class="text-caption text-medium-emphasis">Status</div>
                <v-chip
                  :color="selectedDept.is_active ? 'success' : 'error'"
                  size="small"
                  text-color="white"
                >
                  {{ selectedDept.is_active ? 'Active' : 'Inactive' }}
                </v-chip>
              </v-col>
            </v-row>

            <v-row class="mt-4">
              <v-col cols="12">
                <div class="text-caption text-medium-emphasis">Description</div>
                <p>{{ selectedDept.description }}</p>
              </v-col>
            </v-row>
          </div>

          <div class="mb-4">
            <h4>Members ({{ selectedDept.members_count }})</h4>
            <v-divider class="mb-4"></v-divider>
            <div v-if="selectedDept.members && selectedDept.members.length > 0">
              <div v-for="member in selectedDept.members" :key="member.id" class="d-flex align-center gap-2 mb-3">
                <v-avatar :color="member.color" size="32">
                  {{ getInitials(member.full_name) }}
                </v-avatar>
                <div>
                  <div class="font-weight-bold">{{ member.full_name }}</div>
                  <div class="text-caption text-medium-emphasis">{{ member.email }}</div>
                </div>
              </div>
            </div>
            <p v-else class="text-medium-emphasis">No members in this department yet</p>
          </div>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="showDetailsDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useDepartmentStore } from '@/stores/moduleStore';
import { useMemberStore } from '@/stores/moduleStore';

const departmentStore = useDepartmentStore();
const memberStore = useMemberStore();

const breadcrumbs = ref([
  { title: 'Dashboard', href: '/' },
  { title: 'Departments', disabled: true },
]);

const departmentTypes = ref([
  { label: 'Ministry', value: 'ministry' },
  { label: 'Service', value: 'service' },
  { label: 'Outreach', value: 'outreach' },
  { label: 'Administration', value: 'administration' },
  { label: 'Support', value: 'support' },
]);

const statusOptions = ref([
  { label: 'Active', value: true },
  { label: 'Inactive', value: false },
]);

const showDialog = ref(false);
const showDeleteDialog = ref(false);
const showDetailsDialog = ref(false);
const isEditing = ref(false);
const isSubmitting = ref(false);
const formError = ref(null);
const searchQuery = ref('');
const selectedStatus = ref(null);
const selectedDept = ref(null);
const formRef = ref(null);

const formData = ref({
  name: '',
  description: '',
  type: 'ministry',
  leader_id: null,
  budget: '',
  meeting_day: '',
  is_active: true,
});

const initialFormData = () => ({
  name: '',
  description: '',
  type: 'ministry',
  leader_id: null,
  budget: '',
  meeting_day: '',
  is_active: true,
});

// Computed
const departments = computed(() => departmentStore.departments);
const loading = computed(() => departmentStore.loading);
const error = computed(() => departmentStore.error);
const members = computed(() => memberStore.members);

const totalMembers = computed(() =>
  departments.value.reduce((sum, d) => sum + (d.members_count || 0), 0)
);

const totalLeaders = computed(() =>
  departments.value.filter(d => d.leader_id).length
);

const avgMembersPerDept = computed(() => {
  if (departments.value.length === 0) return 0;
  return Math.round(totalMembers.value / departments.value.length);
});

const filteredDepartments = computed(() => {
  let filtered = departments.value;

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(d =>
      d.name?.toLowerCase().includes(query) ||
      d.description?.toLowerCase().includes(query)
    );
  }

  if (selectedStatus.value !== null && selectedStatus.value !== undefined) {
    filtered = filtered.filter(d => d.is_active === selectedStatus.value);
  }

  return filtered.sort((a, b) => a.name.localeCompare(b.name));
});

// Methods
const fetchDepartments = async () => {
  await departmentStore.fetchDepartments();
  await memberStore.fetchMembers();
};

const openCreateDialog = () => {
  formData.value = initialFormData();
  isEditing.value = false;
  formError.value = null;
  showDialog.value = true;
};

const editDepartment = (dept) => {
  selectedDept.value = dept;
  formData.value = { ...dept };
  isEditing.value = true;
  formError.value = null;
  showDialog.value = true;
};

const viewDepartment = async (dept) => {
  selectedDept.value = { ...dept };
  await departmentStore.fetchDepartment(dept.id);
  showDetailsDialog.value = true;
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
      await departmentStore.updateDepartment(selectedDept.value.id, formData.value);
    } else {
      await departmentStore.createDepartment(formData.value);
    }
    closeDialog();
  } catch (err) {
    formError.value = err.message || 'An error occurred';
  } finally {
    isSubmitting.value = false;
  }
};

const deleteDepartmentConfirm = (dept) => {
  selectedDept.value = dept;
  showDeleteDialog.value = true;
};

const confirmDelete = async () => {
  try {
    await departmentStore.deleteDepartment(selectedDept.value.id);
    showDeleteDialog.value = false;
  } catch (err) {
    formError.value = err.message;
  }
};

const exportData = () => {
  console.log('Exporting departments...');
};

const getDeptIcon = (type) => {
  const icons = {
    ministry: 'mdi-hands-pray',
    service: 'mdi-hammer-wrench',
    outreach: 'mdi-hand-heart',
    administration: 'mdi-clipboard-list',
    support: 'mdi-lifebuoy',
  };
  return icons[type] || 'mdi-folder';
};

const getInitials = (name) => {
  return name?.split(' ').map(n => n[0]).join('').toUpperCase() || '?';
};

// Lifecycle
onMounted(() => {
  fetchDepartments();
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

.h-100 {
  height: 100%;
}

.d-flex {
  display: flex;
}

.flex-column {
  flex-direction: column;
}

.flex-grow-1 {
  flex-grow: 1;
}
</style>
