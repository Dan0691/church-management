<template>
  <div>
    <v-breadcrumbs :items="breadcrumbs" class="mb-4" divider=">"></v-breadcrumbs>

    <v-row>
      <v-col cols="12">
        <v-card>
          <v-card-title class="d-flex justify-space-between align-center">
            <div>
              <h2 class="text-h5">My Profile</h2>
              <p class="text-caption text-medium-emphasis mt-1">Manage your personal information</p>
            </div>
            <v-btn color="primary" @click="saveProfile" :loading="saving">
              Save Changes
            </v-btn>
          </v-card-title>

          <v-card-text>
            <v-form ref="profileForm">
              <v-row>
                <!-- Profile Photo -->
                <v-col cols="12" md="3" class="text-center">
                <v-avatar size="120" class="mb-4" :color="userAvatarColor">
                    <!-- Use auth.user directly -->
                    <v-img v-if="auth.user?.profile_photo_url" :src="auth.user.profile_photo_url" cover></v-img>
                    <span v-else class="text-h4 text-white">{{ userInitials }}</span>
                </v-avatar>
                <v-file-input
                    v-model="profilePhotoFile"
                    label="Change Photo"
                    prepend-icon="mdi-camera"
                    variant="outlined"
                    density="comfortable"
                    accept="image/*"
                    @change="handleProfilePhotoChange"
                    hide-details
                ></v-file-input>
                <v-btn
                    v-if="auth.user?.profile_photo"
                    color="error"
                    variant="text"
                    size="small"
                    @click="removeProfilePhoto"
                    class="mt-2"
                >
                    Remove Photo
                </v-btn>
                </v-col>

                <!-- Personal Information -->
                <v-col cols="12" md="9">
                  <v-row>
                    <v-col cols="12" md="6">
                      <v-text-field
                        v-model="form.name"
                        label="Full Name *"
                        variant="outlined"
                        density="comfortable"
                        :rules="[rules.required]"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                      <v-text-field
                        v-model="form.email"
                        label="Email Address *"
                        variant="outlined"
                        density="comfortable"
                        type="email"
                        :rules="[rules.required, rules.email]"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                      <v-text-field
                        v-model="form.phone"
                        label="Phone Number"
                        variant="outlined"
                        density="comfortable"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                      <v-text-field
                        v-model="form.occupation"
                        label="Occupation"
                        variant="outlined"
                        density="comfortable"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                      <v-text-field
                        v-model="form.date_of_birth"
                        label="Date of Birth"
                        variant="outlined"
                        density="comfortable"
                        type="date"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                      <v-select
                        v-model="form.gender"
                        label="Gender"
                        variant="outlined"
                        density="comfortable"
                        :items="['Male', 'Female', 'Other']"
                        clearable
                      ></v-select>
                    </v-col>
                    <v-col cols="12" md="6">
                      <v-select
                        v-model="form.marital_status"
                        label="Marital Status"
                        variant="outlined"
                        density="comfortable"
                        :items="['Single', 'Married', 'Divorced', 'Widowed', 'Separated']"
                        clearable
                      ></v-select>
                    </v-col>
                    <v-col cols="12">
                      <v-textarea
                        v-model="form.bio"
                        label="Bio/About Me"
                        variant="outlined"
                        density="comfortable"
                        rows="3"
                      ></v-textarea>
                    </v-col>
                  </v-row>
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Address Information -->
    <v-row class="mt-4">
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title class="text-h6">Address Information</v-card-title>
          <v-card-text>
            <v-form ref="addressForm">
              <v-text-field
                v-model="form.address"
                label="Street Address"
                variant="outlined"
                density="comfortable"
                class="mb-3"
              ></v-text-field>
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.city"
                    label="City"
                    variant="outlined"
                    density="comfortable"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.state"
                    label="State/Province"
                    variant="outlined"
                    density="comfortable"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.country"
                    label="Country"
                    variant="outlined"
                    density="comfortable"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.postal_code"
                    label="ZIP/Postal Code"
                    variant="outlined"
                    density="comfortable"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Emergency Contact & Change Password -->
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title class="text-h6">Emergency Contact</v-card-title>
          <v-card-text>
            <v-form ref="emergencyForm">
              <v-text-field
                v-model="form.emergency_contact_name"
                label="Emergency Contact Name"
                variant="outlined"
                density="comfortable"
                class="mb-3"
              ></v-text-field>
              <v-text-field
                v-model="form.emergency_contact_phone"
                label="Emergency Contact Phone"
                variant="outlined"
                density="comfortable"
              ></v-text-field>
            </v-form>
          </v-card-text>
        </v-card>

        <v-card class="mt-4">
          <v-card-title class="text-h6">Change Password</v-card-title>
          <v-card-text>
            <v-form ref="passwordForm">
              <v-text-field
                v-model="password.current_password"
                label="Current Password"
                variant="outlined"
                density="comfortable"
                :append-inner-icon="showCurrentPassword ? 'mdi-eye' : 'mdi-eye-off'"
                :type="showCurrentPassword ? 'text' : 'password'"
                @click:append-inner="showCurrentPassword = !showCurrentPassword"
                class="mb-3"
              ></v-text-field>
              <v-text-field
                v-model="password.new_password"
                label="New Password"
                variant="outlined"
                density="comfortable"
                :append-inner-icon="showNewPassword ? 'mdi-eye' : 'mdi-eye-off'"
                :type="showNewPassword ? 'text' : 'password'"
                @click:append-inner="showNewPassword = !showNewPassword"
                :rules="[rules.minLength(8)]"
                class="mb-3"
              ></v-text-field>
              <v-text-field
                v-model="password.confirm_password"
                label="Confirm New Password"
                variant="outlined"
                density="comfortable"
                :append-inner-icon="showConfirmPassword ? 'mdi-eye' : 'mdi-eye-off'"
                :type="showConfirmPassword ? 'text' : 'password'"
                @click:append-inner="showConfirmPassword = !showConfirmPassword"
                :rules="[passwordMatch]"
              ></v-text-field>
            </v-form>
            <v-btn
              color="primary"
              variant="outlined"
              @click="changePassword"
              :loading="changingPassword"
              class="mt-2"
            >
              Update Password
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { useToast } from 'vue-toastification'
import axios from 'axios'

const auth = useAuthStore()
const toast = useToast()

// Refs
const breadcrumbs = ref([
  { title: 'Dashboard', to: '/' },
  { title: 'Profile', disabled: true }
])

const form = ref({
  name: '',
  email: '',
  phone: '',
  profile_photo: null,
  profile_photo_url: null,
  address: '',
  city: '',
  state: '',
  country: '',
  postal_code: '',
  bio: '',
  date_of_birth: '',
  gender: '',
  marital_status: '',
  occupation: '',
  emergency_contact_name: '',
  emergency_contact_phone: ''
})

const password = ref({
  current_password: '',
  new_password: '',
  confirm_password: ''
})

const profilePhotoFile = ref(null)
const saving = ref(false)
const changingPassword = ref(false)
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

// Forms
const profileForm = ref(null)
const addressForm = ref(null)
const emergencyForm = ref(null)
const passwordForm = ref(null)

// Rules
const rules = {
  required: value => !!value || 'This field is required',
  email: value => {
    const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    return pattern.test(value) || 'Invalid email format'
  },
  minLength: min => value => !value || value.length >= min || `Minimum ${min} characters`
}

// Computed
const userInitials = computed(() => {
  if (!auth.user?.name) return 'U'
  return auth.user.name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const userAvatarColor = computed(() => {
  const colors = ['primary', 'secondary', 'success', 'error', 'warning', 'info']
  const name = auth.user?.name || 'User'
  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  return colors[Math.abs(hash) % colors.length]
})

const passwordMatch = computed(() => {
  return value => value === password.value.new_password || 'Passwords do not match'
})

// Methods
// Methods
const loadUserProfile = () => {
  if (auth.user) {
    console.log('Loading user profile:', auth.user);
    console.log('Profile photo URL:', auth.user.profile_photo_url);

    form.value = {
      name: auth.user.name || '',
      email: auth.user.email || '',
      phone: auth.user.phone || '',
      profile_photo: auth.user.profile_photo || null,
      profile_photo_url: auth.user.profile_photo_url || null,
      address: auth.user.address || '',
      city: auth.user.city || '',
      state: auth.user.state || '',
      country: auth.user.country || '',
      postal_code: auth.user.postal_code || '',
      bio: auth.user.bio || '',
      date_of_birth: auth.user.date_of_birth ? auth.user.date_of_birth.split('T')[0] : '',
      gender: auth.user.gender || '',
      marital_status: auth.user.marital_status || '',
      occupation: auth.user.occupation || '',
      emergency_contact_name: auth.user.emergency_contact_name || '',
      emergency_contact_phone: auth.user.emergency_contact_phone || ''
    };

    console.log('Form loaded:', form.value);
  }
}

const handleProfilePhotoChange = async () => {
  if (!profilePhotoFile.value) return

  const file = profilePhotoFile.value[0]
  if (!file) return

  console.log('Uploading file:', file.name, file.size, file.type);

  // Validate file
  const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/webp']
  if (!validTypes.includes(file.type)) {
    toast.error('Please upload a valid image (JPEG, PNG, GIF, WebP)')
    profilePhotoFile.value = null
    return
  }

  if (file.size > 2 * 1024 * 1024) { // 2MB
    toast.error('Image size should be less than 2MB')
    profilePhotoFile.value = null
    return
  }

  const formData = new FormData()
  formData.append('profile_photo', file)

  console.log('Sending upload request...');

  try {
    const response = await axios.post('/api/user/upload-photo', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    console.log('Upload response:', response.data);

    if (response.data.success) {
      // Update auth store with the updated user data
      if (response.data.user) {
        auth.setUser(response.data.user)
      }

      // Also update the form
      form.value.profile_photo = response.data.path
      form.value.profile_photo_url = response.data.url

      toast.success('Profile photo updated successfully')

      // Clear the file input
      profilePhotoFile.value = null

      // Force a refresh
      await auth.fetchUser()
    } else {
      toast.error(response.data.message || 'Failed to upload profile photo')
    }
  } catch (error) {
    console.error('Error uploading photo:', error)
    console.error('Error details:', error.response)

    if (error.response?.status === 422) {
      const errors = error.response.data.errors
      if (errors.profile_photo) {
        toast.error(errors.profile_photo[0])
      }
    } else if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Failed to upload profile photo')
    }

    profilePhotoFile.value = null
  }
}

const removeProfilePhoto = async () => {
  try {
    const response = await axios.delete('/api/user/remove-photo')

    if (response.data.success) {
      // Update form
      form.value.profile_photo = null
      form.value.profile_photo_url = null
      profilePhotoFile.value = null

      // Update auth store with the updated user data
      if (response.data.user) {
        auth.setUser(response.data.user)
      } else {
        // Fallback: manually update the user object
        auth.user.profile_photo = null
        auth.user.profile_photo_url = null
        auth.setUser(auth.user) // This will update localStorage
      }

      toast.success('Profile photo removed successfully')
    } else {
      toast.error(response.data.message || 'Failed to remove profile photo')
    }
  } catch (error) {
    console.error('Error removing photo:', error)

    if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Failed to remove profile photo')
    }
  }
}

const saveProfile = async () => {
  const isValid = await profileForm.value.validate()
  if (!isValid.valid) {
    toast.error('Please fix the form errors')
    return
  }

  saving.value = true
  try {
    // Remove profile_photo_url from payload (it's a computed property)
    const payload = { ...form.value }
    delete payload.profile_photo_url

    const response = await axios.put('/api/user/profile', payload)

    if (response.data.success) {
      toast.success('Profile updated successfully')

      // Update auth store with the updated user data
      if (response.data.user) {
        auth.setUser(response.data.user)
      }

      // Reload the form data
      loadUserProfile()
    } else {
      toast.error(response.data.message || 'Failed to update profile')
    }
  } catch (error) {
    console.error('Error updating profile:', error)
    if (error.response?.status === 422) {
      const errors = error.response.data.errors
      Object.keys(errors).forEach(key => {
        toast.error(`${key}: ${errors[key][0]}`)
      })
    } else {
      toast.error(error.response?.data?.message || 'Failed to update profile')
    }
  } finally {
    saving.value = false
  }
}

const changePassword = async () => {
  const isValid = await passwordForm.value.validate()
  if (!isValid.valid) {
    toast.error('Please fix the password form errors')
    return
  }

  if (password.value.new_password !== password.value.confirm_password) {
    toast.error('New password and confirmation do not match')
    return
  }

  changingPassword.value = true
  try {
    const response = await axios.put('/api/user/password', {
      current_password: password.value.current_password,
      password: password.value.new_password,
      password_confirmation: password.value.confirm_password
    })

    if (response.data.success) {
      toast.success('Password changed successfully')
      password.value = {
        current_password: '',
        new_password: '',
        confirm_password: ''
      }
    } else {
      toast.error(response.data.message || 'Failed to change password')
    }
  } catch (error) {
    console.error('Error changing password:', error)
    if (error.response?.status === 422) {
      const errors = error.response.data.errors
      Object.keys(errors).forEach(key => {
        toast.error(`${key}: ${errors[key][0]}`)
      })
    } else {
      toast.error(error.response?.data?.message || 'Failed to change password')
    }
  } finally {
    changingPassword.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadUserProfile()
})
</script>
