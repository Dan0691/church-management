<template>
  <div>
    <!-- Breadcrumbs -->
    <v-breadcrumbs class="mb-4" :items="breadcrumbs" divider=">"></v-breadcrumbs>

    <!-- Page Header -->
    <div class="d-flex justify-space-between align-center mb-6">
      <div>
        <h1 class="text-h4 font-weight-bold">Tasks</h1>
        <p class="text-body-1 text-medium-emphasis mt-1">
          Manage and track church tasks and assignments ({{ tasks.length }} tasks)
        </p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <v-btn color="primary" prepend-icon="mdi-plus-circle" @click="openCreateDialog">
          New Task
        </v-btn>
        <v-btn variant="outlined" prepend-icon="mdi-filter-variant" @click="showFilters = !showFilters">
          Filters
        </v-btn>
      </div>
    </div>

    <!-- Stats Cards -->
    <v-row class="mb-6">
      <v-col cols="6" md="2" v-for="stat in stats" :key="stat.title">
        <v-card class="stats-card" @click="applyStatFilter(stat.filter)">
          <v-card-text class="d-flex align-center">
            <v-avatar :color="stat.color" size="48" class="mr-3">
              <v-icon size="24">{{ stat.icon }}</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ stat.value }}</div>
              <div class="text-caption">{{ stat.title }}</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Filters Panel -->
    <v-expand-transition>
      <v-card v-if="showFilters" class="mb-6">
        <v-card-text>
          <v-row>
            <v-col cols="12" md="3">
              <v-select
                v-model="filters.status"
                :items="statusOptions"
                label="Status"
                variant="outlined"
                density="comfortable"
                clearable
                multiple
                chips
              ></v-select>
            </v-col>

            <v-col cols="12" md="3">
              <v-select
                v-model="filters.priority"
                :items="priorityOptions"
                label="Priority"
                variant="outlined"
                density="comfortable"
                clearable
                multiple
                chips
              ></v-select>
            </v-col>

            <v-col cols="12" md="3">
              <v-select
                v-model="filters.department_id"
                :items="departmentOptions"
                item-title="name"
                item-value="id"
                label="Department"
                variant="outlined"
                density="comfortable"
                clearable
              ></v-select>
            </v-col>

            <v-col cols="12" md="3">
              <v-select
                v-model="filters.assigned_to"
                :items="memberOptions"
                item-title="full_name"
                item-value="id"
                label="Assigned To"
                variant="outlined"
                density="comfortable"
                clearable
              ></v-select>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="filters.start_date"
                label="Start Date"
                type="date"
                variant="outlined"
                density="comfortable"
                clearable
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="filters.end_date"
                label="End Date"
                type="date"
                variant="outlined"
                density="comfortable"
                clearable
              ></v-text-field>
            </v-col>

            <v-col cols="12" class="d-flex justify-end">
              <v-btn variant="text" @click="clearFilters" class="mr-2">
                Clear All
              </v-btn>
              <v-btn color="primary" @click="applyFilters">
                Apply Filters
              </v-btn>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-expand-transition>

    <!-- Search -->
    <v-card class="mb-6">
      <v-card-text>
        <v-row>
          <v-col cols="12" md="8">
            <v-text-field
              v-model="searchQuery"
              placeholder="Search tasks by title, description, or tags..."
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="comfortable"
              hide-details
              @keyup.enter="searchTasks"
              @update:model-value="debouncedSearch"
            >
              <template #append>
                <v-btn
                  icon
                  size="small"
                  variant="text"
                  @click="clearSearch"
                  v-if="searchQuery"
                >
                  <v-icon>mdi-close</v-icon>
                </v-btn>
              </template>
            </v-text-field>
          </v-col>
          <v-col cols="12" md="4" class="d-flex align-center">
            <v-btn-toggle v-model="viewMode" mandatory class="ml-auto">
              <v-btn icon value="grid">
                <v-icon>mdi-view-grid</v-icon>
              </v-btn>
              <v-btn icon value="list">
                <v-icon>mdi-view-list</v-icon>
              </v-btn>
              <v-btn icon value="kanban">
                <v-icon>mdi-view-column</v-icon>
              </v-btn>
            </v-btn-toggle>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Tasks Views -->
    <!-- Grid View -->
    <v-row v-if="viewMode === 'grid' && !loading">
      <v-col cols="12" sm="6" md="4" v-for="task in filteredTasks" :key="task.id">
        <v-card class="task-card" :class="getPriorityClass(task.priority)">
          <v-card-text class="pa-4">
            <div class="d-flex justify-space-between align-start mb-3">
              <div>
                <h3 class="text-h6 font-weight-bold mb-1">{{ task.title }}</h3>
                <v-chip size="x-small" :color="getStatusColor(task.status)" class="text-white">
                  {{ formatStatus(task.status) }}
                </v-chip>
              </div>
              <v-btn icon size="small" variant="text" @click="showTaskMenu(task, $event)">
                <v-icon>mdi-dots-vertical</v-icon>
              </v-btn>
            </div>

            <p class="text-body-2 text-medium-emphasis mb-3">
              {{ truncateText(task.description, 120) }}
            </p>

            <div class="task-meta">
              <div class="d-flex align-center mb-2">
                <v-icon size="16" class="mr-1">mdi-account</v-icon>
                <span class="text-caption">{{ task.assigned_to_name || 'Unassigned' }}</span>
              </div>

              <div class="d-flex align-center mb-2">
                <v-icon size="16" class="mr-1">mdi-calendar</v-icon>
                <span class="text-caption" :class="getDueDateClass(task.due_date)">
                  {{ formatDate(task.due_date) || 'No due date' }}
                </span>
              </div>

              <div class="d-flex align-center mb-3">
                <v-icon size="16" class="mr-1">mdi-clock-outline</v-icon>
                <span class="text-caption">
                  {{ task.estimated_hours ? `${task.estimated_hours}h` : 'No estimate' }}
                  <span v-if="task.actual_hours"> / {{ task.actual_hours }}h</span>
                </span>
              </div>

              <div v-if="task.tags && task.tags.length" class="mb-3">
                <v-chip
                  v-for="tag in task.tags.slice(0, 2)"
                  :key="tag"
                  size="x-small"
                  variant="outlined"
                  class="mr-1 mb-1"
                >
                  {{ tag }}
                </v-chip>
                <span v-if="task.tags.length > 2" class="text-caption">+{{ task.tags.length - 2 }}</span>
              </div>

              <v-progress-linear
                v-if="task.estimated_hours && task.actual_hours"
                :model-value="(task.actual_hours / task.estimated_hours) * 100"
                height="6"
                rounded
                class="mb-2"
              ></v-progress-linear>

              <div class="d-flex justify-space-between align-center">
                <div class="d-flex align-center">
                  <v-icon size="16" class="mr-1">mdi-comment-outline</v-icon>
                  <span class="text-caption">{{ task.comments_count || 0 }}</span>
                </div>
                <v-btn
                  size="x-small"
                  variant="text"
                  @click="quickUpdateStatus(task)"
                  :color="getStatusColor(task.status)"
                >
                  {{ getNextStatusLabel(task.status) }}
                </v-btn>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- List View -->
    <v-card v-else-if="viewMode === 'list' && !loading">
      <v-table>
        <thead>
          <tr>
            <th class="text-left">Task</th>
            <th class="text-left">Assigned To</th>
            <th class="text-left">Due Date</th>
            <th class="text-left">Priority</th>
            <th class="text-left">Status</th>
            <th class="text-left">Hours</th>
            <th class="text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="task in filteredTasks" :key="task.id" :class="getPriorityClass(task.priority)">
            <td>
              <div>
                <strong>{{ task.title }}</strong>
                <div class="text-caption text-medium-emphasis">{{ truncateText(task.description, 60) }}</div>
              </div>
            </td>
            <td>
              <v-avatar size="32" class="mr-2">
                <v-img :src="task.assigned_to_avatar" v-if="task.assigned_to_avatar"></v-img>
                <v-icon v-else>mdi-account</v-icon>
              </v-avatar>
              {{ task.assigned_to_name || 'Unassigned' }}
            </td>
            <td>
              <span :class="getDueDateClass(task.due_date)">
                {{ formatDate(task.due_date) || 'No due date' }}
              </span>
            </td>
            <td>
              <v-chip size="x-small" :color="getPriorityColor(task.priority)" class="text-white">
                {{ formatPriority(task.priority) }}
              </v-chip>
            </td>
            <td>
              <v-chip size="x-small" :color="getStatusColor(task.status)" class="text-white">
                {{ formatStatus(task.status) }}
              </v-chip>
            </td>
            <td>
              <div v-if="task.estimated_hours">
                {{ task.actual_hours || 0 }} / {{ task.estimated_hours }}h
              </div>
              <div v-else>-</div>
            </td>
            <td>
              <v-btn icon size="small" @click="viewTask(task)">
                <v-icon>mdi-eye</v-icon>
              </v-btn>
              <v-btn icon size="small" @click="editTask(task)">
                <v-icon>mdi-pencil</v-icon>
              </v-btn>
              <v-btn icon size="small" @click="quickUpdateStatus(task)">
                <v-icon>mdi-check</v-icon>
              </v-btn>
            </td>
          </tr>
        </tbody>
      </v-table>
    </v-card>

    <!-- Kanban View -->
    <div v-else-if="viewMode === 'kanban' && !loading" class="kanban-board">
      <v-row>
        <v-col cols="12" md="3" v-for="column in kanbanColumns" :key="column.status">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-chip :color="getStatusColor(column.status)" class="text-white">
                {{ column.label }}
              </v-chip>
              <v-spacer></v-spacer>
              <span class="text-caption">{{ columnTasks[column.status].length }}</span>
            </v-card-title>
            <v-card-text class="kanban-column">
              <draggable
                v-model="columnTasks[column.status]"
                group="tasks"
                item-key="id"
                class="kanban-list"
                @change="handleDragChange($event, column.status)"
              >
                <template #item="{ element: task }">
                  <v-card class="mb-3 task-kanban-card" :class="getPriorityClass(task.priority)">
                    <v-card-text class="pa-3">
                      <div class="d-flex justify-space-between align-start mb-2">
                        <h4 class="text-subtitle-2 font-weight-bold">{{ task.title }}</h4>
                        <v-chip size="x-small" :color="getPriorityColor(task.priority)" class="text-white">
                          {{ task.priority }}
                        </v-chip>
                      </div>
                      <div class="text-caption text-medium-emphasis mb-2">
                        {{ truncateText(task.description, 80) }}
                      </div>
                      <div class="d-flex justify-space-between align-center">
                        <span class="text-caption">{{ task.assigned_to_name || 'Unassigned' }}</span>
                        <span class="text-caption" :class="getDueDateClass(task.due_date)">
                          {{ formatDate(task.due_date) }}
                        </span>
                      </div>
                    </v-card-text>
                  </v-card>
                </template>
              </draggable>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Loading State -->
    <v-row v-if="loading">
      <v-col cols="12" sm="6" md="4" v-for="n in 6" :key="n">
        <v-skeleton-loader type="card"></v-skeleton-loader>
      </v-col>
    </v-row>

    <!-- Empty State -->
    <div v-else-if="filteredTasks.length === 0" class="text-center py-12">
      <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-checkbox-multiple-blank-outline</v-icon>
      <h3 class="text-h6 mb-2">No tasks found</h3>
      <p class="text-medium-emphasis mb-4">
        {{ hasFilters ? 'Try changing your filters' : 'Create your first task to get started' }}
      </p>
      <v-btn color="primary" @click="openCreateDialog">
        Create First Task
      </v-btn>
    </div>

    <!-- Task Menu -->
    <v-menu v-model="taskMenu.show" :position-x="taskMenu.x" :position-y="taskMenu.y" absolute offset-y>
      <v-list density="compact">
        <v-list-item @click="viewTask(taskMenu.task)">
          <template #prepend>
            <v-icon>mdi-eye</v-icon>
          </template>
          <v-list-item-title>View Details</v-list-item-title>
        </v-list-item>
        <v-list-item @click="editTask(taskMenu.task)">
          <template #prepend>
            <v-icon>mdi-pencil</v-icon>
          </template>
          <v-list-item-title>Edit Task</v-list-item-title>
        </v-list-item>
        <v-list-item @click="quickUpdateStatus(taskMenu.task)">
          <template #prepend>
            <v-icon>mdi-check</v-icon>
          </template>
          <v-list-item-title>Update Status</v-list-item-title>
        </v-list-item>
        <v-list-item @click="deleteTask(taskMenu.task)">
          <template #prepend>
            <v-icon color="error">mdi-delete</v-icon>
          </template>
          <v-list-item-title>Delete Task</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-menu>

    <!-- View/Edit Task Dialog -->
    <v-dialog v-model="taskDialog" max-width="800" scrollable>
      <v-card>
        <v-card-title class="d-flex justify-space-between align-center">
          <span class="text-h5">{{ editingTask ? 'Edit Task' : 'Task Details' }}</span>
          <v-btn icon @click="taskDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="pa-4">
          <v-form ref="form" v-if="selectedTask" @submit.prevent="saveTask">
            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="taskForm.title"
                  label="Task Title *"
                  variant="outlined"
                  :rules="[v => !!v || 'Title is required']"
                  required
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="taskForm.description"
                  label="Description"
                  variant="outlined"
                  rows="3"
                  placeholder="Task description and details..."
                ></v-textarea>
              </v-col>

              <v-col cols="12" md="6">
                <v-select
                  v-model="taskForm.department_id"
                  :items="departmentOptions"
                  item-title="name"
                  item-value="id"
                  label="Department"
                  variant="outlined"
                  clearable
                ></v-select>
              </v-col>

              <v-col cols="12" md="6">
                <v-select
                  v-model="taskForm.assigned_to"
                  :items="memberOptions"
                  item-title="full_name"
                  item-value="id"
                  label="Assigned To"
                  variant="outlined"
                  clearable
                ></v-select>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="taskForm.due_date"
                  label="Due Date"
                  type="date"
                  variant="outlined"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-select
                  v-model="taskForm.priority"
                  :items="priorityOptions"
                  label="Priority *"
                  variant="outlined"
                  :rules="[v => !!v || 'Priority is required']"
                  required
                ></v-select>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="taskForm.estimated_hours"
                  label="Estimated Hours"
                  type="number"
                  variant="outlined"
                  min="0"
                  step="0.5"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="taskForm.actual_hours"
                  label="Actual Hours"
                  type="number"
                  variant="outlined"
                  min="0"
                  step="0.5"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-combobox
                  v-model="taskForm.tags"
                  label="Tags"
                  variant="outlined"
                  multiple
                  chips
                  clearable
                  :items="suggestedTags"
                ></v-combobox>
              </v-col>

              <v-col cols="12">
                <v-switch
                  v-model="taskForm.is_public"
                  label="Public Task"
                  color="primary"
                  hide-details
                ></v-switch>
              </v-col>

              <!-- Status Section -->
              <v-col cols="12" v-if="selectedTask">
                <v-divider class="my-4"></v-divider>
                <h3 class="text-h6 mb-3">Status</h3>
                <v-select
                  v-model="taskForm.status"
                  :items="statusOptions"
                  label="Status"
                  variant="outlined"
                ></v-select>

                <v-textarea
                  v-model="taskForm.completion_notes"
                  label="Completion Notes"
                  variant="outlined"
                  rows="2"
                  placeholder="Notes about task completion..."
                  v-if="taskForm.status === 'completed'"
                ></v-textarea>
              </v-col>

              <!-- Comments Section -->
              <v-col cols="12" v-if="selectedTask && selectedTask.comments">
                <v-divider class="my-4"></v-divider>
                <h3 class="text-h6 mb-3">Comments ({{ selectedTask.comments.length }})</h3>

                <div class="comments-section">
                  <div v-for="comment in selectedTask.comments" :key="comment.id" class="comment-item mb-4">
                    <div class="d-flex align-start">
                      <v-avatar size="32" class="mr-3">
                        <v-img :src="comment.user?.avatar" v-if="comment.user?.avatar"></v-img>
                        <v-icon v-else>mdi-account</v-icon>
                      </v-avatar>
                      <div class="flex-grow-1">
                        <div class="d-flex justify-space-between align-center mb-1">
                          <strong>{{ comment.user?.name }}</strong>
                          <span class="text-caption text-medium-emphasis">
                            {{ formatDateTime(comment.created_at) }}
                            <v-chip size="x-small" v-if="comment.is_private" color="warning" class="ml-1">
                              Private
                            </v-chip>
                          </span>
                        </div>
                        <p class="text-body-2">{{ comment.comment }}</p>
                      </div>
                    </div>
                  </div>

                  <div class="add-comment mt-4">
                    <v-textarea
                      v-model="newComment"
                      label="Add a comment"
                      variant="outlined"
                      rows="2"
                      placeholder="Type your comment here..."
                    ></v-textarea>
                    <div class="d-flex justify-space-between align-center mt-2">
                      <v-switch
                        v-model="newCommentIsPrivate"
                        label="Private comment"
                        color="primary"
                        hide-details
                        density="compact"
                      ></v-switch>
                      <v-btn color="primary" @click="addComment" :disabled="!newComment.trim()">
                        Add Comment
                      </v-btn>
                    </div>
                  </div>
                </div>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>

        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="taskDialog = false">
            Cancel
          </v-btn>
          <v-btn color="primary" @click="saveTask" :loading="saving">
            {{ editingTask ? 'Update Task' : 'Save Task' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import draggable from '@marshallswain/vuedraggable'

const toast = useToast()

// Data
const loading = ref(false)
const saving = ref(false)
const taskDialog = ref(false)
const viewDialog = ref(false)
const tasks = ref([])
const selectedTask = ref(null)
const editingTask = ref(null)
const searchQuery = ref('')
const viewMode = ref('grid')
const showFilters = ref(false)
const taskMenu = ref({ show: false, x: 0, y: 0, task: null })
const newComment = ref('')
const newCommentIsPrivate = ref(false)

// Filters
const filters = ref({
  status: [],
  priority: [],
  department_id: null,
  assigned_to: null,
  start_date: null,
  end_date: null,
})

// Form
const taskForm = ref({
  title: '',
  description: '',
  department_id: null,
  assigned_to: null,
  due_date: null,
  priority: 'medium',
  status: 'pending',
  estimated_hours: null,
  actual_hours: null,
  is_public: false,
  tags: [],
  completion_notes: '',
})

// Options
const statusOptions = ref([
  { title: 'Pending', value: 'pending' },
  { title: 'In Progress', value: 'in_progress' },
  { title: 'Completed', value: 'completed' },
  { title: 'Cancelled', value: 'cancelled' },
])

const priorityOptions = ref([
  { title: 'Low', value: 'low' },
  { title: 'Medium', value: 'medium' },
  { title: 'High', value: 'high' },
  { title: 'Urgent', value: 'urgent' },
])

const departmentOptions = ref([])
const memberOptions = ref([])
const suggestedTags = ref(['Meeting', 'Event', 'Maintenance', 'Administration', 'Outreach', 'Worship', 'Youth', 'Children'])

// Stats
const stats = ref([
  { title: 'Total Tasks', value: 0, icon: 'mdi-checkbox-multiple-blank', color: 'primary', filter: {} },
  { title: 'Pending', value: 0, icon: 'mdi-clock-outline', color: 'warning', filter: { status: ['pending'] } },
  { title: 'In Progress', value: 0, icon: 'mdi-progress-clock', color: 'info', filter: { status: ['in_progress'] } },
  { title: 'Completed', value: 0, icon: 'mdi-check-circle', color: 'success', filter: { status: ['completed'] } },
  { title: 'Overdue', value: 0, icon: 'mdi-alert', color: 'error', filter: { overdue: true } },
  { title: 'My Tasks', value: 0, icon: 'mdi-account', color: 'secondary', filter: { assigned_to: 'me' } },
])

// Kanban
const kanbanColumns = ref([
  { status: 'pending', label: 'Pending' },
  { status: 'in_progress', label: 'In Progress' },
  { status: 'completed', label: 'Completed' },
  { status: 'cancelled', label: 'Cancelled' },
])

const columnTasks = ref({
  pending: [],
  in_progress: [],
  completed: [],
  cancelled: [],
})

// Breadcrumbs
const breadcrumbs = ref([
  { title: 'Dashboard', to: '/' },
  { title: 'Tasks', disabled: true }
])

// Computed
const filteredTasks = computed(() => {
  let filtered = tasks.value

  // Apply search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(task =>
      task.title.toLowerCase().includes(query) ||
      task.description?.toLowerCase().includes(query) ||
      task.tags?.some(tag => tag.toLowerCase().includes(query))
    )
  }

  // Apply filters
  if (filters.value.status.length > 0) {
    filtered = filtered.filter(task => filters.value.status.includes(task.status))
  }

  if (filters.value.priority.length > 0) {
    filtered = filtered.filter(task => filters.value.priority.includes(task.priority))
  }

  if (filters.value.department_id) {
    filtered = filtered.filter(task => task.department_id === filters.value.department_id)
  }

  if (filters.value.assigned_to) {
    filtered = filtered.filter(task => task.assigned_to === filters.value.assigned_to)
  }

  if (filters.value.start_date) {
    filtered = filtered.filter(task => task.due_date >= filters.value.start_date)
  }

  if (filters.value.end_date) {
    filtered = filtered.filter(task => task.due_date <= filters.value.end_date)
  }

  return filtered
})

const hasFilters = computed(() => {
  return Object.values(filters.value).some(val =>
    (Array.isArray(val) && val.length > 0) ||
    (typeof val === 'string' && val) ||
    val
  )
})

// Methods
const fetchTasks = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const params = new URLSearchParams()

    // Add filters to params
    Object.entries(filters.value).forEach(([key, value]) => {
      if (value) {
        if (Array.isArray(value) && value.length > 0) {
          value.forEach(v => params.append(`${key}[]`, v))
        } else {
          params.append(key, value)
        }
      }
    })

    const response = await axios.get(`/api/tasks?${params}`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      tasks.value = response.data.data.data || response.data.data

      // Update stats
      if (response.data.stats) {
        stats.value = stats.value.map(stat => ({
          ...stat,
          value: response.data.stats[stat.title.toLowerCase().replace(/\s+/g, '_')] || 0
        }))
      }

      updateKanbanColumns()
    }
  } catch (error) {
    console.error('Error fetching tasks:', error)
    toast.error('Failed to load tasks')
  } finally {
    loading.value = false
  }
}

const fetchOptions = async () => {
  try {
    const token = localStorage.getItem('token')

    // Fetch departments
    const deptResponse = await axios.get('/api/departments', {
      headers: { Authorization: `Bearer ${token}` }
    })
    departmentOptions.value = deptResponse.data.data || []

    // Fetch members
    const membersResponse = await axios.get('/api/members', {
      headers: { Authorization: `Bearer ${token}` }
    })
    memberOptions.value = membersResponse.data.data || []
  } catch (error) {
    console.error('Error fetching options:', error)
  }
}

const openCreateDialog = () => {
  resetForm()
  selectedTask.value = null
  editingTask.value = null
  taskDialog.value = true
}

const viewTask = async (task) => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get(`/api/tasks/${task.id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      selectedTask.value = response.data.data
      taskForm.value = { ...response.data.data }
      editingTask.value = task
      taskDialog.value = true
    }
  } catch (error) {
    console.error('Error fetching task details:', error)
    toast.error('Failed to load task details')
  }
}

const editTask = (task) => {
  selectedTask.value = task
  taskForm.value = { ...task }
  editingTask.value = task
  taskDialog.value = true
}

const saveTask = async () => {
  saving.value = true
  try {
    const token = localStorage.getItem('token')
    let response

    if (editingTask.value) {
      response = await axios.put(`/api/tasks/${editingTask.value.id}`, taskForm.value, {
        headers: { Authorization: `Bearer ${token}` }
      })
    } else {
      response = await axios.post('/api/tasks', taskForm.value, {
        headers: { Authorization: `Bearer ${token}` }
      })
    }

    if (response.data.success) {
      toast.success(response.data.message || 'Task saved successfully')
      taskDialog.value = false
      fetchTasks()
    } else {
      toast.error(response.data.message || 'Failed to save task')
    }
  } catch (error) {
    console.error('Error saving task:', error)
    toast.error(error.response?.data?.message || 'Failed to save task')
  } finally {
    saving.value = false
  }
}

const deleteTask = async (task) => {
  if (!confirm(`Are you sure you want to delete "${task.title}"?`)) {
    return
  }

  try {
    const token = localStorage.getItem('token')
    const response = await axios.delete(`/api/tasks/${task.id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      toast.success('Task deleted successfully')
      fetchTasks()
    }
  } catch (error) {
    console.error('Error deleting task:', error)
    toast.error('Failed to delete task')
  }
}

const quickUpdateStatus = async (task) => {
  const nextStatus = getNextStatus(task.status)
  try {
    const token = localStorage.getItem('token')
    const response = await axios.post(`/api/tasks/${task.id}/complete`, {
      status: nextStatus
    }, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      toast.success('Task status updated')
      fetchTasks()
    }
  } catch (error) {
    console.error('Error updating task status:', error)
    toast.error('Failed to update status')
  }
}

const assignTask = async (taskId, memberId) => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.post(`/api/tasks/${taskId}/assign`, {
      assigned_to: memberId
    }, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      toast.success('Task assigned successfully')
      fetchTasks()
    }
  } catch (error) {
    console.error('Error assigning task:', error)
    toast.error('Failed to assign task')
  }
}

const addComment = async () => {
  if (!newComment.value.trim() || !selectedTask.value) return

  try {
    const token = localStorage.getItem('token')
    const response = await axios.post(`/api/tasks/${selectedTask.value.id}/add-comment`, {
      comment: newComment.value,
      is_private: newCommentIsPrivate.value
    }, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      toast.success('Comment added')
      newComment.value = ''
      newCommentIsPrivate.value = false
      // Refresh task details
      viewTask(selectedTask.value)
    }
  } catch (error) {
    console.error('Error adding comment:', error)
    toast.error('Failed to add comment')
  }
}

const handleDragChange = async (event, newStatus) => {
  if (event.added) {
    const task = event.added.element
    try {
      const token = localStorage.getItem('token')
      await axios.post(`/api/tasks/${task.id}/complete`, {
        status: newStatus
      }, {
        headers: { Authorization: `Bearer ${token}` }
      })
    } catch (error) {
      console.error('Error updating task status via drag:', error)
      toast.error('Failed to update task status')
      // Revert the change
      fetchTasks()
    }
  }
}

const updateKanbanColumns = () => {
  kanbanColumns.value.forEach(column => {
    columnTasks.value[column.status] = tasks.value.filter(task => task.status === column.status)
  })
}

const showTaskMenu = (task, event) => {
  event.preventDefault()
  taskMenu.value = {
    show: true,
    x: event.clientX,
    y: event.clientY,
    task: task
  }
}

const applyStatFilter = (filter) => {
  if (filter.assigned_to === 'me') {
    filters.value.assigned_to = getCurrentMemberId()
  } else {
    filters.value = { ...filters.value, ...filter }
  }
  applyFilters()
}

const applyFilters = () => {
  fetchTasks()
}

const clearFilters = () => {
  filters.value = {
    status: [],
    priority: [],
    department_id: null,
    assigned_to: null,
    start_date: null,
    end_date: null,
  }
  fetchTasks()
}

const searchTasks = async () => {
  if (!searchQuery.value.trim()) {
    fetchTasks()
    return
  }

  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/tasks/search', {
      headers: { Authorization: `Bearer ${token}` },
      params: { query: searchQuery.value }
    })

    if (response.data.success) {
      tasks.value = response.data.data
    }
  } catch (error) {
    console.error('Error searching tasks:', error)
  } finally {
    loading.value = false
  }
}

const clearSearch = () => {
  searchQuery.value = ''
  fetchTasks()
}

const getCurrentMemberId = () => {
  // This should be implemented based on your auth system
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  return user.member_id
}

// Utility Functions
const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('en-US')
}

const formatDateTime = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleString('en-US')
}

const formatStatus = (status) => {
  return statusOptions.value.find(s => s.value === status)?.title || status
}

const formatPriority = (priority) => {
  return priorityOptions.value.find(p => p.value === priority)?.title || priority
}

const getStatusColor = (status) => {
  const colors = {
    pending: 'warning',
    in_progress: 'info',
    completed: 'success',
    cancelled: 'error'
  }
  return colors[status] || 'grey'
}

const getPriorityColor = (priority) => {
  const colors = {
    low: 'success',
    medium: 'warning',
    high: 'error',
    urgent: 'deep-orange-darken-4'
  }
  return colors[priority] || 'grey'
}

const getPriorityClass = (priority) => {
  return `priority-${priority}`
}

const getDueDateClass = (dueDate) => {
  if (!dueDate) return ''
  const today = new Date()
  const due = new Date(dueDate)
  const diffDays = Math.ceil((due - today) / (1000 * 60 * 60 * 24))

  if (diffDays < 0) return 'text-error' // Overdue
  if (diffDays <= 3) return 'text-warning' // Due soon
  return ''
}

const getNextStatus = (currentStatus) => {
  const statusFlow = ['pending', 'in_progress', 'completed']
  const currentIndex = statusFlow.indexOf(currentStatus)
  return currentIndex < statusFlow.length - 1 ? statusFlow[currentIndex + 1] : 'completed'
}

const getNextStatusLabel = (currentStatus) => {
  const nextStatus = getNextStatus(currentStatus)
  return statusOptions.value.find(s => s.value === nextStatus)?.title || nextStatus
}

const truncateText = (text, length) => {
  if (!text) return ''
  if (text.length <= length) return text
  return text.substring(0, length) + '...'
}

const resetForm = () => {
  taskForm.value = {
    title: '',
    description: '',
    department_id: null,
    assigned_to: null,
    due_date: null,
    priority: 'medium',
    status: 'pending',
    estimated_hours: null,
    actual_hours: null,
    is_public: false,
    tags: [],
    completion_notes: '',
  }
}

// Debounced search
let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    if (searchQuery.value) {
      searchTasks()
    }
  }, 500)
}

// Watch for tasks changes to update kanban
watch(tasks, updateKanbanColumns)

// Lifecycle
onMounted(() => {
  fetchTasks()
  fetchOptions()
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

.task-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  height: 100%;
}

.task-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.priority-low {
  border-left: 4px solid #4CAF50;
}

.priority-medium {
  border-left: 4px solid #FF9800;
}

.priority-high {
  border-left: 4px solid #F44336;
}

.priority-urgent {
  border-left: 4px solid #B71C1C;
}

.task-meta {
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  padding-top: 12px;
}

.kanban-board {
  overflow-x: auto;
}

.kanban-column {
  min-height: 400px;
  max-height: 600px;
  overflow-y: auto;
}

.kanban-list {
  min-height: 100px;
}

.task-kanban-card {
  cursor: grab;
}

.task-kanban-card:active {
  cursor: grabbing;
}

.comment-item {
  padding: 12px;
  border-radius: 8px;
  background: rgba(0, 0, 0, 0.02);
}

.add-comment {
  padding: 12px;
  border-radius: 8px;
  background: rgba(0, 0, 0, 0.02);
  border: 1px solid rgba(0, 0, 0, 0.1);
}

.cursor-pointer {
  cursor: pointer;
}
</style>
