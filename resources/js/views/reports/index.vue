<template>
  <div>
    <!-- Page Header with Breadcrumbs -->
    <v-breadcrumbs class="mb-4" :items="breadcrumbs" divider=">"></v-breadcrumbs>

    <!-- Page Header -->
    <div class="d-flex justify-space-between align-center mb-6">
      <div>
        <h1 class="text-h4 font-weight-bold">Reports</h1>
        <p class="text-body-1 text-medium-emphasis mt-1">
          Generate and view church reports and analytics
        </p>
      </div>
      <div>
        <v-btn color="primary" prepend-icon="mdi-file-chart" @click="generateCustomReport">
          Generate Custom Report
        </v-btn>
      </div>
    </div>

    <!-- Report Cards -->
    <v-row class="mb-6">
      <v-col cols="12" md="4">
        <v-card class="report-card" @click="generateReport('members')">
          <v-card-text class="d-flex align-center">
            <v-avatar color="primary" size="56" class="mr-4">
              <v-icon size="32">mdi-account-group</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">Members Report</div>
              <div class="text-caption text-medium-emphasis">
                Detailed member information and statistics
              </div>
            </div>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn variant="text" color="primary" @click.stop="generateReport('members')">
              Generate
              <v-icon right>mdi-chevron-right</v-icon>
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card class="report-card" @click="generateReport('events')">
          <v-card-text class="d-flex align-center">
            <v-avatar color="success" size="56" class="mr-4">
              <v-icon size="32">mdi-calendar</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">Events Report</div>
              <div class="text-caption text-medium-emphasis">
                Event schedules and attendance analysis
              </div>
            </div>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn variant="text" color="success" @click.stop="generateReport('events')">
              Generate
              <v-icon right>mdi-chevron-right</v-icon>
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card class="report-card" @click="generateReport('attendance')">
          <v-card-text class="d-flex align-center">
            <v-avatar color="warning" size="56" class="mr-4">
              <v-icon size="32">mdi-chart-line</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">Attendance Report</div>
              <div class="text-caption text-medium-emphasis">
                Attendance trends and statistics
              </div>
            </div>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn variant="text" color="warning" @click.stop="generateReport('attendance')">
              Generate
              <v-icon right>mdi-chevron-right</v-icon>
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <!-- Quick Reports -->
    <v-card class="mb-6">
      <v-card-title>Quick Reports</v-card-title>
      <v-card-text>
        <v-row>
          <v-col cols="6" sm="4" md="3" v-for="report in quickReports" :key="report.title">
            <v-card class="text-center pa-4 quick-report-card" @click="generateQuickReport(report)">
              <v-icon size="48" :color="report.color" class="mb-2">{{ report.icon }}</v-icon>
              <div class="text-subtitle-1 font-weight-medium">{{ report.title }}</div>
              <div class="text-caption text-medium-emphasis">{{ report.description }}</div>
            </v-card>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Recent Reports -->
    <v-card>
      <v-card-title class="d-flex justify-space-between align-center">
        <span>Recent Reports</span>
        <v-btn variant="text" @click="refreshReports">
          <v-icon left>mdi-refresh</v-icon>
          Refresh
        </v-btn>
      </v-card-title>
      <v-card-text>
        <v-list v-if="recentReports.length > 0">
          <v-list-item v-for="report in recentReports" :key="report.id">
            <template #prepend>
              <v-avatar :color="getReportColor(report.type)" size="40">
                <v-icon color="white">{{ getReportIcon(report.type) }}</v-icon>
              </v-avatar>
            </template>
            <v-list-item-title>{{ report.title }}</v-list-item-title>
            <v-list-item-subtitle>
              Generated {{ formatRelativeDate(report.generated_at) }}
              • {{ report.format.toUpperCase() }} • {{ report.size }}
            </v-list-item-subtitle>
            <template #append>
              <div class="d-flex gap-1">
                <v-btn icon size="small" @click="downloadReport(report)">
                  <v-icon>mdi-download</v-icon>
                </v-btn>
                <v-btn icon size="small" @click="regenerateReport(report)">
                  <v-icon>mdi-refresh</v-icon>
                </v-btn>
              </div>
            </template>
          </v-list-item>
        </v-list>
        <div v-else class="text-center py-8">
          <v-icon size="48" color="grey" class="mb-2">mdi-file-document-outline</v-icon>
          <p class="text-medium-emphasis">No recent reports</p>
        </div>
      </v-card-text>
    </v-card>

    <!-- Report Generation Dialog -->
    <v-dialog v-model="reportDialog" max-width="600">
      <v-card>
        <v-card-title>{{ selectedReport ? selectedReport.title : 'Generate Report' }}</v-card-title>
        <v-card-text>
          <v-form ref="reportForm" v-model="reportFormValid">
            <v-row v-if="selectedReport">
              <v-col cols="12">
                <v-select
                  v-model="reportFormat"
                  :items="formats"
                  label="Format"
                  variant="outlined"
                  required
                ></v-select>
              </v-col>

              <!-- Members Report Filters -->
              <template v-if="selectedReport.type === 'members'">
                <v-col cols="12" md="6">
                  <v-select
                    v-model="filters.status"
                    :items="statusOptions"
                    label="Status"
                    variant="outlined"
                    clearable
                  ></v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-select
                    v-model="filters.gender"
                    :items="genderOptions"
                    label="Gender"
                    variant="outlined"
                    clearable
                  ></v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="filters.start_date"
                    label="Join Date From"
                    type="date"
                    variant="outlined"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="filters.end_date"
                    label="Join Date To"
                    type="date"
                    variant="outlined"
                  ></v-text-field>
                </v-col>
              </template>

              <!-- Events Report Filters -->
              <template v-if="selectedReport.type === 'events'">
                <v-col cols="12" md="6">
                  <v-select
                    v-model="filters.type"
                    :items="eventTypes"
                    label="Event Type"
                    variant="outlined"
                    clearable
                  ></v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-select
                    v-model="filters.status"
                    :items="eventStatusOptions"
                    label="Status"
                    variant="outlined"
                    clearable
                  ></v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="filters.start_date"
                    label="Start Date From"
                    type="date"
                    variant="outlined"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="filters.end_date"
                    label="Start Date To"
                    type="date"
                    variant="outlined"
                  ></v-text-field>
                </v-col>
              </template>

              <!-- Attendance Report Filters -->
              <template v-if="selectedReport.type === 'attendance'">
                <v-col cols="12">
                  <v-select
                    v-model="filters.event_id"
                    :items="events"
                    item-title="title"
                    item-value="id"
                    label="Event"
                    variant="outlined"
                    clearable
                  ></v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="filters.start_date"
                    label="Date From"
                    type="date"
                    variant="outlined"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="filters.end_date"
                    label="Date To"
                    type="date"
                    variant="outlined"
                  ></v-text-field>
                </v-col>
              </template>

              <v-col cols="12">
                <v-switch
                  v-model="includeCharts"
                  label="Include Charts"
                  color="primary"
                ></v-switch>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="reportDialog = false">
            Cancel
          </v-btn>
          <v-btn color="primary" @click="generateSelectedReport" :loading="generatingReport">
            Generate Report
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'

const toast = useToast()
const reportDialog = ref(false)
const reportFormValid = ref(false)
const generatingReport = ref(false)
const reportFormat = ref('pdf')
const includeCharts = ref(true)
const selectedReport = ref(null)
const events = ref([])

// Form ref
const reportForm = ref(null)

// Breadcrumbs
const breadcrumbs = ref([
  { title: 'Dashboard', to: '/' },
  { title: 'Reports', disabled: true }
])

// Data
const recentReports = ref([])

// Quick reports
const quickReports = ref([
  {
    title: 'Active Members',
    type: 'members',
    filters: { status: 'active' },
    icon: 'mdi-account-check',
    color: 'success',
    description: 'List of active members'
  },
  {
    title: 'Upcoming Events',
    type: 'events',
    filters: { status: 'upcoming' },
    icon: 'mdi-calendar-clock',
    color: 'warning',
    description: 'Events in the next 30 days'
  },
  {
    title: 'Monthly Attendance',
    type: 'attendance',
    filters: {
      start_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
      end_date: new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0).toISOString().split('T')[0]
    },
    icon: 'mdi-chart-bar',
    color: 'info',
    description: 'This month\'s attendance'
  },
  {
    title: 'New Members',
    type: 'members',
    filters: {
      start_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0]
    },
    icon: 'mdi-account-plus',
    color: 'primary',
    description: 'Members joined this month'
  },
])

// Filters
const filters = ref({
  status: '',
  gender: '',
  type: '',
  event_id: '',
  start_date: '',
  end_date: '',
})

// Options
const formats = ref([
  { title: 'PDF Document', value: 'pdf' },
  { title: 'Excel Spreadsheet', value: 'excel' },
  { title: 'CSV File', value: 'csv' },
])

const statusOptions = ref([
  'active',
  'inactive',
  'visitor',
  'pending',
])

const genderOptions = ref([
  'Male',
  'Female',
  'Other',
])

const eventTypes = ref([
  'service',
  'meeting',
  'outreach',
  'social',
  'youth',
  'children',
  'women',
  'men',
  'prayer',
  'bible_study',
  'training',
  'conference',
  'other',
])

const eventStatusOptions = ref([
  'upcoming',
  'past',
  'ongoing',
])

// Methods
const generateReport = (type) => {
  selectedReport.value = {
    type: type,
    title: type.charAt(0).toUpperCase() + type.slice(1) + ' Report',
  }

  // Reset filters
  filters.value = {
    status: '',
    gender: '',
    type: '',
    event_id: '',
    start_date: '',
    end_date: '',
  }

  reportDialog.value = true
}

const generateQuickReport = (report) => {
  selectedReport.value = report
  filters.value = { ...report.filters }
  reportDialog.value = true
}

const generateCustomReport = () => {
  // Open custom report builder
  toast.info('Custom report builder coming soon')
}

const generateSelectedReport = async () => {
  if (!reportForm.value) return

  const { valid } = await reportForm.value.validate()
  if (!valid) {
    toast.error('Please fill in all required fields')
    return
  }

  generatingReport.value = true

  try {
    const token = localStorage.getItem('token')
    const params = {
      type: selectedReport.value.type,
      format: reportFormat.value,
      ...filters.value
    }

    // Remove empty filters
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null || params[key] === undefined) {
        delete params[key]
      }
    })

    // Generate report
    const response = await axios.get('/api/reports/generate', {
      headers: { Authorization: `Bearer ${token}` },
      params: params,
      responseType: 'blob'
    })

    // Create download link
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url

    // Determine filename
    let filename = `${selectedReport.value.type}_report_${new Date().toISOString().split('T')[0]}`
    if (reportFormat.value === 'pdf') {
      filename += '.pdf'
    } else if (reportFormat.value === 'excel') {
      filename += '.xlsx'
    } else if (reportFormat.value === 'csv') {
      filename += '.csv'
    }

    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    link.remove()

    // Add to recent reports
    const newReport = {
      id: Date.now(),
      type: selectedReport.value.type,
      title: selectedReport.value.title,
      format: reportFormat.value,
      generated_at: new Date().toISOString(),
      size: formatFileSize(response.data.size),
      filters: { ...filters.value }
    }

    recentReports.value.unshift(newReport)

    // Keep only last 10 reports
    if (recentReports.value.length > 10) {
      recentReports.value = recentReports.value.slice(0, 10)
    }

    toast.success('Report generated successfully')
    reportDialog.value = false

  } catch (error) {
    console.error('Error generating report:', error)
    toast.error('Failed to generate report')
  } finally {
    generatingReport.value = false
  }
}

const downloadReport = async (report) => {
  try {
    const token = localStorage.getItem('token')

    // In a real app, you would fetch the actual report file
    // For now, we'll regenerate it
    const params = {
      type: report.type,
      format: report.format,
      ...report.filters
    }

    const response = await axios.get('/api/reports/generate', {
      headers: { Authorization: `Bearer ${token}` },
      params: params,
      responseType: 'blob'
    })

    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `${report.type}_report_${new Date().toISOString().split('T')[0]}.${report.format}`)
    document.body.appendChild(link)
    link.click()
    link.remove()

    toast.success('Report downloaded')
  } catch (error) {
    console.error('Error downloading report:', error)
    toast.error('Failed to download report')
  }
}

const regenerateReport = (report) => {
  selectedReport.value = report
  filters.value = { ...report.filters }
  reportFormat.value = report.format
  reportDialog.value = true
}

const refreshReports = () => {
  // Load recent reports from localStorage
  const savedReports = localStorage.getItem('recentReports')
  if (savedReports) {
    recentReports.value = JSON.parse(savedReports)
  }

  toast.success('Reports refreshed')
}

const loadEvents = async () => {
  try {
    const token = localStorage.getItem('token')

    const response = await axios.get('/api/events', {
      headers: { Authorization: `Bearer ${token}` },
      params: { per_page: 100 }
    })

    if (response.data.success) {
      events.value = response.data.data
    }
  } catch (error) {
    console.error('Error loading events:', error)
  }
}

const formatRelativeDate = (dateString) => {
  if (!dateString) return 'N/A'

  const date = new Date(dateString)
  const now = new Date()
  const diff = now - date
  const hours = Math.floor(diff / (1000 * 60 * 60))

  if (hours < 1) {
    return 'just now'
  } else if (hours < 24) {
    return `${hours} hour${hours > 1 ? 's' : ''} ago`
  } else {
    const days = Math.floor(hours / 24)
    return `${days} day${days > 1 ? 's' : ''} ago`
  }
}

const formatFileSize = (bytes) => {
  if (!bytes) return 'N/A'

  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const getReportColor = (type) => {
  const colors = {
    members: 'primary',
    events: 'success',
    attendance: 'warning',
    financial: 'error',
    summary: 'info',
  }
  return colors[type] || 'grey'
}

const getReportIcon = (type) => {
  const icons = {
    members: 'mdi-account-group',
    events: 'mdi-calendar',
    attendance: 'mdi-chart-line',
    financial: 'mdi-cash',
    summary: 'mdi-file-document',
  }
  return icons[type] || 'mdi-file-document-outline'
}

// Lifecycle
onMounted(() => {
  // Load saved reports from localStorage
  const savedReports = localStorage.getItem('recentReports')
  if (savedReports) {
    recentReports.value = JSON.parse(savedReports)
  }

  // Load events for filters
  loadEvents()
})

// Watch for changes in recent reports
import { watch } from 'vue'
watch(recentReports, (newVal) => {
  localStorage.setItem('recentReports', JSON.stringify(newVal))
}, { deep: true })
</script>

<style scoped>
.report-card {
  cursor: pointer;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  height: 100%;
}

.report-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.quick-report-card {
  cursor: pointer;
  transition: all 0.3s ease;
  height: 100%;
}

.quick-report-card:hover {
  background-color: rgba(var(--v-theme-primary), 0.05);
  transform: scale(1.05);
}

.v-list-item {
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  transition: background-color 0.2s ease;
}

.v-list-item:hover {
  background-color: rgba(0, 0, 0, 0.02);
}

.v-list-item:last-child {
  border-bottom: none;
}
</style>
