<template>
  <div>
    <!-- Breadcrumbs -->
    <v-breadcrumbs class="mb-4" :items="breadcrumbs" divider=">"></v-breadcrumbs>

    <!-- Page Header -->
    <div class="d-flex justify-space-between align-center mb-6">
      <div>
        <h1 class="text-h4 font-weight-bold">Departments</h1>
        <p class="text-body-1 text-medium-emphasis mt-1">
          Manage church departments and ministry groups ({{ departments.length }} total)
        </p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <v-btn color="primary" prepend-icon="mdi-account-group" @click="openCreateDialog">
          Add Department
        </v-btn>
        <v-btn variant="outlined" prepend-icon="mdi-chart-box" @click="generateReport">
          Generate Report
        </v-btn>
      </div>
    </div>

    <!-- Stats Cards -->
    <v-row class="mb-6">
      <v-col cols="6" md="3" v-for="stat in stats" :key="stat.title">
        <v-card class="stats-card" @click="filterByCategory(stat.category)">
          <v-card-text class="d-flex align-center">
            <v-avatar :color="stat.color" size="56" class="mr-4">
              <v-icon size="32">{{ stat.icon }}</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ stat.value }}</div>
              <div class="text-caption">{{ stat.title }}</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Filter Chips -->
    <div class="d-flex flex-wrap gap-2 mb-4">
      <v-chip
        v-for="category in categories"
        :key="category.value"
        :color="activeCategory === category.value ? 'primary' : 'default'"
        @click="filterByCategory(category.value)"
        class="cursor-pointer"
      >
        {{ category.label }}
      </v-chip>
      <v-chip
        v-if="activeCategory"
        color="warning"
        @click="clearFilter"
        class="cursor-pointer"
      >
        Clear Filter
      </v-chip>
    </div>

    <!-- Departments Grid -->
    <v-row v-if="loading" class="mb-6">
      <v-col cols="12" sm="6" md="4" v-for="n in 6" :key="n">
        <v-skeleton-loader type="card"></v-skeleton-loader>
      </v-col>
    </v-row>

    <v-row v-else-if="filteredDepartments.length > 0" class="mb-6">
      <v-col cols="12" sm="6" md="4" v-for="dept in filteredDepartments" :key="dept.id">
        <v-card class="department-card" @click="viewDepartment(dept)">
          <v-card-text class="pa-4">
            <div class="d-flex justify-space-between align-start mb-3">
              <div>
                <h3 class="text-h6 font-weight-bold">{{ dept.name }}</h3>
                <v-chip size="small" :color="getCategoryColor(dept.category)" class="mt-1">
                  {{ dept.category }}
                </v-chip>
              </div>
              <v-chip size="small" :color="dept.is_active ? 'success' : 'error'">
                {{ dept.is_active ? 'Active' : 'Inactive' }}
              </v-chip>
            </div>

            <p class="text-body-2 text-medium-emphasis mb-3">
              {{ truncateText(dept.description, 100) }}
            </p>

            <div class="department-meta">
              <div class="d-flex align-center mb-2">
                <v-icon size="16" class="mr-2" color="primary">mdi-account-group</v-icon>
                <span class="text-caption">{{ dept.members_count || 0 }} members</span>
              </div>
              <div class="d-flex align-center mb-2" v-if="dept.leader">
                <v-icon size="16" class="mr-2" color="primary">mdi-crown</v-icon>
                <span class="text-caption">Leader: {{ dept.leader.first_name }} {{ dept.leader.last_name }}</span>
              </div>
              <div class="d-flex align-center" v-if="dept.meeting_schedule">
                <v-icon size="16" class="mr-2" color="primary">mdi-calendar</v-icon>
                <span class="text-caption">{{ dept.meeting_schedule }}</span>
              </div>
            </div>
          </v-card-text>

          <v-card-actions class="pa-3">
            <v-btn size="small" variant="text" @click.stop="viewDepartment(dept)">
              <v-icon left size="18">mdi-eye</v-icon>
              View
            </v-btn>
            <v-spacer></v-spacer>
            <v-btn icon size="small" @click.stop="editDepartment(dept)">
              <v-icon size="18">mdi-pencil</v-icon>
            </v-btn>
            <v-btn icon size="small" @click.stop="deleteDepartment(dept)">
              <v-icon size="18">mdi-delete</v-icon>
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <!-- Empty State -->
    <v-card v-else class="text-center py-12">
      <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-account-group-off</v-icon>
      <h3 class="text-h6 mb-2">No departments found</h3>
      <p class="text-medium-emphasis mb-4">
        {{ activeCategory ? 'Try changing your filter' : 'Create your first department to get started' }}
      </p>
      <v-btn color="primary" @click="openCreateDialog">
        Create First Department
      </v-btn>
    </v-card>

    <!-- Create/Edit Dialog -->
    <v-dialog v-model="dialog" max-width="600">
      <v-card>
        <v-card-title class="d-flex justify-space-between align-center">
          <span class="text-h5">{{ editingDepartment ? 'Edit Department' : 'Add New Department' }}</span>
          <v-btn icon @click="closeDialog">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text>
        <v-form ref="departmentForm" @submit.prevent="saveDepartment">
            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="form.name"
                  label="Department Name *"
                  variant="outlined"
                  :rules="[v => !!v || 'Name is required']"
                  required
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="form.description"
                  label="Description"
                  variant="outlined"
                  rows="3"
                ></v-textarea>
              </v-col>

              <v-col cols="12" md="6">
                <v-select
                    v-model="form.category"
                    :items="categoryOptions"
                    item-title="title"
                    item-value="value"
                    label="Category *"
                    variant="outlined"
                    required
                    />
              </v-col>

              <v-col cols="12" md="6">
                <v-select
                  v-model="form.leader_id"
                  :items="members"
                  item-title="full_name"
                  item-value="id"
                  label="Leader"
                  variant="outlined"
                  clearable
                ></v-select>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.email"
                  label="Department Email"
                  type="email"
                  variant="outlined"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.phone"
                  label="Department Phone"
                  variant="outlined"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-text-field
                  v-model="form.meeting_schedule"
                  label="Meeting Schedule"
                  variant="outlined"
                  placeholder="e.g., Every Sunday at 2 PM"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-switch
                  v-model="form.is_active"
                  label="Active Department"
                  color="primary"
                ></v-switch>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>

        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="closeDialog">
            Cancel
          </v-btn>
          <v-btn color="primary" @click="saveDepartment" :loading="saving">
            {{ editingDepartment ? 'Update' : 'Save' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- View Department Dialog -->
    <v-dialog v-model="viewDialog" max-width="800">
      <v-card v-if="selectedDepartment">
        <v-card-title class="d-flex justify-space-between align-center">
          <div class="d-flex align-center">
            <v-avatar :color="getCategoryColor(selectedDepartment.category)" size="48" class="mr-3">
              <v-icon color="white">mdi-account-group</v-icon>
            </v-avatar>
            <div>
              <h2 class="text-h5">{{ selectedDepartment.name }}</h2>
              <div class="d-flex align-center mt-1">
                <v-chip size="small" :color="getCategoryColor(selectedDepartment.category)" class="mr-2">
                  {{ selectedDepartment.category }}
                </v-chip>
                <v-chip size="small" :color="selectedDepartment.is_active ? 'success' : 'error'">
                  {{ selectedDepartment.is_active ? 'Active' : 'Inactive' }}
                </v-chip>
              </div>
            </div>
          </div>
          <v-btn icon @click="viewDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-divider></v-divider>

        <v-tabs v-model="viewTab" color="primary" class="px-4">
          <v-tab value="overview">Overview</v-tab>
          <v-tab value="members">Members</v-tab>
          <v-tab value="activities">Activities</v-tab>
          <v-tab value="settings">Settings</v-tab>
        </v-tabs>

        <v-divider></v-divider>

        <v-card-text>
          <v-window v-model="viewTab">
            <!-- Overview Tab -->
            <v-window-item value="overview">
              <v-row class="mt-2">
                <v-col cols="12">
                  <div class="info-item mb-4">
                    <div class="text-caption text-medium-emphasis">Description</div>
                    <div class="text-body-1">{{ selectedDepartment.description || 'No description provided' }}</div>
                  </div>
                </v-col>

                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">
                      <v-icon size="16" class="mr-1">mdi-crown</v-icon>
                      Leader
                    </div>
                    <div class="text-body-1" v-if="selectedDepartment.leader">
                      {{ selectedDepartment.leader.first_name }} {{ selectedDepartment.leader.last_name }}
                    </div>
                    <div class="text-body-1 text-medium-emphasis" v-else>
                      No leader assigned
                    </div>
                  </div>
                </v-col>

                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">
                      <v-icon size="16" class="mr-1">mdi-calendar</v-icon>
                      Meeting Schedule
                    </div>
                    <div class="text-body-1">{{ selectedDepartment.meeting_schedule || 'Not specified' }}</div>
                  </div>
                </v-col>

                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">
                      <v-icon size="16" class="mr-1">mdi-email</v-icon>
                      Email
                    </div>
                    <div class="text-body-1">{{ selectedDepartment.email || 'Not specified' }}</div>
                  </div>
                </v-col>

                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">
                      <v-icon size="16" class="mr-1">mdi-phone</v-icon>
                      Phone
                    </div>
                    <div class="text-body-1">{{ selectedDepartment.phone || 'Not specified' }}</div>
                  </div>
                </v-col>

                <v-col cols="12">
                  <v-divider class="my-2"></v-divider>
                  <div class="info-item">
                    <div class="text-caption text-medium-emphasis mb-2">Quick Stats</div>
                    <v-row>
                      <v-col cols="6" md="3">
                        <div class="text-center">
                          <div class="text-h6">{{ selectedDepartment.members_count || 0 }}</div>
                          <div class="text-caption">Total Members</div>
                        </div>
                      </v-col>
                      <v-col cols="6" md="3">
                        <div class="text-center">
                          <div class="text-h6">{{ selectedDepartment.activities?.length || 0 }}</div>
                          <div class="text-caption">Activities</div>
                        </div>
                      </v-col>
                      <v-col cols="6" md="3">
                        <div class="text-center">
                          <div class="text-h6">{{ getLeadersCount }}</div>
                          <div class="text-caption">Leaders</div>
                        </div>
                      </v-col>
                      <v-col cols="6" md="3">
                        <div class="text-center">
                          <div class="text-h6">{{ getActiveMembersCount }}</div>
                          <div class="text-caption">Active Members</div>
                        </div>
                      </v-col>
                    </v-row>
                  </div>
                </v-col>
              </v-row>
            </v-window-item>

            <!-- Members Tab -->
            <v-window-item value="members">
              <div class="d-flex justify-space-between align-center mb-4">
                <h3 class="text-h6">Department Members ({{ selectedDepartment.members?.length || 0 }})</h3>
                <v-btn color="primary" size="small" @click="openAddMemberDialog">
                  <v-icon left>mdi-account-plus</v-icon>
                  Add Members
                </v-btn>
              </div>

              <v-table v-if="selectedDepartment.members?.length > 0" density="comfortable">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Joined Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="member in selectedDepartment.members" :key="member.id">
                    <td>
                      <div class="d-flex align-center">
                        <v-avatar size="32" :color="getAvatarColor(member.member)" class="mr-3">
                          <span class="text-white text-caption">{{ getInitials(member.member) }}</span>
                        </v-avatar>
                        <div>
                          <div class="font-weight-medium">{{ member.member.first_name }} {{ member.member.last_name }}</div>
                          <div class="text-caption text-medium-emphasis">{{ member.member.email }}</div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <v-chip size="small" :color="getRoleColor(member.role)">
                        {{ member.role }}
                      </v-chip>
                    </td>
                    <td>
                      {{ formatDate(member.joined_date) }}
                    </td>
                    <td>
                      <div class="d-flex gap-1">
                        <v-btn icon size="small" @click="editMemberRole(member)">
                          <v-icon size="18">mdi-account-edit</v-icon>
                        </v-btn>
                        <v-btn icon size="small" color="error" @click="removeMember(member)">
                          <v-icon size="18">mdi-account-remove</v-icon>
                        </v-btn>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </v-table>

              <div v-else class="text-center py-8">
                <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-account-group-off</v-icon>
                <h3 class="text-h6 mb-2">No Members Yet</h3>
                <p class="text-medium-emphasis mb-4">Add members to this department to get started</p>
                <v-btn color="primary" @click="openAddMemberDialog">
                  Add Members
                </v-btn>
              </div>
            </v-window-item>

            <!-- Activities Tab -->
            <v-window-item value="activities">
              <div class="d-flex justify-space-between align-center mb-4">
                <h3 class="text-h6">Department Activities</h3>
                <v-btn color="primary" size="small" @click="openRecordActivityDialog">
                  <v-icon left>mdi-calendar-plus</v-icon>
                  Record Activity
                </v-btn>
              </div>

              <v-list v-if="selectedDepartment.activities?.length > 0">
                <v-list-item v-for="activity in selectedDepartment.activities" :key="activity.id">
                  <template #prepend>
                    <v-avatar :color="getActivityColor(activity.type)" size="40" class="mr-3">
                      <v-icon color="white">{{ getActivityIcon(activity.type) }}</v-icon>
                    </v-avatar>
                  </template>
                  <v-list-item-title>{{ activity.title }}</v-list-item-title>
                  <v-list-item-subtitle>
                    {{ formatDateTime(activity.activity_date) }} • {{ activity.type }}
                  </v-list-item-subtitle>
                  <template #append>
                    <v-chip size="small">
                      {{ activity.attendance?.length || 0 }} attended
                    </v-chip>
                  </template>
                </v-list-item>
              </v-list>

              <div v-else class="text-center py-8">
                <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-calendar-blank</v-icon>
                <h3 class="text-h6 mb-2">No Activities Recorded</h3>
                <p class="text-medium-emphasis mb-4">Record activities to track department engagement</p>
                <v-btn color="primary" @click="openRecordActivityDialog">
                  Record First Activity
                </v-btn>
              </div>
            </v-window-item>

            <!-- Settings Tab -->
            <v-window-item value="settings">
              <div class="text-center py-8">
                <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-cog</v-icon>
                <h3 class="text-h6 mb-2">Department Settings</h3>
                <p class="text-medium-emphasis mb-4">Advanced department settings coming soon</p>
                <v-btn color="primary" @click="editDepartment(selectedDepartment)">
                  Edit Department
                </v-btn>
              </div>
            </v-window-item>
          </v-window>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 pb-4">
          <v-btn color="primary" @click="editDepartment(selectedDepartment)">
            <v-icon left>mdi-pencil</v-icon>
            Edit Department
          </v-btn>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="viewDialog = false">
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Add Member Dialog -->
    <v-dialog v-model="addMemberDialog" max-width="600">
      <v-card>
        <v-card-title>Add Members to Department</v-card-title>
        <v-card-text>
          <v-select
            v-model="selectedMembers"
            :items="availableMembers"
            item-title="full_name"
            item-value="id"
            label="Select Members"
            multiple
            chips
            variant="outlined"
          ></v-select>

          <v-select
            v-model="memberRole"
            :items="roleOptions"
            label="Role"
            variant="outlined"
            class="mt-4"
          ></v-select>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="addMemberDialog = false">
            Cancel
          </v-btn>
          <v-btn color="primary" @click="addMembers" :loading="addingMembers">
            Add Members
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Record Activity Dialog -->
    <v-dialog v-model="activityDialog" max-width="600">
      <v-card>
        <v-card-title>Record Department Activity</v-card-title>
        <v-card-text>
          <v-form ref="activityForm">
            <v-row>
              <v-col cols="12">
                <v-select
                  v-model="activityForm.type"
                  :items="activityTypes"
                  label="Activity Type"
                  variant="outlined"
                  required
                ></v-select>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  v-model="activityForm.title"
                  label="Activity Title"
                  variant="outlined"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-textarea
                  v-model="activityForm.description"
                  label="Description"
                  variant="outlined"
                  rows="3"
                ></v-textarea>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  v-model="activityForm.activity_date"
                  label="Activity Date & Time"
                  type="datetime-local"
                  variant="outlined"
                  required
                ></v-text-field>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="activityDialog = false">
            Cancel
          </v-btn>
          <v-btn color="primary" @click="recordActivity" :loading="recordingActivity">
            Record Activity
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'

const toast = useToast()

// Data
const loading = ref(false)
const saving = ref(false)
const dialog = ref(false)
const viewDialog = ref(false)
const addMemberDialog = ref(false)
const activityDialog = ref(false)
const addingMembers = ref(false)
const recordingActivity = ref(false)
const departments = ref([])
const members = ref([])
const selectedDepartment = ref(null)
const editingDepartment = ref(null)
const viewTab = ref('overview')
const activeCategory = ref('')
const selectedMembers = ref([])
const memberRole = ref('member')
const availableMembers = ref([])
const departmentForm = ref(null)


// Form
const form = reactive({
  name: '',
  description: '',
  category: 'ministry',
  leader_id: null,
  email: '',
  phone: '',
  meeting_schedule: '',
  is_active: true,
})

const activityForm = ref({
  type: 'meeting',
  title: '',
  description: '',
  activity_date: new Date().toISOString().slice(0, 16),
})

// Breadcrumbs
const breadcrumbs = ref([
  { title: 'Dashboard', to: '/' },
  { title: 'Departments', disabled: true }
])

// Options
const categoryOptions = ref([
  { title: 'Ministry', value: 'ministry' },
  { title: 'Service', value: 'service' },
  { title: 'Outreach', value: 'outreach' },
  { title: 'Fellowship', value: 'fellowship' },
  { title: 'Administration', value: 'administration' },
  { title: 'Other', value: 'other' },
])

const categories = computed(() => [
  { label: 'All Departments', value: '' },
  ...categoryOptions.value.map(cat => ({ label: cat.title, value: cat.value }))
])

const roleOptions = ref([
  { title: 'Leader', value: 'leader' },
  { title: 'Co-Leader', value: 'co-leader' },
  { title: 'Member', value: 'member' },
  { title: 'Volunteer', value: 'volunteer' },
])

const activityTypes = ref([
  { title: 'Meeting', value: 'meeting' },
  { title: 'Outreach', value: 'outreach' },
  { title: 'Training', value: 'training' },
  { title: 'Prayer', value: 'prayer' },
  { title: 'Planning', value: 'planning' },
  { title: 'Other', value: 'other' },
])

// Computed
const filteredDepartments = computed(() => {
  if (!activeCategory.value) return departments.value
  return departments.value.filter(dept => dept.category === activeCategory.value)
})

const stats = computed(() => {
  const total = departments.value.length
  const active = departments.value.filter(d => d.is_active).length
  const ministries = departments.value.filter(d => d.category === 'ministry').length
  const outreach = departments.value.filter(d => d.category === 'outreach').length

  return [
    { title: 'Total Departments', value: total, category: '', icon: 'mdi-account-group', color: 'primary' },
    { title: 'Active Departments', value: active, category: '', icon: 'mdi-check-circle', color: 'success' },
    { title: 'Ministries', value: ministries, category: 'ministry', icon: 'mdi-church', color: 'info' },
    { title: 'Outreach', value: outreach, category: 'outreach', icon: 'mdi-hand-heart', color: 'warning' },
  ]
})

const getLeadersCount = computed(() => {
  if (!selectedDepartment.value?.members) return 0
  return selectedDepartment.value.members.filter(m => m.role === 'leader').length
})

const getActiveMembersCount = computed(() => {
  if (!selectedDepartment.value?.members) return 0
  // Assuming all members are active for now
  return selectedDepartment.value.members.length
})

// Methods
const fetchDepartments = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/departments', {
      headers: { Authorization: `Bearer ${token}` },
      params: { per_page: 100 }
    })

    if (response.data.success) {
      departments.value = response.data.data.data || response.data.data
    }
  } catch (error) {
    console.error('Error fetching departments:', error)
    toast.error('Failed to load departments')
  } finally {
    loading.value = false
  }
}

const fetchMembers = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/members', {
      headers: { Authorization: `Bearer ${token}` },
      params: { per_page: 1000 }
    })

    if (response.data.success) {
      members.value = (response.data.data.data || response.data.data).map(m => ({
        ...m,
        full_name: `${m.first_name} ${m.last_name}`
      }))
    }
  } catch (error) {
    console.error('Error fetching members:', error)
  }
}

const openCreateDialog = () => {
  resetForm()
  editingDepartment.value = null
  dialog.value = true
}

const editDepartment = (department) => {
  editingDepartment.value = department
  Object.assign(form, department)
  dialog.value = true
}

const viewDepartment = async (department) => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get(`/api/departments/${department.id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      selectedDepartment.value = response.data.data
      viewTab.value = 'overview'
      viewDialog.value = true
    }
  } catch (error) {
    console.error('Error fetching department details:', error)
    toast.error('Failed to load department details')
  }
}

const saveDepartment = async () => {
  saving.value = true
  try {
    const token = localStorage.getItem('token')
    let response

    // ✅ correct payload
    const payload = { ...form }

    if (editingDepartment.value) {
      response = await axios.put(
        `/api/departments/${editingDepartment.value.id}`,
        payload,
        {
          headers: { Authorization: `Bearer ${token}` }
        }
      )
    } else {
      response = await axios.post(
        '/api/departments',
        payload,
        {
          headers: { Authorization: `Bearer ${token}` }
        }
      )
    }

    if (response.data.success) {
      toast.success(response.data.message || 'Department saved successfully')
      closeDialog()
      await fetchDepartments() // 👈 ensure refresh
    } else {
      toast.error(response.data.message || 'Failed to save department')
    }
  } catch (error) {
    console.error('Error saving department:', error)
    toast.error(error.response?.data?.message || 'Failed to save department')
  } finally {
    saving.value = false
  }
}

const deleteDepartment = async (department) => {
  if (!confirm(`Are you sure you want to delete "${department.name}"?`)) {
    return
  }

  try {
    const token = localStorage.getItem('token')
    const response = await axios.delete(`/api/departments/${department.id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      toast.success('Department deleted successfully')
      fetchDepartments()
    } else {
      toast.error(response.data.message || 'Failed to delete department')
    }
  } catch (error) {
    console.error('Error deleting department:', error)
    toast.error('Failed to delete department')
  }
}

const openAddMemberDialog = () => {
  // Filter out members already in the department
  const memberIds = selectedDepartment.value.members?.map(m => m.member_id) || []
  availableMembers.value = members.value.filter(m => !memberIds.includes(m.id))
  selectedMembers.value = []
  memberRole.value = 'member'
  addMemberDialog.value = true
}

const addMembers = async () => {
  if (selectedMembers.value.length === 0) {
    toast.error('Please select at least one member')
    return
  }

  addingMembers.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.post(`/api/departments/${selectedDepartment.value.id}/bulk-members`, {
      member_ids: selectedMembers.value,
      role: memberRole.value
    }, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      toast.success(`Added ${response.data.data.total_added} members to department`)
      addMemberDialog.value = false
      // Refresh department data
      await viewDepartment(selectedDepartment.value)
    } else {
      toast.error(response.data.message || 'Failed to add members')
    }
  } catch (error) {
    console.error('Error adding members:', error)
    toast.error('Failed to add members')
  } finally {
    addingMembers.value = false
  }
}

const removeMember = async (departmentMember) => {
  if (!confirm(`Remove ${departmentMember.member.first_name} from department?`)) {
    return
  }

  try {
    const token = localStorage.getItem('token')
    const response = await axios.delete(`/api/departments/${selectedDepartment.value.id}/members/${departmentMember.member_id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      toast.success('Member removed from department')
      // Refresh department data
      await viewDepartment(selectedDepartment.value)
    }
  } catch (error) {
    console.error('Error removing member:', error)
    toast.error('Failed to remove member')
  }
}

const editMemberRole = async (departmentMember) => {
  const newRole = prompt('Enter new role (leader, co-leader, member, volunteer):', departmentMember.role)

  if (!newRole || !['leader', 'co-leader', 'member', 'volunteer'].includes(newRole)) {
    toast.error('Invalid role')
    return
  }

  try {
    const token = localStorage.getItem('token')
    const response = await axios.put(`/api/departments/${selectedDepartment.value.id}/members/${departmentMember.member_id}/role`, {
      role: newRole
    }, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      toast.success('Member role updated')
      await viewDepartment(selectedDepartment.value)
    }
  } catch (error) {
    console.error('Error updating member role:', error)
    toast.error('Failed to update member role')
  }
}

const openRecordActivityDialog = () => {
  activityForm.value = {
    type: 'meeting',
    title: '',
    description: '',
    activity_date: new Date().toISOString().slice(0, 16),
  }
  activityDialog.value = true
}

const recordActivity = async () => {
  recordingActivity.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.post(`/api/departments/${selectedDepartment.value.id}/activities`, activityForm.value, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      toast.success('Activity recorded successfully')
      activityDialog.value = false
      await viewDepartment(selectedDepartment.value)
    }
  } catch (error) {
    console.error('Error recording activity:', error)
    toast.error('Failed to record activity')
  } finally {
    recordingActivity.value = false
  }
}

const generateReport = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get(`/api/departments/${selectedDepartment.value.id}/report`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      // Download report as PDF
      const report = response.data.data
      const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' })
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `department_report_${selectedDepartment.value.name}_${new Date().toISOString().split('T')[0]}.json`
      a.click()
      a.remove()

      toast.success('Report generated successfully')
    }
  } catch (error) {
    console.error('Error generating report:', error)
    toast.error('Failed to generate report')
  }
}

const filterByCategory = (category) => {
  activeCategory.value = category
}

const clearFilter = () => {
  activeCategory.value = ''
}

const closeDialog = () => {
  dialog.value = false
  editingDepartment.value = null
  resetForm()
}

const resetForm = () => {
  Object.assign(form, {
    name: '',
    description: '',
    category: 'ministry',
    leader_id: null,
    email: '',
    phone: '',
    meeting_schedule: '',
    is_active: true,
  })
}



// Utility Functions
const truncateText = (text, length) => {
  if (!text) return ''
  if (text.length <= length) return text
  return text.substring(0, length) + '...'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US')
}

const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleString('en-US')
}

const getCategoryColor = (category) => {
  const colors = {
    ministry: 'primary',
    service: 'success',
    outreach: 'warning',
    fellowship: 'info',
    administration: 'purple',
    other: 'grey',
  }
  return colors[category] || 'grey'
}

const getRoleColor = (role) => {
  const colors = {
    leader: 'primary',
    'co-leader': 'info',
    member: 'success',
    volunteer: 'warning',
  }
  return colors[role] || 'grey'
}

const getActivityColor = (type) => {
  const colors = {
    meeting: 'primary',
    outreach: 'success',
    training: 'info',
    prayer: 'purple',
    planning: 'warning',
    other: 'grey',
  }
  return colors[type] || 'grey'
}

const getActivityIcon = (type) => {
  const icons = {
    meeting: 'mdi-account-group',
    outreach: 'mdi-hand-heart',
    training: 'mdi-school',
    prayer: 'mdi-prayer',
    planning: 'mdi-calendar-text',
    other: 'mdi-calendar',
  }
  return icons[type] || 'mdi-calendar'
}

const getAvatarColor = (member) => {
  const colors = ['primary', 'secondary', 'success', 'error', 'warning', 'info', 'purple', 'pink', 'teal']
  const name = (member.first_name + member.last_name).toLowerCase()
  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  const index = Math.abs(hash) % colors.length
  return colors[index]
}

const getInitials = (member) => {
  if (!member) return '?'
  const first = member.first_name?.[0] || ''
  const last = member.last_name?.[0] || ''
  return (first + last).toUpperCase() || '?'
}

// Lifecycle
onMounted(() => {
  fetchDepartments()
  fetchMembers()
})
</script>

<style scoped>
.stats-card {
  cursor: pointer;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stats-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.department-card {
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  height: 100%;
}

.department-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.department-meta {
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  padding-top: 12px;
  margin-top: 12px;
}

.info-item {
  padding: 8px 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.info-item:last-child {
  border-bottom: none;
}

.cursor-pointer {
  cursor: pointer;
}
</style>
