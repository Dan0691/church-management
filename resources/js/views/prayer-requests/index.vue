<template>
  <div>
    <!-- Breadcrumbs -->
    <v-breadcrumbs class="mb-4" :items="breadcrumbs" divider=">"></v-breadcrumbs>

    <!-- Page Header -->
    <div class="d-flex justify-space-between align-center mb-6">
      <div>
        <h1 class="text-h4 font-weight-bold">Prayer Requests</h1>
        <p class="text-body-1 text-medium-emphasis mt-1">
          Submit and pray for church prayer requests ({{ prayerRequests.length }} total)
        </p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <v-btn color="primary" prepend-icon="mdi-pray" @click="openCreateDialog">
          New Prayer Request
        </v-btn>
        <v-btn variant="outlined" prepend-icon="mdi-download" @click="exportRequests">
          Export
        </v-btn>
      </div>
    </div>

    <!-- Stats Cards -->
    <v-row class="mb-6">
      <v-col cols="6" md="3" v-for="stat in stats" :key="stat.title">
        <v-card class="stats-card" @click="filterRequests(stat.filter)">
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

    <!-- Urgent Prayer Requests -->
    <v-card v-if="urgentRequests.length > 0" class="mb-6 urgent-card">
      <v-card-title class="text-h6 text-error">
        <v-icon color="error" class="mr-2">mdi-alert</v-icon>
        Urgent Prayer Requests
      </v-card-title>
      <v-card-text>
        <v-list lines="two">
          <v-list-item
            v-for="request in urgentRequests"
            :key="request.id"
            @click="viewRequest(request)"
            class="mb-2 urgent-item"
          >
            <template #prepend>
              <v-avatar color="error" size="48" class="mr-3">
                <v-icon color="white">mdi-alert</v-icon>
              </v-avatar>
            </template>
            <v-list-item-title class="font-weight-bold">{{ request.title }}</v-list-item-title>
            <v-list-item-subtitle>
              {{ request.short_request }}
            </v-list-item-subtitle>
            <template #append>
              <v-btn icon @click.stop="prayForRequest(request)">
                <v-icon :color="request.user_prayed ? 'success' : ''">mdi-hands-pray</v-icon>
              </v-btn>
            </template>
          </v-list-item>
        </v-list>
      </v-card-text>
    </v-card>

    <!-- Filter Chips -->
    <div class="d-flex flex-wrap gap-2 mb-4">
      <v-chip
        v-for="filter in quickFilters"
        :key="filter.value"
        :color="activeFilter === filter.value ? 'primary' : 'default'"
        @click="applyQuickFilter(filter)"
        class="cursor-pointer"
      >
        {{ filter.label }}
      </v-chip>
      <v-chip
        v-if="activeFilter"
        color="warning"
        @click="clearFilter"
        class="cursor-pointer"
      >
        Clear Filter
      </v-chip>
    </div>

    <!-- Search and Filter Bar -->
    <v-card class="mb-6">
      <v-card-text>
        <v-row align="center">
          <v-col cols="12" md="4">
            <v-text-field
              v-model="search"
              placeholder="Search prayer requests..."
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="comfortable"
              hide-details
              @update:model-value="debouncedFetchRequests"
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="3">
            <v-select
              v-model="categoryFilter"
              :items="categoryOptions"
              label="Category"
              variant="outlined"
              density="comfortable"
              hide-details
              clearable
              @update:model-value="fetchPrayerRequests"
            ></v-select>
          </v-col>
          <v-col cols="12" md="3">
            <v-select
              v-model="statusFilter"
              :items="statusOptions"
              label="Status"
              variant="outlined"
              density="comfortable"
              hide-details
              clearable
              @update:model-value="fetchPrayerRequests"
            ></v-select>
          </v-col>
          <v-col cols="12" md="2">
            <v-btn variant="tonal" color="primary" @click="resetFilters" block>
              Reset
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Prayer Requests List -->
    <v-row>
      <v-col cols="12" md="6" v-for="request in filteredRequests" :key="request.id">
        <v-card class="prayer-card" :class="{ 'answered-card': request.is_answered }">
          <v-card-text>
            <div class="d-flex justify-space-between align-start mb-3">
              <div>
                <h3 class="text-h6 font-weight-bold">{{ request.title }}</h3>
                <div class="d-flex align-center mt-1">
                  <v-chip size="small" :color="getCategoryColor(request.category)" class="mr-2">
                    {{ request.category }}
                  </v-chip>
                  <v-chip size="small" :color="getPriorityColor(request.priority)">
                    {{ request.priority }}
                  </v-chip>
                </div>
              </div>
              <div class="text-right">
                <v-chip size="small" :color="request.is_answered ? 'success' : 'warning'">
                  {{ request.is_answered ? 'Answered' : 'Pending' }}
                </v-chip>
                <div class="text-caption text-medium-emphasis mt-1">
                  {{ formatRelativeDate(request.created_at) }}
                </div>
              </div>
            </div>

            <p class="text-body-2 mb-4">{{ request.short_request }}</p>

            <div class="d-flex justify-space-between align-center">
              <div class="d-flex align-center">
                <v-avatar size="32" :color="getAvatarColor(request.member)" class="mr-2">
                  <span class="text-white text-caption">{{ getInitials(request.member) }}</span>
                </v-avatar>
                <div>
                  <div class="text-caption font-weight-medium">
                    {{ request.member ? request.member.first_name + ' ' + request.member.last_name : 'Anonymous' }}
                  </div>
                  <div class="text-caption text-medium-emphasis">
                    {{ request.privacy }}
                  </div>
                </div>
              </div>

              <div class="d-flex align-center gap-2">
                <div class="text-center">
                  <v-icon size="20" :color="request.user_prayed ? 'success' : ''">mdi-hands-pray</v-icon>
                  <div class="text-caption">{{ request.prayer_count }}</div>
                </div>
                <div class="d-flex gap-1">
                  <v-btn icon size="small" @click="prayForRequest(request)">
                    <v-icon size="20">mdi-hands-pray</v-icon>
                  </v-btn>
                  <v-btn icon size="small" @click="viewRequest(request)">
                    <v-icon size="20">mdi-eye</v-icon>
                  </v-btn>
                  <v-btn v-if="canEdit(request)" icon size="small" @click="editRequest(request)">
                    <v-icon size="20">mdi-pencil</v-icon>
                  </v-btn>
                </div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Empty State -->
    <v-card v-if="filteredRequests.length === 0 && !loading" class="text-center py-12">
      <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-pray</v-icon>
      <h3 class="text-h6 mb-2">No prayer requests found</h3>
      <p class="text-medium-emphasis mb-4">
        {{ search || activeFilter ? 'Try changing your search or filters' : 'Submit the first prayer request' }}
      </p>
      <v-btn color="primary" @click="openCreateDialog">
        Submit First Request
      </v-btn>
    </v-card>

    <!-- Create/Edit Dialog -->
    <v-dialog v-model="dialog" max-width="600">
      <v-card>
        <v-card-title class="d-flex justify-space-between align-center">
          <span class="text-h5">{{ editingRequest ? 'Edit Prayer Request' : 'New Prayer Request' }}</span>
          <v-btn icon @click="closeDialog">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>
          <v-form ref="form" @submit.prevent="saveRequest">
            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="form.title"
                  label="Title *"
                  variant="outlined"
                  :rules="[v => !!v || 'Title is required']"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-textarea
                  v-model="form.request"
                  label="Prayer Request *"
                  variant="outlined"
                  rows="4"
                  :rules="[v => !!v || 'Request is required']"
                  required
                  placeholder="Please share your prayer request here..."
                ></v-textarea>
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="form.category"
                  :items="categoryOptions"
                  label="Category *"
                  variant="outlined"
                  :rules="[v => !!v || 'Category is required']"
                  required
                ></v-select>
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="form.priority"
                  :items="priorityOptions"
                  label="Priority *"
                  variant="outlined"
                  required
                ></v-select>
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="form.privacy"
                  :items="privacyOptions"
                  label="Privacy *"
                  variant="outlined"
                  required
                ></v-select>
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="form.member_id"
                  :items="members"
                  item-title="full_name"
                  item-value="id"
                  label="Requester"
                  variant="outlined"
                  clearable
                  :hint="auth.member_id ? 'Defaults to your member profile' : ''"
                ></v-select>
              </v-col>
              <v-col cols="12">
                <v-row>
                  <v-col cols="6">
                    <v-switch
                      v-model="form.allow_prayers"
                      label="Allow Prayers"
                      color="primary"
                      hide-details
                    ></v-switch>
                  </v-col>
                  <v-col cols="6">
                    <v-switch
                      v-model="form.allow_comments"
                      label="Allow Comments"
                      color="primary"
                      hide-details
                    ></v-switch>
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="closeDialog">
            Cancel
          </v-btn>
          <v-btn color="primary" @click="saveRequest" :loading="saving">
            {{ editingRequest ? 'Update' : 'Submit' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- View Request Dialog -->
    <v-dialog v-model="viewDialog" max-width="800">
      <v-card v-if="selectedRequest">
        <v-card-title class="d-flex justify-space-between align-center">
          <div class="d-flex align-center">
            <v-avatar :color="getCategoryColor(selectedRequest.category)" size="48" class="mr-3">
              <v-icon color="white">mdi-pray</v-icon>
            </v-avatar>
            <div>
              <h2 class="text-h5">{{ selectedRequest.title }}</h2>
              <div class="d-flex align-center mt-1">
                <v-chip size="small" :color="getCategoryColor(selectedRequest.category)" class="mr-2">
                  {{ selectedRequest.category }}
                </v-chip>
                <v-chip size="small" :color="getPriorityColor(selectedRequest.priority)">
                  {{ selectedRequest.priority }}
                </v-chip>
                <v-chip size="small" :color="selectedRequest.is_answered ? 'success' : 'warning'" class="ml-2">
                  {{ selectedRequest.is_answered ? 'Answered' : 'Pending' }}
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
          <v-tab value="request">Request</v-tab>
          <v-tab value="prayers">Prayers ({{ selectedRequest.prayer_count }})</v-tab>
          <v-tab value="comments">Comments</v-tab>
          <v-tab value="answers">Answers</v-tab>
        </v-tabs>

        <v-divider></v-divider>

        <v-card-text>
          <v-window v-model="viewTab">
            <!-- Request Tab -->
            <v-window-item value="request">
              <v-row class="mt-2">
                <v-col cols="12">
                  <div class="info-item mb-4">
                    <div class="text-caption text-medium-emphasis mb-1">Prayer Request</div>
                    <div class="text-body-1" style="white-space: pre-line;">{{ selectedRequest.request }}</div>
                  </div>
                </v-col>

                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">Requester</div>
                    <div class="d-flex align-center mt-1">
                      <v-avatar size="32" :color="getAvatarColor(selectedRequest.member)" class="mr-2">
                        <span class="text-white text-caption">{{ getInitials(selectedRequest.member) }}</span>
                      </v-avatar>
                      <div>
                        <div class="text-body-1">
                          {{ selectedRequest.member ? selectedRequest.member.first_name + ' ' + selectedRequest.member.last_name : 'Anonymous' }}
                        </div>
                        <div class="text-caption text-medium-emphasis">
                          {{ formatDate(selectedRequest.created_at) }}
                        </div>
                      </div>
                    </div>
                  </div>
                </v-col>

                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">Privacy</div>
                    <div class="text-body-1">{{ selectedRequest.privacy }}</div>
                  </div>
                </v-col>

                <v-col cols="12">
                  <div class="d-flex justify-center mt-6">
                    <v-btn
                      color="primary"
                      size="large"
                      @click="prayForRequest(selectedRequest)"
                      :disabled="selectedRequest.user_prayed || !selectedRequest.allow_prayers"
                    >
                      <v-icon left>mdi-hands-pray</v-icon>
                      {{ selectedRequest.user_prayed ? 'You Prayed For This' : 'Pray For This Request' }}
                    </v-btn>
                  </div>
                </v-col>
              </v-row>
            </v-window-item>

            <!-- Prayers Tab -->
            <v-window-item value="prayers">
              <div v-if="selectedRequest.prayer_count > 0" class="mt-4">
                <v-list>
                  <v-list-item v-for="prayer in selectedRequest.prayed_by" :key="prayer.id">
                    <template #prepend>
                      <v-avatar size="40" :color="getAvatarColor(prayer)" class="mr-3">
                        <span class="text-white">{{ getInitials(prayer) }}</span>
                      </v-avatar>
                    </template>
                    <v-list-item-title>{{ prayer.name }}</v-list-item-title>
                    <v-list-item-subtitle>
                      Prayed {{ formatRelativeDate(prayer.pivot.prayed_at) }}
                    </v-list-item-subtitle>
                  </v-list-item>
                </v-list>
              </div>
              <div v-else class="text-center py-8">
                <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-hands-pray-outline</v-icon>
                <h3 class="text-h6 mb-2">No Prayers Yet</h3>
                <p class="text-medium-emphasis mb-4">Be the first to pray for this request</p>
                <v-btn color="primary" @click="prayForRequest(selectedRequest)">
                  Pray Now
                </v-btn>
              </div>
            </v-window-item>

            <!-- Comments Tab -->
            <v-window-item value="comments">
              <div class="mt-4">
                <v-textarea
                  v-model="newComment"
                  label="Add a comment"
                  variant="outlined"
                  rows="2"
                  class="mb-4"
                  :disabled="!selectedRequest.allow_comments"
                  :hint="!selectedRequest.allow_comments ? 'Comments are disabled for this request' : ''"
                ></v-textarea>
                <v-btn
                  color="primary"
                  @click="addComment"
                  :disabled="!newComment.trim() || !selectedRequest.allow_comments"
                  class="mb-6"
                >
                  Add Comment
                </v-btn>

                <v-list v-if="selectedRequest.comments?.length > 0">
                  <v-list-item v-for="comment in selectedRequest.comments" :key="comment.id">
                    <template #prepend>
                      <v-avatar size="40" :color="getAvatarColor(comment.user)" class="mr-3">
                        <span class="text-white">{{ getInitials(comment.user) }}</span>
                      </v-avatar>
                    </template>
                    <v-list-item-title>{{ comment.user.name }}</v-list-item-title>
                    <v-list-item-subtitle>
                      {{ formatRelativeDate(comment.created_at) }}
                      <v-chip v-if="comment.is_private" size="x-small" color="warning" class="ml-2">
                        Private
                      </v-chip>
                    </v-list-item-subtitle>
                    <v-list-item-content class="mt-2">
                      {{ comment.comment }}
                    </v-list-item-content>
                  </v-list-item>
                </v-list>
                <div v-else class="text-center py-8">
                  <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-comment-outline</v-icon>
                  <h3 class="text-h6 mb-2">No Comments Yet</h3>
                  <p class="text-medium-emphasis">Be the first to comment on this prayer request</p>
                </div>
              </div>
            </v-window-item>

            <!-- Answers Tab -->
            <v-window-item value="answers">
              <div v-if="selectedRequest.answers?.length > 0" class="mt-4">
                <v-list>
                  <v-list-item v-for="answer in selectedRequest.answers" :key="answer.id">
                    <template #prepend>
                      <v-avatar size="40" color="success" class="mr-3">
                        <v-icon color="white">mdi-check</v-icon>
                      </v-avatar>
                    </template>
                    <v-list-item-title>{{ answer.user?.name || 'Anonymous' }}</v-list-item-title>
                    <v-list-item-subtitle>
                      Answered on {{ formatDate(answer.answered_date) }}
                    </v-list-item-subtitle>
                    <v-list-item-content class="mt-2">
                      {{ answer.answer }}
                    </v-list-item-content>
                  </v-list-item>
                </v-list>
              </div>
              <div v-else class="text-center py-8">
                <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-help-circle</v-icon>
                <h3 class="text-h6 mb-2">No Answers Yet</h3>
                <p class="text-medium-emphasis">This prayer request hasn't been answered yet</p>
                <v-btn v-if="auth.is_admin" color="success" @click="openAnswerDialog" class="mt-4">
                  <v-icon left>mdi-check</v-icon>
                  Add Answer
                </v-btn>
              </div>
            </v-window-item>
          </v-window>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 pb-4">
          <v-btn color="primary" @click="prayForRequest(selectedRequest)" :disabled="selectedRequest.user_prayed">
            <v-icon left>mdi-hands-pray</v-icon>
            {{ selectedRequest.user_prayed ? 'You Prayed' : 'Pray' }}
          </v-btn>
          <v-spacer></v-spacer>
          <v-btn v-if="canEdit(selectedRequest)" color="warning" @click="editRequest(selectedRequest)">
            <v-icon left>mdi-pencil</v-icon>
            Edit
          </v-btn>
          <v-btn variant="text" @click="viewDialog = false">
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Answer Dialog -->
    <v-dialog v-model="answerDialog" max-width="500">
      <v-card>
        <v-card-title>Add Answer</v-card-title>
        <v-card-text>
          <v-form ref="answerForm">
            <v-textarea
              v-model="answerForm.answer"
              label="Answer *"
              variant="outlined"
              rows="4"
              :rules="[v => !!v || 'Answer is required']"
              required
              placeholder="Share how this prayer was answered..."
            ></v-textarea>
            <v-text-field
              v-model="answerForm.answered_date"
              label="Answered Date *"
              type="date"
              variant="outlined"
              :rules="[v => !!v || 'Date is required']"
              required
            ></v-text-field>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="answerDialog = false">
            Cancel
          </v-btn>
          <v-btn color="success" @click="saveAnswer" :loading="savingAnswer">
            Save Answer
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Statistics Dialog -->
    <v-dialog v-model="statsDialog" max-width="800">
      <v-card>
        <v-card-title>Prayer Request Statistics</v-card-title>
        <v-card-text>
          <v-row class="mb-4">
            <v-col cols="6" md="3" v-for="stat in detailedStats" :key="stat.title">
              <v-card variant="outlined" class="text-center pa-3">
                <div class="text-h4 font-weight-bold">{{ stat.value }}</div>
                <div class="text-caption text-medium-emphasis">{{ stat.title }}</div>
              </v-card>
            </v-col>
          </v-row>

          <v-divider class="my-4"></v-divider>

          <h4 class="text-subtitle-1 mb-2">Monthly Trend</h4>
          <v-list>
            <v-list-item v-for="month in monthlyStats" :key="month.month">
              <v-list-item-title>{{ month.month }}</v-list-item-title>
              <template #append>
                <div class="d-flex gap-4">
                  <span class="text-caption">{{ month.requests }} requests</span>
                  <span class="text-caption text-success">{{ month.answered }} answered</span>
                </div>
              </template>
            </v-list-item>
          </v-list>

          <v-divider class="my-4"></v-divider>

          <h4 class="text-subtitle-1 mb-2">Category Distribution</h4>
          <v-list>
            <v-list-item v-for="category in categoryStats" :key="category.category">
              <v-list-item-title>{{ category.category }}</v-list-item-title>
              <template #append>
                <v-chip size="small">{{ category.count }}</v-chip>
              </template>
            </v-list-item>
          </v-list>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="statsDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '../../stores/auth'
import axios from 'axios'

const toast = useToast()
const auth = useAuthStore()

// Data
const loading = ref(false)
const saving = ref(false)
const savingAnswer = ref(false)
const dialog = ref(false)
const viewDialog = ref(false)
const answerDialog = ref(false)
const statsDialog = ref(false)
const prayerRequests = ref([])
const urgentRequests = ref([])
const members = ref([])
const selectedRequest = ref(null)
const editingRequest = ref(null)
const viewTab = ref('request')
const search = ref('')
const categoryFilter = ref('')
const statusFilter = ref('')
const activeFilter = ref('')
const newComment = ref('')

// Form
const form = ref({
  title: '',
  request: '',
  category: '',
  priority: 'normal',
  privacy: 'church_only',
  member_id: auth.member_id,
  allow_prayers: true,
  allow_comments: true,
})

const answerForm = ref({
  answer: '',
  answered_date: new Date().toISOString().split('T')[0],
})

// Stats
const stats = ref([
  { title: 'Total Requests', value: 0, color: 'primary', icon: 'mdi-pray', filter: '' },
  { title: 'Pending', value: 0, color: 'warning', icon: 'mdi-clock', filter: 'pending' },
  { title: 'Answered', value: 0, color: 'success', icon: 'mdi-check', filter: 'answered' },
  { title: 'Urgent', value: 0, color: 'error', icon: 'mdi-alert', filter: 'urgent' },
])

const detailedStats = ref([])
const monthlyStats = ref([])
const categoryStats = ref([])

// Breadcrumbs
const breadcrumbs = ref([
  { title: 'Dashboard', to: '/' },
  { title: 'Prayer Requests', disabled: true }
])

// Quick filters
const quickFilters = ref([
  { label: 'All Requests', value: '' },
  { label: 'My Requests', value: 'my' },
  { label: 'Urgent', value: 'urgent' },
  { label: 'Answered', value: 'answered' },
  { label: 'Needs Prayer', value: 'needs_prayer' },
])

// Options
const categoryOptions = ref([
  { title: 'Healing', value: 'healing' },
  { title: 'Financial', value: 'financial' },
  { title: 'Guidance', value: 'guidance' },
  { title: 'Protection', value: 'protection' },
  { title: 'Thanksgiving', value: 'thanksgiving' },
  { title: 'Other', value: 'other' },
])

const priorityOptions = ref([
  { title: 'Normal', value: 'normal' },
  { title: 'Urgent', value: 'urgent' },
])

const privacyOptions = ref([
  { title: 'Public', value: 'public' },
  { title: 'Church Only', value: 'church_only' },
  { title: 'Private', value: 'private' },
])

const statusOptions = ref([
  { title: 'Pending', value: 'pending' },
  { title: 'Reviewing', value: 'reviewing' },
  { title: 'Praying', value: 'praying' },
  { title: 'Answered', value: 'answered' },
  { title: 'Closed', value: 'closed' },
])

// Form refs
const formRef = ref(null)
const answerFormRef = ref(null)

// Computed
const filteredRequests = computed(() => {
  let filtered = prayerRequests.value

  // Apply quick filters
  if (activeFilter.value === 'my') {
    filtered = filtered.filter(r => r.member_id === auth.member_id)
  } else if (activeFilter.value === 'urgent') {
    filtered = filtered.filter(r => r.priority === 'urgent')
  } else if (activeFilter.value === 'answered') {
    filtered = filtered.filter(r => r.is_answered)
  } else if (activeFilter.value === 'needs_prayer') {
    filtered = filtered.filter(r => !r.user_prayed && r.allow_prayers)
  }

  // Apply category filter
  if (categoryFilter.value) {
    filtered = filtered.filter(r => r.category === categoryFilter.value)
  }

  // Apply status filter
  if (statusFilter.value) {
    filtered = filtered.filter(r => r.status === statusFilter.value)
  }

  // Apply search
  if (search.value) {
    const searchTerm = search.value.toLowerCase()
    filtered = filtered.filter(r =>
      r.title.toLowerCase().includes(searchTerm) ||
      r.request.toLowerCase().includes(searchTerm) ||
      (r.member && (
        r.member.first_name.toLowerCase().includes(searchTerm) ||
        r.member.last_name.toLowerCase().includes(searchTerm)
      ))
    )
  }

  return filtered
})

// Methods
const fetchPrayerRequests = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')

    const response = await axios.get('/api/prayer-requests', {
      headers: { Authorization: `Bearer ${token}` },
      params: {
        category: categoryFilter.value,
        status: statusFilter.value,
        answered: statusFilter.value === 'answered' ? true : undefined,
      }
    })

    if (response.data.success) {
      prayerRequests.value = response.data.data.data || response.data.data

      // Update stats
      stats.value[0].value = response.data.stats.total
      stats.value[1].value = response.data.stats.pending
      stats.value[2].value = response.data.stats.answered
      stats.value[3].value = response.data.stats.urgent
    }
  } catch (error) {
    console.error('Error fetching prayer requests:', error)
    toast.error('Failed to load prayer requests')
  } finally {
    loading.value = false
  }
}

const fetchUrgentRequests = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/prayer-requests/urgent', {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      urgentRequests.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching urgent requests:', error)
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

const fetchStatistics = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/prayer-requests/statistics', {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      const data = response.data.data
      detailedStats.value = [
        { title: 'Total Requests', value: data.stats.total },
        { title: 'Total Prayers', value: data.stats.prayer_count },
        { title: 'Answered', value: data.stats.answered },
        { title: 'Urgent', value: data.stats.urgent },
      ]
      monthlyStats.value = data.monthly
      categoryStats.value = data.categories
    }
  } catch (error) {
    console.error('Error fetching statistics:', error)
  }
}

const openCreateDialog = () => {
  resetForm()
  editingRequest.value = null
  form.value.member_id = auth.member_id
  dialog.value = true
}

const editRequest = (request) => {
  editingRequest.value = request
  form.value = { ...request }
  dialog.value = true
}

const viewRequest = async (request) => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get(`/api/prayer-requests/${request.id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      selectedRequest.value = response.data.data
      viewTab.value = 'request'
      viewDialog.value = true
    }
  } catch (error) {
    console.error('Error fetching request details:', error)
    toast.error('Failed to load request details')
  }
}

const saveRequest = async () => {
  if (!formRef.value) return

  const { valid } = await formRef.value.validate()
  if (!valid) {
    toast.error('Please fill in all required fields')
    return
  }

  saving.value = true
  try {
    const token = localStorage.getItem('token')
    let response

    if (editingRequest.value) {
      response = await axios.put(`/api/prayer-requests/${editingRequest.value.id}`, form.value, {
        headers: { Authorization: `Bearer ${token}` }
      })
    } else {
      response = await axios.post('/api/prayer-requests', form.value, {
        headers: { Authorization: `Bearer ${token}` }
      })
    }

    if (response.data.success) {
      toast.success(response.data.message || 'Prayer request saved successfully')
      closeDialog()
      fetchPrayerRequests()
      fetchUrgentRequests()
    } else {
      toast.error(response.data.message || 'Failed to save prayer request')
    }
  } catch (error) {
    console.error('Error saving prayer request:', error)
    toast.error(error.response?.data?.message || 'Failed to save prayer request')
  } finally {
    saving.value = false
  }
}

const prayForRequest = async (request) => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.post(`/api/prayer-requests/${request.id}/pray`, {}, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      // Update the request in the list
      const index = prayerRequests.value.findIndex(r => r.id === request.id)
      if (index !== -1) {
        prayerRequests.value[index].prayer_count = response.data.data.prayer_count
        prayerRequests.value[index].user_prayed = response.data.data.user_prayed
      }

      // Update urgent requests if needed
      const urgentIndex = urgentRequests.value.findIndex(r => r.id === request.id)
      if (urgentIndex !== -1) {
        urgentRequests.value[urgentIndex].prayer_count = response.data.data.prayer_count
        urgentRequests.value[urgentIndex].user_prayed = response.data.data.user_prayed
      }

      // Update selected request if viewing
      if (selectedRequest.value && selectedRequest.value.id === request.id) {
        selectedRequest.value.prayer_count = response.data.data.prayer_count
        selectedRequest.value.user_prayed = response.data.data.user_prayed
      }

      toast.success('Thank you for praying!')
    }
  } catch (error) {
    console.error('Error praying for request:', error)
    toast.error('Failed to record prayer')
  }
}

const addComment = async () => {
  if (!newComment.value.trim()) {
    toast.error('Please enter a comment')
    return
  }

  try {
    const token = localStorage.getItem('token')
    const response = await axios.post(`/api/prayer-requests/${selectedRequest.value.id}/comment`, {
      comment: newComment.value
    }, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      toast.success('Comment added successfully')
      newComment.value = ''

      // Refresh the request to get updated comments
      await viewRequest(selectedRequest.value)
    }
  } catch (error) {
    console.error('Error adding comment:', error)
    toast.error('Failed to add comment')
  }
}

const openAnswerDialog = () => {
  answerForm.value = {
    answer: '',
    answered_date: new Date().toISOString().split('T')[0],
  }
  answerDialog.value = true
}

const saveAnswer = async () => {
  if (!answerFormRef.value) return

  const { valid } = await answerFormRef.value.validate()
  if (!valid) {
    toast.error('Please fill in all required fields')
    return
  }

  savingAnswer.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.post(`/api/prayer-requests/${selectedRequest.value.id}/answer`, answerForm.value, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      toast.success('Answer added successfully')
      answerDialog.value = false

      // Refresh the request
      await viewRequest(selectedRequest.value)

      // Refresh the list
      fetchPrayerRequests()
    }
  } catch (error) {
    console.error('Error adding answer:', error)
    toast.error('Failed to add answer')
  } finally {
    savingAnswer.value = false
  }
}

const exportRequests = async () => {
  try {
    const token = localStorage.getItem('token')

    // Get all requests for export
    const response = await axios.get('/api/prayer-requests/export', {
      headers: { Authorization: `Bearer ${token}` },
      responseType: 'blob'
    })

    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `prayer_requests_${new Date().toISOString().split('T')[0]}.csv`)
    document.body.appendChild(link)
    link.click()
    link.remove()

    toast.success('Prayer requests exported successfully')
  } catch (error) {
    console.error('Error exporting prayer requests:', error)
    toast.error('Failed to export prayer requests')
  }
}

const applyQuickFilter = (filter) => {
  activeFilter.value = filter.active ? '' : filter.value
  filter.active = !filter.active

  // Deactivate other filters
  quickFilters.value.forEach(f => {
    if (f.value !== filter.value) {
      f.active = false
    }
  })
}

const filterRequests = (filter) => {
  if (filter === '') {
    resetFilters()
  } else if (filter === 'urgent') {
    activeFilter.value = 'urgent'
    priorityFilter.value = 'urgent'
  } else if (filter === 'answered') {
    activeFilter.value = 'answered'
    statusFilter.value = 'answered'
  } else if (filter === 'pending') {
    activeFilter.value = 'pending'
    statusFilter.value = 'pending'
  }

  fetchPrayerRequests()
}

const clearFilter = () => {
  activeFilter.value = ''
  priorityFilter.value = ''
  statusFilter.value = ''
  resetFilters()
}

const resetFilters = () => {
  search.value = ''
  categoryFilter.value = ''
  statusFilter.value = ''
  activeFilter.value = ''

  quickFilters.value.forEach(f => f.active = false)
  fetchPrayerRequests()
}

const closeDialog = () => {
  dialog.value = false
  editingRequest.value = null
  resetForm()
}

const resetForm = () => {
  form.value = {
    title: '',
    request: '',
    category: '',
    priority: 'normal',
    privacy: 'church_only',
    member_id: auth.member_id,
    allow_prayers: true,
    allow_comments: true,
  }
}

const canEdit = (request) => {
  return auth.is_admin || request.created_by === auth.id || request.member_id === auth.member_id
}

// Utility Functions
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatRelativeDate = (dateString) => {
  if (!dateString) return 'N/A'

  const date = new Date(dateString)
  const now = new Date()
  const diff = now - date
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))

  if (days === 0) return 'today'
  if (days === 1) return 'yesterday'
  if (days < 7) return `${days} days ago`
  if (days < 30) return `${Math.floor(days / 7)} weeks ago`
  return `${Math.floor(days / 30)} months ago`
}

const getCategoryColor = (category) => {
  const colors = {
    healing: 'success',
    financial: 'warning',
    guidance: 'info',
    protection: 'primary',
    thanksgiving: 'purple',
    other: 'grey',
  }
  return colors[category] || 'grey'
}

const getPriorityColor = (priority) => {
  return priority === 'urgent' ? 'error' : 'primary'
}

const getAvatarColor = (person) => {
  if (!person) return 'grey'

  const colors = ['primary', 'secondary', 'success', 'error', 'warning', 'info', 'purple', 'pink', 'teal']
  const name = (person.first_name || person.name || '').toLowerCase()
  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  const index = Math.abs(hash) % colors.length
  return colors[index]
}

const getInitials = (person) => {
  if (!person) return '?'

  if (person.first_name && person.last_name) {
    const first = person.first_name[0] || ''
    const last = person.last_name[0] || ''
    return (first + last).toUpperCase() || '?'
  } else if (person.name) {
    const parts = person.name.split(' ')
    const first = parts[0]?.[0] || ''
    const last = parts[1]?.[0] || ''
    return (first + last).toUpperCase() || '?'
  }

  return '?'
}

const showStatistics = async () => {
  await fetchStatistics()
  statsDialog.value = true
}

// Debounced search
let searchTimeout = null
const debouncedFetchRequests = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchPrayerRequests()
  }, 500)
}

// Lifecycle
onMounted(() => {
  fetchPrayerRequests()
  fetchUrgentRequests()
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

.urgent-card {
  border-left: 4px solid var(--v-error-base);
}

.urgent-item {
  border-left: 3px solid var(--v-error-base);
  background-color: rgba(var(--v-error-base), 0.05);
}

.prayer-card {
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  height: 100%;
}

.prayer-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.answered-card {
  border-left: 4px solid var(--v-success-base);
  background-color: rgba(var(--v-success-base), 0.03);
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
