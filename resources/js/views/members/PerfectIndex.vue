<template>
  <div>
    <!-- Breadcrumbs -->
    <v-breadcrumbs class="mb-4" :items="breadcrumbs" divider=">"></v-breadcrumbs>

    <!-- Page Header -->
    <div class="d-flex justify-space-between align-center mb-6 flex-wrap gap-3">
      <div>
        <h1 class="text-h4 font-weight-bold">Members</h1>
        <p class="text-body-1 text-medium-emphasis mt-1">
          Manage church members and directory ({{ members.length }} total)
        </p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreateDialog" class="mb-1">
          Add Member
        </v-btn>
        <v-btn
          variant="outlined"
          prepend-icon="mdi-refresh"
          @click="fetchMembers"
          :loading="loading"
          class="mb-1"
        >
          Refresh
        </v-btn>
        <v-menu>
          <template v-slot:activator="{ props }">
            <v-btn variant="outlined" prepend-icon="mdi-download" v-bind="props" class="mb-1">
              Export
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="exportAsCSV">
              <v-list-item-title>Export as CSV</v-list-item-title>
            </v-list-item>
            <v-list-item @click="exportAsExcel">
              <v-list-item-title>Export as Excel</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
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
              <div class="text-h5 font-weight-bold">{{ members.length }}</div>
              <div class="text-caption text-medium-emphasis">Total Members</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card">
          <v-card-text class="d-flex align-center">
            <v-avatar color="success" size="56" class="mr-4">
              <v-icon size="32">mdi-account-check</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ stats.active }}</div>
              <div class="text-caption text-medium-emphasis">Active Members</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card">
          <v-card-text class="d-flex align-center">
            <v-avatar color="warning" size="56" class="mr-4">
              <v-icon size="32">mdi-calendar-plus</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ stats.newThisMonth }}</div>
              <div class="text-caption text-medium-emphasis">New This Month</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card">
          <v-card-text class="d-flex align-center">
            <v-avatar color="info" size="56" class="mr-4">
              <v-icon size="32">mdi-account-eye</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ stats.visitors }}</div>
              <div class="text-caption text-medium-emphasis">Visitors</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Filters -->
    <v-card class="mb-6">
      <v-card-text>
        <v-row>
          <v-col cols="12" md="3">
            <v-text-field
              v-model="searchQuery"
              label="Search members..."
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="compact"
              clearable
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="3">
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
          <v-col cols="12" md="3">
            <v-select
              v-model="selectedDepartment"
              label="Filter by department"
              :items="departments"
              item-title="name"
              item-value="id"
              variant="outlined"
              density="compact"
              clearable
            ></v-select>
          </v-col>
          <v-col cols="12" md="3">
            <v-select
              v-model="viewMode"
              label="View mode"
              :items="[
                { label: 'Table', value: 'table' },
                { label: 'Grid', value: 'grid' },
                { label: 'List', value: 'list' },
              ]"
              item-title="label"
              item-value="value"
              variant="outlined"
              density="compact"
            ></v-select>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Table View -->
    <v-card v-if="viewMode === 'table'" :loading="loading">
      <v-table v-if="filteredMembers.length > 0">
        <thead>
          <tr>
            <th class="text-left">Name</th>
            <th class="text-left">Email</th>
            <th class="text-left">Phone</th>
            <th class="text-left">Join Date</th>
            <th class="text-left">Status</th>
            <th class="text-left">Department</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="member in filteredMembers" :key="member.id">
            <td>
              <div class="d-flex align-center gap-2">
                <v-avatar size="36" :color="getStatusColor(member.membership_status)">
                  {{ getInitials(`${member.first_name} ${member.last_name}`) }}
                </v-avatar>
                <div>
                  <div class="font-weight-bold">{{ member.first_name }} {{ member.last_name }}</div>
                  <div class="text-caption text-medium-emphasis">{{ member.id }}</div>
                </div>
              </div>
            </td>
            <td>{{ member.email }}</td>
            <td>{{ member.phone || 'N/A' }}</td>
            <td>{{ formatDate(member.join_date) }}</td>
            <td>
              <v-chip :color="getStatusColor(member.membership_status)" size="small" text-color="white">
                {{ member.membership_status }}
              </v-chip>
            </td>
            <td>
              <div v-if="member.departments && member.departments.length > 0">
                <v-chip
                  v-for="dept in member.departments.slice(0, 2)"
                  :key="dept.id"
                  size="x-small"
                  class="mr-1 mb-1"
                >
                  {{ dept.name }}
                </v-chip>
                <span v-if="member.departments.length > 2" class="text-caption">
                  +{{ member.departments.length - 2 }} more
                </span>
              </div>
              <span v-else class="text-medium-emphasis">None</span>
            </td>
            <td class="text-center">
              <v-btn
                icon="mdi-eye"
                size="x-small"
                variant="text"
                @click="viewMember(member)"
              ></v-btn>
              <v-btn
                icon="mdi-pencil"
                size="x-small"
                variant="text"
                @click="editMember(member)"
              ></v-btn>
              <v-btn
                icon="mdi-delete"
                size="x-small"
                variant="text"
                color="error"
                @click="deleteMemberConfirm(member)"
              ></v-btn>
            </td>
          </tr>
        </tbody>
      </v-table>
      <v-card-text v-else class="text-center py-8">
        <v-icon size="64" class="mb-4 text-medium-emphasis">mdi-account-off</v-icon>
        <p class="text-body-1 text-medium-emphasis">No members found</p>
      </v-card-text>
    </v-card>

    <!-- Grid View -->
    <v-row v-if="viewMode === 'grid'" class="mb-6">
      <v-col v-for="member in filteredMembers" :key="member.id" cols="12" sm="6" md="4" lg="3">
        <v-card class="h-100" hover @click="viewMember(member)">
          <v-card-text class="text-center py-8">
            <v-avatar size="80" :color="getStatusColor(member.membership_status)" class="mb-4">
              {{ getInitials(`${member.first_name} ${member.last_name}`) }}
            </v-avatar>
            <div class="font-weight-bold text-h6">{{ member.first_name }} {{ member.last_name }}</div>
            <div class="text-caption text-medium-emphasis mb-2">{{ member.email }}</div>
            <v-chip :color="getStatusColor(member.membership_status)" size="x-small" text-color="white">
              {{ member.membership_status }}
            </v-chip>
          </v-card-text>
          <v-divider></v-divider>
          <v-card-actions class="justify-center gap-1">
            <v-btn
              icon="mdi-pencil"
              size="small"
              variant="text"
              @click.stop="editMember(member)"
            ></v-btn>
            <v-btn
              icon="mdi-delete"
              size="small"
              variant="text"
              color="error"
              @click.stop="deleteMemberConfirm(member)"
            ></v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <!-- List View -->
    <v-card v-if="viewMode === 'list'" :loading="loading">
      <v-list>
        <v-list-item
          v-for="member in filteredMembers"
          :key="member.id"
          @click="viewMember(member)"
          class="cursor-pointer"
        >
          <template v-slot:prepend>
            <v-avatar :color="getStatusColor(member.membership_status)">
              {{ getInitials(`${member.first_name} ${member.last_name}`) }}
            </v-avatar>
          </template>

          <v-list-item-title class="font-weight-bold">{{ member.first_name }} {{ member.last_name }}</v-list-item-title>
          <v-list-item-subtitle>{{ member.email }} • {{ member.phone }}</v-list-item-subtitle>

          <template v-slot:append>
            <div class="d-flex gap-2">
              <v-btn
                icon="mdi-pencil"
                size="x-small"
                variant="text"
                @click.stop="editMember(member)"
              ></v-btn>
              <v-btn
                icon="mdi-delete"
                size="x-small"
                variant="text"
                color="error"
                @click.stop="deleteMemberConfirm(member)"
              ></v-btn>
            </div>
          </template>
        </v-list-item>
      </v-list>
    </v-card>

    <!-- Form Dialog -->
    <v-dialog v-model="showDialog" max-width="700px" persistent>
      <v-card>
        <v-card-title class="bg-primary text-white">
          <v-icon left>{{ isEditing ? 'mdi-pencil' : 'mdi-plus' }}</v-icon>
          {{ isEditing ? 'Edit Member' : 'Add New Member' }}
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
                  v-model="formData.first_name"
                  label="First Name *"
                  variant="outlined"
                  :rules="[v => !!v || 'First name is required']"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formData.last_name"
                  label="Last Name *"
                  variant="outlined"
                  :rules="[v => !!v || 'Last name is required']"
                  required
                ></v-text-field>
              </v-col>
            </v-row>

            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formData.email"
                  label="Email"
                  type="email"
                  variant="outlined"
                ></v-text-field>
              </v-col>
            </v-row>

            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formData.phone"
                  label="Phone Number"
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formData.birth_date"
                  label="Date of Birth"
                  type="date"
                  variant="outlined"
                ></v-text-field>
              </v-col>
            </v-row>

            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formData.join_date"
                  label="Join Date"
                  type="date"
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="formData.membership_status"
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
                <v-text-field
                  v-model="formData.address"
                  label="Address"
                  variant="outlined"
                ></v-text-field>
              </v-col>
            </v-row>

            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formData.city"
                  label="City"
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formData.zip_code"
                  label="Zip Code"
                  variant="outlined"
                ></v-text-field>
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
                  {{ isSubmitting ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
                </v-btn>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- View Details Dialog -->
    <v-dialog v-model="showDetailsDialog" max-width="600px">
      <v-card v-if="selectedMember">
        <v-card-title class="bg-primary text-white d-flex align-center">
          <v-avatar :color="getStatusColor(selectedMember.membership_status)" size="48" class="mr-4">
            {{ getInitials(`${selectedMember.first_name} ${selectedMember.last_name}`) }}
          </v-avatar>
          {{ selectedMember.first_name }} {{ selectedMember.last_name }}
        </v-card-title>

        <v-card-text class="pt-6">
          <!-- Contact Info -->
          <div class="mb-6">
            <h4 class="mb-4">Contact Information</h4>
            <v-row>
              <v-col cols="12" md="6">
                <div class="text-caption text-medium-emphasis">Email</div>
                <div>{{ selectedMember.email || 'N/A' }}</div>
              </v-col>
              <v-col cols="12" md="6">
                <div class="text-caption text-medium-emphasis">Phone</div>
                <div>{{ selectedMember.phone || 'N/A' }}</div>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12" md="6">
                <div class="text-caption text-medium-emphasis">Date of Birth</div>
                <div>{{ formatDate(selectedMember.birth_date) }}</div>
              </v-col>
              <v-col cols="12" md="6">
                <div class="text-caption text-medium-emphasis">Status</div>
                <v-chip :color="getStatusColor(selectedMember.membership_status)" size="small" text-color="white">
                  {{ selectedMember.membership_status }}
                </v-chip>
              </v-col>
            </v-row>
          </div>

          <v-divider class="my-4"></v-divider>

          <!-- Address Info -->
          <div class="mb-6">
            <h4 class="mb-4">Address</h4>
            <p>
              {{ selectedMember.address }}<br />
              {{ selectedMember.city }}, {{ selectedMember.zip_code }}
            </p>
          </div>

          <v-divider class="my-4"></v-divider>

          <!-- Departments -->
          <div v-if="selectedMember.departments && selectedMember.departments.length > 0">
            <h4 class="mb-4">Departments</h4>
            <div class="d-flex flex-wrap gap-2">
              <v-chip v-for="dept in selectedMember.departments" :key="dept.id">
                {{ dept.name }}
              </v-chip>
            </div>
          </div>
        </v-card-text>

        <v-card-actions>
          <v-btn variant="text" @click="editMember(selectedMember)">Edit</v-btn>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="showDetailsDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation -->
    <v-dialog v-model="showDeleteDialog" max-width="400px">
      <v-card>
        <v-card-title>Delete Member?</v-card-title>
        <v-card-text>
          Are you sure you want to delete "<strong>{{ selectedMember?.full_name }}</strong>"?
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
import { ref, computed, onMounted } from 'vue';
import { useMemberStore } from '@/stores/moduleStore';
import { useDepartmentStore } from '@/stores/moduleStore';

const memberStore = useMemberStore();
const departmentStore = useDepartmentStore();

const breadcrumbs = ref([
  { title: 'Dashboard', href: '/' },
  { title: 'Members', disabled: true },
]);

const statusOptions = ref([
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' },
  { label: 'Visitor', value: 'visitor' },
  { label: 'Prospect', value: 'prospect' },
]);

const showDialog = ref(false);
const showDeleteDialog = ref(false);
const showDetailsDialog = ref(false);
const isEditing = ref(false);
const isSubmitting = ref(false);
const formError = ref(null);
const searchQuery = ref('');
const selectedStatus = ref(null);
const selectedDepartment = ref(null);
const viewMode = ref('table');
const selectedMember = ref(null);
const formRef = ref(null);

const formData = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  birth_date: '',
  join_date: new Date().toISOString().split('T')[0],
  membership_status: 'active',
  address: '',
  city: '',
  zip_code: '',
  notes: '',
});

const initialFormData = () => ({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  birth_date: '',
  join_date: new Date().toISOString().split('T')[0],
  membership_status: 'active',
  address: '',
  city: '',
  zip_code: '',
  notes: '',
});

// Computed
const members = computed(() => memberStore.members);
const loading = computed(() => memberStore.loading);
const error = computed(() => memberStore.error);
const departments = computed(() => departmentStore.departments);
const memberStats = computed(() => memberStore.stats);

const stats = computed(() => {
  // Use stats from store, fallback to calculated values
  return {
    active: memberStats.value.active || members.value.filter(m => m.membership_status === 'active').length,
    newThisMonth: memberStats.value.new_this_month || members.value.filter(m => {
      const now = new Date();
      const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);
      return new Date(m.join_date) >= monthStart;
    }).length,
    visitors: memberStats.value.visitors || members.value.filter(m => m.membership_status === 'visitor').length,
    attendanceRate: members.value.length > 0 ? 85 : 0, // Placeholder
  };
});

const filteredMembers = computed(() => {
  let filtered = members.value;

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(m => {
      const fullName = `${m.first_name || ''} ${m.last_name || ''}`.toLowerCase();
      return (
        fullName.includes(query) ||
        m.email?.toLowerCase().includes(query) ||
        m.phone?.includes(query)
      );
    });
  }

  if (selectedStatus.value) {
    filtered = filtered.filter(m => m.membership_status === selectedStatus.value);
  }

  if (selectedDepartment.value) {
    filtered = filtered.filter(m =>
      m.departments?.some(d => d.id === selectedDepartment.value)
    );
  }

  return filtered.sort((a, b) => {
    const nameA = `${a.first_name || ''} ${a.last_name || ''}`;
    const nameB = `${b.first_name || ''} ${b.last_name || ''}`;
    return nameA.localeCompare(nameB);
  });
});

// Methods
const fetchMembers = async () => {
  await memberStore.fetchMembers();
  await departmentStore.fetchDepartments();
};

const openCreateDialog = () => {
  formData.value = initialFormData();
  isEditing.value = false;
  formError.value = null;
  showDialog.value = true;
};

const editMember = (member) => {
  selectedMember.value = member;
  formData.value = { ...member };
  isEditing.value = true;
  formError.value = null;
  showDialog.value = true;
};

const viewMember = (member) => {
  selectedMember.value = member;
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
      await memberStore.updateMember(selectedMember.value.id, formData.value);
    } else {
      await memberStore.createMember(formData.value);
    }
    closeDialog();
  } catch (err) {
    formError.value = err.message || 'An error occurred';
  } finally {
    isSubmitting.value = false;
  }
};

const deleteMemberConfirm = (member) => {
  selectedMember.value = member;
  showDeleteDialog.value = true;
};

const confirmDelete = async () => {
  try {
    await memberStore.deleteMember(selectedMember.value.id);
    showDeleteDialog.value = false;
  } catch (err) {
    formError.value = err.message;
  }
};

const exportAsCSV = () => {
  console.log('Exporting as CSV...');
};

const exportAsExcel = () => {
  console.log('Exporting as Excel...');
};

const getStatusColor = (status) => {
  const colors = {
    active: 'success',
    inactive: 'error',
    visitor: 'info',
    prospect: 'warning',
  };
  return colors[status] || 'default';
};

const getInitials = (name) => {
  return name?.split(' ').map(n => n[0]).join('').toUpperCase() || '?';
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

// Lifecycle
onMounted(() => {
  fetchMembers();
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

.cursor-pointer {
  cursor: pointer;
}

.d-flex {
  display: flex;
}

.flex-wrap {
  flex-wrap: wrap;
}

.gap-2 {
  gap: 0.5rem;
}
</style>
