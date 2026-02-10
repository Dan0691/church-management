<template>
  <div>
    <v-breadcrumbs :items="breadcrumbs" class="mb-4" divider=">"></v-breadcrumbs>

    <v-card class="mb-6">
      <v-card-title class="d-flex justify-space-between align-center">
        <div>
          <h2 class="text-h5">Settings</h2>
          <p class="text-caption text-medium-emphasis mt-1">Configure your church management system</p>
        </div>
        <div>
          <v-btn color="primary" @click="saveAllSettings" :loading="saving">
            Save All Changes
          </v-btn>
          <v-btn
            color="error"
            variant="outlined"
            @click="confirmResetDialog = true"
            class="ml-2"
          >
            Reset to Default
          </v-btn>
        </div>
      </v-card-title>
    </v-card>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
      <p class="mt-4 text-medium-emphasis">Loading settings...</p>
    </div>

    <!-- Settings Content -->
    <template v-else>
      <v-tabs v-model="activeTab" color="primary" class="mb-6">
        <v-tab v-for="tab in tabs" :key="tab.value" :value="tab.value">
          <v-icon left>{{ tab.icon }}</v-icon>
          {{ tab.label }}
        </v-tab>
      </v-tabs>

      <v-window v-model="activeTab">
        <!-- General Settings -->
        <v-window-item value="general">
          <v-card>
            <v-card-title>General Settings</v-card-title>
            <v-card-text>
              <v-form ref="generalForm">
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.general.app_name"
                      label="Application Name"
                      variant="outlined"
                      density="comfortable"
                      hint="Name displayed throughout the system"
                      persistent-hint
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="settings.general.app_timezone"
                      label="Timezone"
                      variant="outlined"
                      density="comfortable"
                      :items="timezoneOptions"
                      hint="Default timezone for the application"
                      persistent-hint
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="settings.general.app_locale"
                      label="Language"
                      variant="outlined"
                      density="comfortable"
                      :items="localeOptions"
                      hint="Default language for the application"
                      persistent-hint
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="settings.general.date_format"
                      label="Date Format"
                      variant="outlined"
                      density="comfortable"
                      :items="dateFormatOptions"
                      hint="Default date display format"
                      persistent-hint
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="settings.general.time_format"
                      label="Time Format"
                      variant="outlined"
                      density="comfortable"
                      :items="timeFormatOptions"
                      hint="Default time display format"
                      persistent-hint
                    ></v-select>
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>
          </v-card>
        </v-window-item>

        <!-- Church Information -->
        <v-window-item value="church">
          <v-card>
            <v-card-title>Church Information</v-card-title>
            <v-card-text>
              <v-form ref="churchForm">
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="churchInfo.name"
                      label="Church Name"
                      variant="outlined"
                      density="comfortable"
                      :rules="[rules.required]"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="churchInfo.email"
                      label="Church Email"
                      variant="outlined"
                      density="comfortable"
                      type="email"
                      :rules="[rules.email]"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="churchInfo.phone"
                      label="Church Phone"
                      variant="outlined"
                      density="comfortable"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="churchInfo.website"
                      label="Website"
                      variant="outlined"
                      density="comfortable"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-textarea
                      v-model="churchInfo.address"
                      label="Address"
                      variant="outlined"
                      density="comfortable"
                      rows="2"
                    ></v-textarea>
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="churchInfo.city"
                      label="City"
                      variant="outlined"
                      density="comfortable"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="churchInfo.state"
                      label="State/Province"
                      variant="outlined"
                      density="comfortable"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="churchInfo.country"
                      label="Country"
                      variant="outlined"
                      density="comfortable"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="churchInfo.pastor_name"
                      label="Pastor's Name"
                      variant="outlined"
                      density="comfortable"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="churchInfo.pastor_email"
                      label="Pastor's Email"
                      variant="outlined"
                      density="comfortable"
                      type="email"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-textarea
                      v-model="churchInfo.about"
                      label="About Church"
                      variant="outlined"
                      density="comfortable"
                      rows="3"
                    ></v-textarea>
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>
            <v-card-actions>
              <v-btn color="primary" @click="saveChurchInfo" :loading="savingChurch">
                Update Church Info
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-window-item>

        <!-- Services -->
        <v-window-item value="services">
          <v-card>
            <v-card-title>Service Settings</v-card-title>
            <v-card-text>
              <v-form ref="servicesForm">
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.services.sunday_service_time"
                      label="Sunday Service Time"
                      variant="outlined"
                      density="comfortable"
                      type="time"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.services.midweek_service_time"
                      label="Midweek Service Time"
                      variant="outlined"
                      density="comfortable"
                      type="time"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.services.service_duration"
                      label="Service Duration (minutes)"
                      variant="outlined"
                      density="comfortable"
                      type="number"
                      min="30"
                      max="240"
                    ></v-text-field>
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>
          </v-card>
        </v-window-item>

        <!-- Members -->
        <v-window-item value="members">
          <v-card>
            <v-card-title>Member Settings</v-card-title>
            <v-card-text>
              <v-form ref="membersForm">
                <v-row>
                  <v-col cols="12">
                    <v-switch
                      v-model="settings.members.allow_member_registration"
                      label="Allow Member Self-Registration"
                      color="primary"
                      hide-details
                    ></v-switch>
                  </v-col>
                  <v-col cols="12">
                    <v-switch
                      v-model="settings.members.member_auto_approval"
                      label="Auto-Approve New Members"
                      color="primary"
                      hide-details
                    ></v-switch>
                  </v-col>
                  <v-col cols="12">
                    <v-switch
                      v-model="settings.members.require_member_approval"
                      label="Require Admin Approval"
                      color="primary"
                      hide-details
                    ></v-switch>
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>
          </v-card>
        </v-window-item>

        <!-- Financial -->
        <v-window-item value="financial">
          <v-card>
            <v-card-title>Financial Settings</v-card-title>
            <v-card-text>
              <v-form ref="financialForm">
                <v-row>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="settings.financial.currency"
                      label="Currency"
                      variant="outlined"
                      density="comfortable"
                      :items="currencyOptions"
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.financial.tax_rate"
                      label="Tax Rate (%)"
                      variant="outlined"
                      density="comfortable"
                      type="number"
                      min="0"
                      max="100"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-switch
                      v-model="settings.financial.donation_receipt_auto"
                      label="Auto-Generate Donation Receipts"
                      color="primary"
                      hide-details
                    ></v-switch>
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>
          </v-card>
        </v-window-item>

        <!-- Notifications -->
        <v-window-item value="notifications">
          <v-card>
            <v-card-title>Notification Settings</v-card-title>
            <v-card-text>
              <v-form ref="notificationsForm">
                <v-row>
                  <v-col cols="12">
                    <v-switch
                      v-model="settings.notifications.email_notifications"
                      label="Enable Email Notifications"
                      color="primary"
                      hide-details
                    ></v-switch>
                  </v-col>
                  <v-col cols="12">
                    <v-switch
                      v-model="settings.notifications.sms_notifications"
                      label="Enable SMS Notifications"
                      color="primary"
                      hide-details
                    ></v-switch>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.notifications.event_reminder_days"
                      label="Event Reminder (Days Before)"
                      variant="outlined"
                      density="comfortable"
                      type="number"
                      min="0"
                      max="30"
                    ></v-text-field>
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>
          </v-card>
        </v-window-item>

        <!-- Security -->
        <v-window-item value="security">
          <v-card>
            <v-card-title>Security Settings</v-card-title>
            <v-card-text>
              <v-form ref="securityForm">
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.security.session_timeout"
                      label="Session Timeout (minutes)"
                      variant="outlined"
                      density="comfortable"
                      type="number"
                      min="5"
                      max="240"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="settings.security.password_expiry_days"
                      label="Password Expiry (days)"
                      variant="outlined"
                      density="comfortable"
                      type="number"
                      min="0"
                      max="365"
                      hint="0 = never expire"
                      persistent-hint
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-switch
                      v-model="settings.security.two_factor_auth"
                      label="Enable Two-Factor Authentication"
                      color="primary"
                      hide-details
                    ></v-switch>
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>
          </v-card>
        </v-window-item>

        <!-- Appearance -->
        <v-window-item value="appearance">
          <v-card>
            <v-card-title>Appearance Settings</v-card-title>
            <v-card-text>
              <v-form ref="appearanceForm">
                <v-row>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="settings.appearance.theme_color"
                      label="Theme Color"
                      variant="outlined"
                      density="comfortable"
                      :items="themeColorOptions"
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-switch
                      v-model="settings.appearance.dark_mode"
                      label="Dark Mode"
                      color="primary"
                      hide-details
                    ></v-switch>
                  </v-col>
                  <v-col cols="12">
                    <v-switch
                      v-model="settings.appearance.show_church_logo"
                      label="Show Church Logo in Header"
                      color="primary"
                      hide-details
                    ></v-switch>
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>
          </v-card>
        </v-window-item>
      </v-window>
    </template>

    <!-- Reset Confirmation Dialog -->
    <v-dialog v-model="confirmResetDialog" max-width="500">
      <v-card>
        <v-card-title>Reset Settings</v-card-title>
        <v-card-text>
          Are you sure you want to reset all settings to default values?
          <v-alert type="warning" class="mt-4">
            This action cannot be undone. All custom settings will be lost.
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="confirmResetDialog = false">
            Cancel
          </v-btn>
          <v-btn color="error" @click="resetToDefault" :loading="resetting">
            Reset to Default
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { useToast } from 'vue-toastification'
import axios from 'axios'

const auth = useAuthStore()
const toast = useToast()

// State
const breadcrumbs = ref([
  { title: 'Dashboard', to: '/' },
  { title: 'Settings', disabled: true }
])

const activeTab = ref('general')
const loading = ref(false)
const saving = ref(false)
const savingChurch = ref(false)
const resetting = ref(false)
const confirmResetDialog = ref(false)

// Tabs
const tabs = ref([
  { label: 'General', value: 'general', icon: 'mdi-cog' },
  { label: 'Church Info', value: 'church', icon: 'mdi-church' },
  { label: 'Services', value: 'services', icon: 'mdi-calendar-clock' },
  { label: 'Members', value: 'members', icon: 'mdi-account-group' },
  { label: 'Financial', value: 'financial', icon: 'mdi-cash' },
  { label: 'Notifications', value: 'notifications', icon: 'mdi-bell' },
  { label: 'Security', value: 'security', icon: 'mdi-shield' },
  { label: 'Appearance', value: 'appearance', icon: 'mdi-palette' }
])

// Settings structure
const settings = reactive({
  general: {
    app_name: '',
    app_timezone: 'UTC',
    app_locale: 'en',
    date_format: 'Y-m-d',
    time_format: '24'
  },
  services: {
    sunday_service_time: '09:00',
    midweek_service_time: '19:00',
    service_duration: '120'
  },
  members: {
    allow_member_registration: true,
    member_auto_approval: false,
    require_member_approval: true
  },
  financial: {
    currency: 'NGN',
    tax_rate: '0',
    donation_receipt_auto: true
  },
  notifications: {
    email_notifications: true,
    sms_notifications: false,
    event_reminder_days: '1'
  },
  security: {
    session_timeout: '30',
    password_expiry_days: '90',
    two_factor_auth: false
  },
  appearance: {
    theme_color: 'primary',
    dark_mode: false,
    show_church_logo: true
  }
})

// Church info
const churchInfo = reactive({
  name: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  state: '',
  country: '',
  website: '',
  pastor_name: '',
  pastor_email: '',
  about: ''
})

// Options
const timezoneOptions = ref(['UTC', 'Africa/Lagos', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'Europe/London', 'Europe/Paris', 'Asia/Tokyo', 'Australia/Sydney'])
const localeOptions = ref([
  { title: 'English', value: 'en' },
  { title: 'Spanish', value: 'es' },
  { title: 'French', value: 'fr' },
  { title: 'German', value: 'de' }
])
const dateFormatOptions = ref([
  { title: 'YYYY-MM-DD', value: 'Y-m-d' },
  { title: 'DD/MM/YYYY', value: 'd/m/Y' },
  { title: 'MM/DD/YYYY', value: 'm/d/Y' },
  { title: 'DD-MMM-YYYY', value: 'd-M-Y' }
])
const timeFormatOptions = ref([
  { title: '24 Hour', value: '24' },
  { title: '12 Hour', value: '12' }
])
const currencyOptions = ref(['USD', 'EUR', 'GBP', 'NGN'])
const themeColorOptions = ref([
  { title: 'Blue', value: 'primary' },
  { title: 'Green', value: 'success' },
  { title: 'Orange', value: 'warning' },
  { title: 'Red', value: 'error' },
  { title: 'Teal', value: 'info' },
  { title: 'Gray', value: 'secondary' }
])

// Rules
const rules = {
  required: value => !!value || 'This field is required',
  email: value => {
    const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    return pattern.test(value) || 'Invalid email format'
  }
}

// Methods
const fetchSettings = async () => {
  loading.value = true
  try {
    console.log('Fetching settings...')
    
    const response = await axios.get('/api/settings')
    console.log('Settings API response:', response.data)

    if (response.data.success) {
      const settingsData = response.data.settings
      const churchData = response.data.church || {}

      console.log('Settings data:', settingsData)
      console.log('Church data:', churchData)

      // Map settings to structure
      if (settingsData) {
        Object.keys(settingsData).forEach(category => {
          if (settings[category]) {
            settingsData[category].forEach(setting => {
              if (settings[category].hasOwnProperty(setting.key)) {
                // Convert boolean strings to actual booleans
                if (setting.type === 'boolean') {
                  settings[category][setting.key] = setting.value === 'true' || setting.value === '1'
                } else {
                  settings[category][setting.key] = setting.value
                }
              }
            })
          }
        })
      }

      // Load church info from church data
      if (churchData) {
        Object.keys(churchInfo).forEach(key => {
          if (churchData.hasOwnProperty(key)) {
            churchInfo[key] = churchData[key] || ''
          }
        })
      }
      
      console.log('Final church info:', churchInfo)
    }
  } catch (error) {
    console.error('Error fetching settings:', error)
    console.error('Error response:', error.response)
    toast.error('Failed to load settings: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const saveAllSettings = async () => {
  saving.value = true
  try {
    // First, save church info if it has changed
    await saveChurchInfo();
    
    // Then save all other settings
    const settingsArray = []

    Object.keys(settings).forEach(category => {
      Object.keys(settings[category]).forEach(key => {
        // Convert boolean values to string for backend
        let value = settings[category][key];
        if (typeof value === 'boolean') {
          value = value ? '1' : '0';
        }
        
        settingsArray.push({
          key: key,
          value: value.toString(),
          type: getSettingType(category, key),
          category: category
        })
      })
    })

    console.log('Saving all settings:', settingsArray);

    const response = await axios.post('/api/settings', {
      settings: settingsArray
    })

    if (response.data.success) {
      toast.success('All settings saved successfully')
    } else {
      toast.error(response.data.message || 'Failed to save settings')
    }
  } catch (error) {
    console.error('Error saving settings:', error)
    if (error.response?.status === 422) {
      const errors = error.response.data.errors
      Object.keys(errors).forEach(key => {
        toast.error(`${key}: ${errors[key][0]}`)
      })
    } else {
      toast.error(error.response?.data?.message || 'Failed to save settings')
    }
  } finally {
    saving.value = false
  }
}

const saveChurchInfo = async () => {
  savingChurch.value = true
  try {
    console.log('Saving church info:', churchInfo)
    
    // Get current church ID from auth store
    const churchId = auth.user?.church_id
    
    if (!churchId) {
      toast.error('No church ID found')
      return
    }

    // Call the church update endpoint
    const response = await axios.put(`/api/churches/${churchId}`, churchInfo)

    console.log('Church info save response:', response.data)

    if (response.data.success) {
      // Update the church in the auth store
      auth.setChurch(response.data.church)
      
      // Also update user's church name if it's stored there
      if (auth.user && response.data.church) {
        auth.setUser({
          ...auth.user,
          church_name: response.data.church.name
        })
      }
      
      toast.success('Church information saved successfully')
      
      // Refresh settings to get updated values
      await fetchSettings()
    } else {
      toast.error(response.data.message || 'Failed to save church information')
    }
  } catch (error) {
    console.error('Error saving church info:', error)
    console.error('Error details:', error.response?.data)
    
    if (error.response?.status === 422) {
      const errors = error.response.data.errors
      Object.keys(errors).forEach(key => {
        toast.error(`${key}: ${errors[key][0]}`)
      })
    } else {
      toast.error(error.response?.data?.message || 'Failed to save church information')
    }
  } finally {
    savingChurch.value = false
  }
}

const resetToDefault = async () => {
  resetting.value = true
  try {
    const token = localStorage.getItem('token')

    const response = await axios.post('/api/settings/reset', {}, {
      headers: { 'Authorization': `Bearer ${token}` }
    })

    if (response.data.success) {
      toast.success('Settings reset to default successfully')
      confirmResetDialog.value = false
      await fetchSettings() // Reload settings
    } else {
      toast.error(response.data.message || 'Failed to reset settings')
    }
  } catch (error) {
    console.error('Error resetting settings:', error)
    toast.error(error.response?.data?.message || 'Failed to reset settings')
  } finally {
    resetting.value = false
  }
}

const getSettingType = (category, key) => {
  // This is a simplified type mapping
  const typeMap = {
    'general': {
      'app_name': 'text',
      'app_timezone': 'select',
      'app_locale': 'select',
      'date_format': 'select',
      'time_format': 'select'
    },
    'services': {
      'sunday_service_time': 'time',
      'midweek_service_time': 'time',
      'service_duration': 'number'
    },
    'members': {
      'allow_member_registration': 'boolean',
      'member_auto_approval': 'boolean',
      'require_member_approval': 'boolean'
    },
    'financial': {
      'currency': 'select',
      'tax_rate': 'number',
      'donation_receipt_auto': 'boolean'
    },
    'notifications': {
      'email_notifications': 'boolean',
      'sms_notifications': 'boolean',
      'event_reminder_days': 'number'
    },
    'security': {
      'session_timeout': 'number',
      'password_expiry_days': 'number',
      'two_factor_auth': 'boolean'
    },
    'appearance': {
      'theme_color': 'select',
      'dark_mode': 'boolean',
      'show_church_logo': 'boolean'
    }
  }

  return typeMap[category]?.[key] || 'text'
}

// Lifecycle
onMounted(() => {
  fetchSettings()
})
</script>
