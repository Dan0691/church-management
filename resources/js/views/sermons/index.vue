<template>
  <div>
    <!-- Breadcrumbs -->
    <v-breadcrumbs class="mb-4" :items="breadcrumbs" divider=">"></v-breadcrumbs>

    <!-- Page Header -->
    <div class="d-flex justify-space-between align-center mb-6">
      <div>
        <h1 class="text-h4 font-weight-bold">Sermons</h1>
        <p class="text-body-1 text-medium-emphasis mt-1">
          Church teaching and sermon library ({{ sermons.length }} sermons)
        </p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <v-btn color="primary" prepend-icon="mdi-microphone-plus" @click="openCreateDialog">
          Add Sermon
        </v-btn>
        <v-btn variant="outlined" prepend-icon="mdi-cloud-upload" @click="uploadDialog = true">
          Bulk Upload
        </v-btn>
      </div>
    </div>

    <!-- Stats Cards -->
    <v-row class="mb-6">
      <v-col cols="6" md="3" v-for="stat in stats" :key="stat.title">
        <v-card class="stats-card">
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

    <!-- Search and Filters -->
    <v-card class="mb-6">
      <v-card-text>
        <v-row>
          <v-col cols="12" md="6">
            <v-text-field
              v-model="searchQuery"
              placeholder="Search sermons by title, speaker, or scripture..."
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="comfortable"
              hide-details
              @keyup.enter="searchSermons"
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

          <v-col cols="12" md="2">
            <v-select
              v-model="selectedSeries"
              :items="seriesOptions"
              label="Series"
              variant="outlined"
              density="comfortable"
              clearable
              hide-details
            ></v-select>
          </v-col>

          <v-col cols="12" md="2">
            <v-select
              v-model="selectedSpeaker"
              :items="speakerOptions"
              label="Speaker"
              variant="outlined"
              density="comfortable"
              clearable
              hide-details
            ></v-select>
          </v-col>

          <v-col cols="12" md="2">
            <v-select
              v-model="selectedYear"
              :items="yearOptions"
              label="Year"
              variant="outlined"
              density="comfortable"
              clearable
              hide-details
            ></v-select>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Sermons Grid/List -->
    <v-row v-if="loading">
      <v-col cols="12" sm="6" md="4" v-for="n in 6" :key="n">
        <v-skeleton-loader type="card"></v-skeleton-loader>
      </v-col>
    </v-row>

    <div v-else-if="filteredSermons.length === 0" class="text-center py-12">
      <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-microphone-off</v-icon>
      <h3 class="text-h6 mb-2">No sermons found</h3>
      <p class="text-medium-emphasis mb-4">
        {{ hasFilters ? 'Try changing your filters' : 'Add your first sermon to get started' }}
      </p>
      <v-btn color="primary" @click="openCreateDialog">
        Add First Sermon
      </v-btn>
    </div>

    <v-row v-else>
      <v-col cols="12" sm="6" md="4" v-for="sermon in filteredSermons" :key="sermon.id">
        <v-card class="sermon-card" @click="viewSermon(sermon)">
          <v-img
            v-if="getSermonThumbnail(sermon)"
            :src="getSermonThumbnail(sermon)"
            height="200"
            cover
          >
            <template #placeholder>
              <div class="d-flex align-center justify-center fill-height">
                <v-icon size="64" color="primary">mdi-book-open-variant</v-icon>
              </div>
            </template>
          </v-img>
          <div v-else class="sermon-thumbnail-placeholder">
            <v-icon size="64" color="primary">mdi-book-open-variant</v-icon>
          </div>

          <v-card-text class="pa-4">
            <div class="d-flex justify-space-between align-start mb-2">
              <div>
                <h3 class="text-h6 font-weight-bold">{{ sermon.title }}</h3>
                <div class="text-caption text-medium-emphasis">{{ sermon.speaker }}</div>
              </div>
              <v-chip size="small" color="primary" v-if="sermon.series">
                {{ sermon.series }}
              </v-chip>
            </div>

            <p class="text-body-2 text-medium-emphasis mb-3">
              {{ truncateText(sermon.description, 100) }}
            </p>

            <div class="sermon-meta">
              <div class="d-flex justify-space-between text-caption">
                <div class="d-flex align-center">
                  <v-icon size="16" class="mr-1">mdi-calendar</v-icon>
                  {{ formatDate(sermon.sermon_date) }}
                </div>
                <div class="d-flex align-center" v-if="sermon.duration">
                  <v-icon size="16" class="mr-1">mdi-clock</v-icon>
                  {{ formatDuration(sermon.duration) }}
                </div>
              </div>

              <div v-if="sermon.scripture_reference" class="mt-2">
                <v-chip size="x-small" color="primary" variant="outlined">
                  {{ sermon.scripture_reference }}
                </v-chip>
              </div>

              <v-divider class="my-3"></v-divider>

              <div class="d-flex justify-space-between">
                <div class="d-flex align-center">
                  <v-icon size="16" class="mr-1" color="primary">mdi-eye</v-icon>
                  {{ sermon.views || 0 }}
                </div>
                <div class="d-flex align-center">
                  <v-icon size="16" class="mr-1" color="primary">mdi-download</v-icon>
                  {{ sermon.downloads || 0 }}
                </div>
                <div class="d-flex align-center">
                  <v-icon size="16" class="mr-1" :color="sermon.audio_url ? 'success' : 'grey'">
                    mdi-music-note
                  </v-icon>
                </div>
                <div class="d-flex align-center">
                  <v-icon size="16" class="mr-1" :color="sermon.video_url ? 'success' : 'grey'">
                    mdi-video
                  </v-icon>
                </div>
              </div>
            </div>
          </v-card-text>

          <v-card-actions class="pa-3">
            <v-btn size="small" variant="text" @click.stop="viewSermon(sermon)">
              <v-icon left size="18">mdi-eye</v-icon>
              View
            </v-btn>
            <v-spacer></v-spacer>
            <v-btn icon size="small" @click.stop="playAudio(sermon)" v-if="sermon.audio_url">
              <v-icon size="18">mdi-play</v-icon>
            </v-btn>
            <v-btn icon size="small" @click.stop="downloadSermon(sermon)">
              <v-icon size="18">mdi-download</v-icon>
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <!-- View Sermon Dialog -->
    <v-dialog v-model="viewDialog" max-width="900" scrollable>
      <v-card v-if="selectedSermon">
        <v-card-title class="d-flex justify-space-between align-center">
          <div class="d-flex align-center">
            <v-avatar color="primary" size="48" class="mr-3">
              <v-icon color="white">mdi-book-open-variant</v-icon>
            </v-avatar>
            <div>
              <h2 class="text-h5">{{ selectedSermon.title }}</h2>
              <div class="d-flex align-center mt-1">
                <span class="text-body-1">{{ selectedSermon.speaker }}</span>
                <v-chip size="small" class="ml-2" v-if="selectedSermon.series">
                  {{ selectedSermon.series }}
                </v-chip>
              </div>
            </div>
          </div>
          <v-btn icon @click="viewDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-divider></v-divider>

        <v-card-text class="pa-4">
          <v-row>
            <v-col cols="12" lg="8">
              <!-- Media Player -->
              <div v-if="selectedSermon.video_url" class="mb-6">
                <video
                  :src="selectedSermon.video_url"
                  controls
                  class="sermon-video"
                  @play="incrementView"
                ></video>
              </div>
              <div v-else-if="selectedSermon.audio_url" class="mb-6">
                <audio
                  :src="selectedSermon.audio_url"
                  controls
                  class="sermon-audio"
                  @play="incrementView"
                ></audio>
              </div>
              <div v-else class="text-center py-8 mb-6 no-media">
                <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-multimedia</v-icon>
                <p class="text-medium-emphasis">No media available for this sermon</p>
              </div>

              <!-- Sermon Details -->
              <div class="sermon-details">
                <h3 class="text-h6 mb-3">Sermon Details</h3>
                <div class="text-body-1 white-space-pre mb-4">{{ selectedSermon.description }}</div>

                <v-row>
                  <v-col cols="6" md="3">
                    <div class="info-item">
                      <div class="text-caption text-medium-emphasis">Date</div>
                      <div class="text-body-1">{{ formatDate(selectedSermon.sermon_date) }}</div>
                    </div>
                  </v-col>
                  <v-col cols="6" md="3">
                    <div class="info-item">
                      <div class="text-caption text-medium-emphasis">Duration</div>
                      <div class="text-body-1">{{ formatDuration(selectedSermon.duration) }}</div>
                    </div>
                  </v-col>
                  <v-col cols="6" md="3">
                    <div class="info-item">
                      <div class="text-caption text-medium-emphasis">Views</div>
                      <div class="text-body-1">{{ selectedSermon.views || 0 }}</div>
                    </div>
                  </v-col>
                  <v-col cols="6" md="3">
                    <div class="info-item">
                      <div class="text-caption text-medium-emphasis">Downloads</div>
                      <div class="text-body-1">{{ selectedSermon.downloads || 0 }}</div>
                    </div>
                  </v-col>
                </v-row>

                <div v-if="selectedSermon.scripture_reference" class="mt-4">
                  <div class="text-caption text-medium-emphasis">Scripture Reference</div>
                  <v-chip color="primary" class="mt-1">
                    {{ selectedSermon.scripture_reference }}
                  </v-chip>
                </div>
              </div>
            </v-col>

            <v-col cols="12" lg="4">
              <!-- Resources -->
              <v-card variant="outlined" class="mb-4">
                <v-card-title class="text-subtitle-1">Resources</v-card-title>
                <v-card-text>
                  <v-list density="compact">
                    <v-list-item
                      v-if="selectedSermon.audio_url"
                      @click="playAudio(selectedSermon)"
                      class="cursor-pointer"
                    >
                      <template #prepend>
                        <v-icon color="success">mdi-music-note</v-icon>
                      </template>
                      <v-list-item-title>Audio</v-list-item-title>
                      <v-list-item-subtitle>Listen to sermon</v-list-item-subtitle>
                    </v-list-item>

                    <v-list-item
                      v-if="selectedSermon.video_url"
                      @click="playVideo(selectedSermon)"
                      class="cursor-pointer"
                    >
                      <template #prepend>
                        <v-icon color="error">mdi-video</v-icon>
                      </template>
                      <v-list-item-title>Video</v-list-item-title>
                      <v-list-item-subtitle>Watch sermon</v-list-item-subtitle>
                    </v-list-item>

                    <v-list-item
                      v-if="selectedSermon.slides_url"
                      @click="downloadFile(selectedSermon.slides_url, 'slides')"
                      class="cursor-pointer"
                    >
                      <template #prepend>
                        <v-icon color="warning">mdi-presentation</v-icon>
                      </template>
                      <v-list-item-title>Slides</v-list-item-title>
                      <v-list-item-subtitle>Download presentation</v-list-item-subtitle>
                    </v-list-item>

                    <v-list-item
                      v-if="selectedSermon.notes_url"
                      @click="downloadFile(selectedSermon.notes_url, 'notes')"
                      class="cursor-pointer"
                    >
                      <template #prepend>
                        <v-icon color="info">mdi-file-document</v-icon>
                      </template>
                      <v-list-item-title>Notes</v-list-item-title>
                      <v-list-item-subtitle>Download study notes</v-list-item-subtitle>
                    </v-list-item>
                  </v-list>
                </v-card-text>
              </v-card>

              <!-- Quick Stats -->
              <v-card variant="outlined">
                <v-card-title class="text-subtitle-1">Sermon Statistics</v-card-title>
                <v-card-text>
                  <div class="d-flex justify-space-between mb-2">
                    <span>Views:</span>
                    <strong>{{ selectedSermon.views || 0 }}</strong>
                  </div>
                  <div class="d-flex justify-space-between mb-2">
                    <span>Downloads:</span>
                    <strong>{{ selectedSermon.downloads || 0 }}</strong>
                  </div>
                  <div class="d-flex justify-space-between mb-2">
                    <span>Added:</span>
                    <span>{{ formatDate(selectedSermon.created_at) }}</span>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="pa-4">
          <v-btn color="primary" @click="editSermon(selectedSermon)">
            <v-icon left>mdi-pencil</v-icon>
            Edit Sermon
          </v-btn>
          <v-btn color="success" @click="downloadAll(selectedSermon)">
            <v-icon left>mdi-download</v-icon>
            Download All
          </v-btn>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="viewDialog = false">
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Create/Edit Dialog -->
    <v-dialog v-model="dialog" max-width="800" scrollable>
      <v-card>
        <v-card-title class="d-flex justify-space-between align-center">
          <span class="text-h5">{{ editingSermon ? 'Edit Sermon' : 'Add New Sermon' }}</span>
          <v-btn icon @click="closeDialog">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text>
          <v-form ref="form" @submit.prevent="saveSermon">
            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="form.title"
                  label="Sermon Title *"
                  variant="outlined"
                  :rules="[v => !!v || 'Title is required']"
                  required
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="form.description"
                  label="Description"
                  variant="outlined"
                  rows="3"
                  placeholder="Sermon summary or key points..."
                ></v-textarea>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.speaker"
                  label="Speaker *"
                  variant="outlined"
                  :rules="[v => !!v || 'Speaker is required']"
                  required
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.sermon_date"
                  label="Sermon Date *"
                  type="date"
                  variant="outlined"
                  :rules="[v => !!v || 'Date is required']"
                  required
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.scripture_reference"
                  label="Scripture Reference"
                  variant="outlined"
                  placeholder="e.g., John 3:16"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.series"
                  label="Series"
                  variant="outlined"
                  placeholder="e.g., Fruit of the Spirit"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model.number="form.duration"
                  label="Duration (minutes)"
                  type="number"
                  variant="outlined"
                  min="1"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-divider class="my-2"></v-divider>
                <h3 class="text-h6 mb-3">Media Files</h3>
              </v-col>

              <v-col cols="12" md="6">
                <v-file-input
                  v-model="form.audio_file"
                  label="Audio File"
                  accept="audio/*"
                  variant="outlined"
                  prepend-icon="mdi-music-note"
                  clearable
                ></v-file-input>
                <v-text-field
                  v-if="!form.audio_file"
                  v-model="form.audio_url"
                  label="Or Audio URL"
                  variant="outlined"
                  placeholder="https://example.com/audio.mp3"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-file-input
                  v-model="form.video_file"
                  label="Video File"
                  accept="video/*"
                  variant="outlined"
                  prepend-icon="mdi-video"
                  clearable
                ></v-file-input>
                <v-text-field
                  v-if="!form.video_file"
                  v-model="form.video_url"
                  label="Or Video URL"
                  variant="outlined"
                  placeholder="https://example.com/video.mp4"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-file-input
                  v-model="form.slides_file"
                  label="Slides File"
                  accept=".pdf,.ppt,.pptx"
                  variant="outlined"
                  prepend-icon="mdi-presentation"
                  clearable
                ></v-file-input>
                <v-text-field
                  v-if="!form.slides_file"
                  v-model="form.slides_url"
                  label="Or Slides URL"
                  variant="outlined"
                  placeholder="https://example.com/slides.pdf"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-file-input
                  v-model="form.notes_file"
                  label="Notes File"
                  accept=".pdf,.doc,.docx,.txt"
                  variant="outlined"
                  prepend-icon="mdi-file-document"
                  clearable
                ></v-file-input>
                <v-text-field
                  v-if="!form.notes_file"
                  v-model="form.notes_url"
                  label="Or Notes URL"
                  variant="outlined"
                  placeholder="https://example.com/notes.pdf"
                ></v-text-field>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>

        <v-card-actions class="px-4 pb-4">
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="closeDialog">
            Cancel
          </v-btn>
          <v-btn color="primary" @click="saveSermon" :loading="saving">
            {{ editingSermon ? 'Update' : 'Save Sermon' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Bulk Upload Dialog -->
    <v-dialog v-model="uploadDialog" max-width="500">
      <v-card>
        <v-card-title>Bulk Upload Sermons</v-card-title>
        <v-card-text>
          <v-alert type="info" class="mb-4">
            Upload a CSV file with sermon information. Download the template first.
          </v-alert>

          <v-file-input
            v-model="uploadFile"
            label="Choose CSV file"
            accept=".csv"
            variant="outlined"
            prepend-icon="mdi-file"
          ></v-file-input>

          <v-btn
            variant="text"
            color="primary"
            @click="downloadTemplate"
            class="mt-2"
          >
            <v-icon left>mdi-download</v-icon>
            Download Template
          </v-btn>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="uploadDialog = false">
            Cancel
          </v-btn>
          <v-btn color="primary" @click="processUpload" :loading="uploading" :disabled="!uploadFile">
            Upload
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

const toast = useToast()

// Data
const loading = ref(false)
const saving = ref(false)
const uploading = ref(false)
const dialog = ref(false)
const viewDialog = ref(false)
const uploadDialog = ref(false)
const sermons = ref([])
const selectedSermon = ref(null)
const editingSermon = ref(null)
const uploadFile = ref(null)
const searchQuery = ref('')
const selectedSeries = ref('')
const selectedSpeaker = ref('')
const selectedYear = ref('')
const seriesOptions = ref([])
const speakerOptions = ref([])
const yearOptions = ref([])
const stats = ref([])


// Form
const form = ref({
  title: '',
  description: '',
  speaker: '',
  sermon_date: new Date().toISOString().split('T')[0],
  scripture_reference: '',
  series: '',
  duration: null,
  audio_file: null,
  audio_url: '',
  video_file: null,
  video_url: '',
  slides_file: null,
  slides_url: '',
  notes_file: null,
  notes_url: '',
})

// Breadcrumbs
const breadcrumbs = ref([
  { title: 'Dashboard', to: '/' },
  { title: 'Sermons', disabled: true }
])

// Computed
const filteredSermons = computed(() => {
  let filtered = sermons.value

  if (selectedSeries.value) {
    filtered = filtered.filter(s => s.series === selectedSeries.value)
  }

  if (selectedSpeaker.value) {
    filtered = filtered.filter(s => s.speaker === selectedSpeaker.value)
  }

  if (selectedYear.value) {
    filtered = filtered.filter(s => {
      const year = new Date(s.sermon_date).getFullYear()
      return year.toString() === selectedYear.value
    })
  }

  return filtered
})

const hasFilters = computed(() => {
  return selectedSeries.value || selectedSpeaker.value || selectedYear.value || searchQuery.value
})

// Methods
const fetchSermons = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/sermons', {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      sermons.value = response.data.data.data || response.data.data
      seriesOptions.value = response.data.filters?.series || []
      speakerOptions.value = response.data.filters?.speakers || []
      yearOptions.value = response.data.filters?.years || []
    }
  } catch (error) {
    console.error('Error fetching sermons:', error)
    toast.error('Failed to load sermons')
  } finally {
    loading.value = false
  }
}

const fetchStatistics = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/sermons/statistics', {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
     stats.value = [
  {
    title: 'Total Sermons',
    value: response.data.data.total_sermons || 0,
    icon: 'mdi-book-open-variant',
    color: 'primary'
  },
  {
    title: 'Total Views',
    value: response.data.data.total_views || 0,
    icon: 'mdi-eye',
    color: 'success'
  },
  {
    title: 'Total Downloads',
    value: response.data.data.total_downloads || 0,
    icon: 'mdi-download',
    color: 'warning'
  },
  {
    title: 'Avg Duration',
    value: `${Math.round(response.data.data.average_duration || 0)} min`,
    icon: 'mdi-clock',
    color: 'info'
  }
]

    }
  } catch (error) {
    console.error('Error fetching statistics:', error)
  }
}

const openCreateDialog = () => {
  resetForm()
  editingSermon.value = null
  dialog.value = true
}

const editSermon = (sermon) => {
  editingSermon.value = sermon
  form.value = { ...sermon }
  dialog.value = true
  viewDialog.value = false
}

const viewSermon = async (sermon) => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get(`/api/sermons/${sermon.id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      selectedSermon.value = response.data.data
      viewDialog.value = true
    }
  } catch (error) {
    console.error('Error fetching sermon details:', error)
    toast.error('Failed to load sermon details')
  }
}

const saveSermon = async () => {
  saving.value = true
  try {
    const token = localStorage.getItem('token')

    // Create FormData for file uploads
    const formData = new FormData()
    Object.keys(form.value).forEach(key => {
      if (form.value[key] !== null && form.value[key] !== undefined) {
        formData.append(key, form.value[key])
      }
    })

    let response
    if (editingSermon.value) {
      response = await axios.post(`/api/sermons/${editingSermon.value.id}?_method=PUT`, formData, {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'multipart/form-data'
        }
      })
    } else {
      response = await axios.post('/api/sermons', formData, {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'multipart/form-data'
        }
      })
    }

    if (response.data.success) {
      toast.success(response.data.message || 'Sermon saved successfully')
      closeDialog()
      fetchSermons()
      fetchStatistics()
    } else {
      toast.error(response.data.message || 'Failed to save sermon')
    }
  } catch (error) {
    console.error('Error saving sermon:', error)
    toast.error(error.response?.data?.message || 'Failed to save sermon')
  } finally {
    saving.value = false
  }
}

const deleteSermon = async (sermon) => {
  if (!confirm(`Are you sure you want to delete "${sermon.title}"?`)) {
    return
  }

  try {
    const token = localStorage.getItem('token')
    const response = await axios.delete(`/api/sermons/${sermon.id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (response.data.success) {
      toast.success('Sermon deleted successfully')
      fetchSermons()
      fetchStatistics()
    }
  } catch (error) {
    console.error('Error deleting sermon:', error)
    toast.error('Failed to delete sermon')
  }
}

const playAudio = (sermon) => {
  if (sermon.audio_url) {
    const audio = new Audio(sermon.audio_url)
    audio.play()
    incrementView(sermon)
  } else {
    toast.info('No audio available for this sermon')
  }
}

const playVideo = (sermon) => {
  if (sermon.video_url) {
    window.open(sermon.video_url, '_blank')
    incrementView(sermon)
  } else {
    toast.info('No video available for this sermon')
  }
}

const downloadSermon = async (sermon) => {
  try {
    const token = localStorage.getItem('token')
    await axios.post(`/api/sermons/${sermon.id}/download`, {}, {
      headers: { Authorization: `Bearer ${token}` }
    })

    // Get the main file (audio or video or notes)
    const fileUrl = sermon.audio_url || sermon.video_url || sermon.notes_url || sermon.slides_url
    if (fileUrl) {
      downloadFile(fileUrl, sermon.title)
      toast.success('Download recorded')
    } else {
      toast.info('No files available for download')
    }
  } catch (error) {
    console.error('Error downloading sermon:', error)
    toast.error('Failed to record download')
  }
}

const downloadAll = (sermon) => {
  const files = [
    { url: sermon.audio_url, name: 'audio' },
    { url: sermon.video_url, name: 'video' },
    { url: sermon.slides_url, name: 'slides' },
    { url: sermon.notes_url, name: 'notes' },
  ].filter(f => f.url)

  files.forEach((file, index) => {
    setTimeout(() => {
      downloadFile(file.url, `${sermon.title}_${file.name}`)
    }, index * 1000)
  })

  toast.success(`Started download of ${files.length} files`)
}

const downloadFile = (url, name) => {
  const link = document.createElement('a')
  link.href = url
  link.download = name
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

const incrementView = async (sermon) => {
  try {
    const token = localStorage.getItem('token')
    await axios.post(`/api/sermons/${sermon.id}/view`, {}, {
      headers: { Authorization: `Bearer ${token}` }
    })
    // Update local view count
    sermon.views = (sermon.views || 0) + 1
  } catch (error) {
    console.error('Error incrementing view:', error)
  }
}

const searchSermons = async () => {
  if (!searchQuery.value.trim()) return

  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/sermons/search', {
      headers: { Authorization: `Bearer ${token}` },
      params: { query: searchQuery.value }
    })

    if (response.data.success) {
      sermons.value = response.data.data
    }
  } catch (error) {
    console.error('Error searching sermons:', error)
  } finally {
    loading.value = false
  }
}

const clearSearch = () => {
  searchQuery.value = ''
  fetchSermons()
}

const downloadTemplate = () => {
  const template = `Title,Speaker,Date,Scripture,Series,Duration,Description
"God's Love for Us","Pastor John","2024-01-07","John 3:16","Foundations",45,"Understanding God's unconditional love"
"Walking in Faith","Elder Sarah","2024-01-14","Hebrews 11:1","Foundations",50,"Learning to walk by faith and not by sight"`

  const blob = new Blob([template], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = 'sermon_upload_template.csv'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

const processUpload = async () => {
  if (!uploadFile.value) return

  uploading.value = true
  try {
    const token = localStorage.getItem('token')
    const formData = new FormData()
    formData.append('file', uploadFile.value)

    const response = await axios.post('/api/sermons/upload', formData, {
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'multipart/form-data'
      }
    })

    if (response.data.success) {
      toast.success('Sermons uploaded successfully')
      uploadDialog.value = false
      uploadFile.value = null
      fetchSermons()
    }
  } catch (error) {
    console.error('Error uploading sermons:', error)
    toast.error('Failed to upload sermons')
  } finally {
    uploading.value = false
  }
}

const closeDialog = () => {
  dialog.value = false
  editingSermon.value = null
  resetForm()
}

const resetForm = () => {
  form.value = {
    title: '',
    description: '',
    speaker: '',
    sermon_date: new Date().toISOString().split('T')[0],
    scripture_reference: '',
    series: '',
    duration: null,
    audio_file: null,
    audio_url: '',
    video_file: null,
    video_url: '',
    slides_file: null,
    slides_url: '',
    notes_file: null,
    notes_url: '',
  }
}

// Utility Functions
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US')
}

const formatDuration = (minutes) => {
  if (!minutes) return 'N/A'
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60
  if (hours > 0) {
    return `${hours}h ${mins}m`
  }
  return `${mins}m`
}

const truncateText = (text, length) => {
  if (!text) return ''
  if (text.length <= length) return text
  return text.substring(0, length) + '...'
}

const getSermonThumbnail = (sermon) => {
  // You can implement thumbnail generation here
  // For now, return null or a default image
  return null
}

// Debounced search
let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    if (searchQuery.value) {
      searchSermons()
    }
  }, 500)
}

// Watch filters
watch([selectedSeries, selectedSpeaker, selectedYear], () => {
  // Filtering is done computed, so no need to fetch again
})

// Lifecycle
onMounted(() => {
  fetchSermons()
  fetchStatistics()
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

.sermon-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  height: 100%;
}

.sermon-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.sermon-thumbnail-placeholder {
  height: 200px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.sermon-meta {
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  padding-top: 12px;
}

.sermon-video {
  width: 100%;
  max-height: 400px;
  background: #000;
  border-radius: 8px;
}

.sermon-audio {
  width: 100%;
}

.no-media {
  background: rgba(0, 0, 0, 0.02);
  border-radius: 8px;
  border: 2px dashed rgba(0, 0, 0, 0.1);
}

.info-item {
  margin-bottom: 12px;
}

.info-item:last-child {
  margin-bottom: 0;
}

.white-space-pre {
  white-space: pre-wrap;
  word-wrap: break-word;
}

.cursor-pointer {
  cursor: pointer;
}
</style>
