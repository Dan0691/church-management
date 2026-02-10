<template>
  <v-app>
    <v-main class="register-background">
      <v-container class="fill-height" fluid>
        <v-row align="center" justify="center">
          <v-col cols="12" xl="6" lg="8" md="10" sm="12">
            <v-card class="register-card elevation-10 rounded-lg">
              <!-- Progress Steps -->
              <div class="progress-steps">
                <div class="step active">
                  <div class="step-number">1</div>
                  <span class="step-label">Church Details</span>
                </div>
                <div class="step-divider"></div>
                <div class="step">
                  <div class="step-number">2</div>
                  <span class="step-label">Admin Account</span>
                </div>
              </div>

              <div class="register-header">
                <div class="d-flex align-center justify-center mb-4">
                  <v-avatar color="primary" size="64" class="mr-3">
                    <v-icon icon="mdi-church" size="32"></v-icon>
                  </v-avatar>
                  <div>
                    <h1 class="text-h4 font-weight-bold gradient-text">Start Your Church Journey</h1>
                    <p class="text-subtitle-1 text-medium-emphasis">Get your church management setup in minutes</p>
                  </div>
                </div>
              </div>

              <v-card-text class="px-8 pb-8 pt-2">
                <v-form @submit.prevent="register" ref="registerForm" class="register-form">
                  <!-- Church Information Section -->
                  <v-card class="section-card mb-6" variant="flat">
                    <v-card-title class="section-title">
                      <v-icon icon="mdi-church" color="primary" class="mr-3"></v-icon>
                      Church Information
                      <span class="text-caption text-medium-emphasis ml-2">(Required)</span>
                    </v-card-title>
                    <v-card-text>
                      <v-row>
                        <v-col cols="12" md="6">
                          <div class="input-container">
                            <label class="input-label">
                              <v-icon icon="mdi-domain" size="16" class="mr-1"></v-icon>
                              Church Name
                            </label>
                            <v-text-field
                              v-model="form.church_name"
                              placeholder="Enter your church name"
                              prepend-inner-icon="mdi-home-city-outline"
                              type="text"
                              variant="outlined"
                              density="comfortable"
                              :error-messages="errors.church_name"
                              :rules="[rules.required]"
                              hide-details="auto"
                              class="rounded-lg"
                              bg-color="background"
                            ></v-text-field>
                          </div>
                        </v-col>
                        <v-col cols="12" md="6">
                          <div class="input-container">
                            <label class="input-label">
                              <v-icon icon="mdi-account-tie" size="16" class="mr-1"></v-icon>
                              Pastor's Name
                            </label>
                            <v-text-field
                              v-model="form.pastor_name"
                              placeholder="Enter pastor's full name"
                              prepend-inner-icon="mdi-account-outline"
                              type="text"
                              variant="outlined"
                              density="comfortable"
                              :error-messages="errors.pastor_name"
                              :rules="[rules.required]"
                              hide-details="auto"
                              class="rounded-lg"
                              bg-color="background"
                            ></v-text-field>
                          </div>
                        </v-col>
                      </v-row>

                      <div class="input-container mt-4">
                        <label class="input-label">
                          <v-icon icon="mdi-email" size="16" class="mr-1"></v-icon>
                          Church Email
                        </label>
                        <v-text-field
                          v-model="form.church_email"
                          placeholder="church@example.com"
                          prepend-inner-icon="mdi-email-outline"
                          type="email"
                          variant="outlined"
                          density="comfortable"
                          :error-messages="errors.church_email"
                          :rules="[rules.required, rules.email]"
                          hide-details="auto"
                          class="rounded-lg"
                          bg-color="background"
                        ></v-text-field>
                      </div>
                    </v-card-text>
                  </v-card>

                  <!-- Admin Information Section -->
                  <v-card class="section-card mb-6" variant="flat">
                    <v-card-title class="section-title">
                      <v-icon icon="mdi-account-cog" color="primary" class="mr-3"></v-icon>
                      Administrator Account
                      <span class="text-caption text-medium-emphasis ml-2">(You'll use this to login)</span>
                    </v-card-title>
                    <v-card-text>
                      <v-row>
                        <v-col cols="12" md="6">
                          <div class="input-container">
                            <label class="input-label">
                              <v-icon icon="mdi-account" size="16" class="mr-1"></v-icon>
                              Your Full Name
                            </label>
                            <v-text-field
                              v-model="form.name"
                              placeholder="Enter your full name"
                              prepend-inner-icon="mdi-account-circle-outline"
                              type="text"
                              variant="outlined"
                              density="comfortable"
                              :error-messages="errors.name"
                              :rules="[rules.required]"
                              hide-details="auto"
                              class="rounded-lg"
                              bg-color="background"
                            ></v-text-field>
                          </div>
                        </v-col>
                        <v-col cols="12" md="6">
                          <div class="input-container">
                            <label class="input-label">
                              <v-icon icon="mdi-phone" size="16" class="mr-1"></v-icon>
                              Phone Number
                            </label>
                            <v-text-field
                              v-model="form.phone"
                              placeholder="+234 800 123 4567"
                              prepend-inner-icon="mdi-phone-outline"
                              type="tel"
                              variant="outlined"
                              density="comfortable"
                              :error-messages="errors.phone"
                              :rules="[rules.required, rules.phone]"
                              hide-details="auto"
                              class="rounded-lg"
                              bg-color="background"
                            ></v-text-field>
                          </div>
                        </v-col>
                      </v-row>

                      <div class="input-container mt-4">
                        <label class="input-label">
                          <v-icon icon="mdi-email" size="16" class="mr-1"></v-icon>
                          Your Email Address
                        </label>
                        <v-text-field
                          v-model="form.email"
                          placeholder="you@example.com"
                          prepend-inner-icon="mdi-email-outline"
                          type="email"
                          variant="outlined"
                          density="comfortable"
                          :error-messages="errors.email"
                          :rules="[rules.required, rules.email]"
                          hide-details="auto"
                          class="rounded-lg"
                          bg-color="background"
                        ></v-text-field>
                      </div>

                      <v-row class="mt-4">
                        <v-col cols="12" md="6">
                          <div class="input-container">
                            <label class="input-label">
                              <v-icon icon="mdi-lock" size="16" class="mr-1"></v-icon>
                              Create Password
                            </label>
                            <v-text-field
                              v-model="form.password"
                              placeholder="Minimum 8 characters"
                              prepend-inner-icon="mdi-lock-outline"
                              :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                              :type="showPassword ? 'text' : 'password'"
                              variant="outlined"
                              density="comfortable"
                              @click:append-inner="showPassword = !showPassword"
                              :error-messages="errors.password"
                              :rules="[rules.required, rules.minLength]"
                              hide-details="auto"
                              class="rounded-lg"
                              bg-color="background"
                            ></v-text-field>
                            <div class="text-caption text-medium-emphasis mt-1">
                              <v-icon icon="mdi-information" size="14" class="mr-1"></v-icon>
                              Use a strong password with letters, numbers, and symbols
                            </div>
                          </div>
                        </v-col>
                        <v-col cols="12" md="6">
                          <div class="input-container">
                            <label class="input-label">
                              <v-icon icon="mdi-lock-check" size="16" class="mr-1"></v-icon>
                              Confirm Password
                            </label>
                            <v-text-field
                              v-model="form.password_confirmation"
                              placeholder="Re-enter your password"
                              prepend-inner-icon="mdi-lock-check-outline"
                              :append-inner-icon="showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye'"
                              :type="showConfirmPassword ? 'text' : 'password'"
                              variant="outlined"
                              density="comfortable"
                              @click:append-inner="showConfirmPassword = !showConfirmPassword"
                              :error-messages="errors.password_confirmation"
                              :rules="[rules.required, passwordMatch]"
                              hide-details="auto"
                              class="rounded-lg"
                              bg-color="background"
                            ></v-text-field>
                          </div>
                        </v-col>
                      </v-row>
                    </v-card-text>
                  </v-card>

                  <!-- Terms & Conditions -->
                  <!-- <div class="terms-section mb-6">
                    <v-checkbox
                      v-model="termsAccepted"
                      color="primary"
                      hide-details
                      class="ma-0"
                    >
                      <template v-slot:label>
                        <span class="text-body-2">
                          I agree to the
                          <a href="#" class="text-primary text-decoration-none font-weight-medium">Terms of Service</a> and
                          <a href="#" class="text-primary text-decoration-none font-weight-medium">Privacy Policy</a>
                        </span>
                      </template>
                    </v-checkbox>
                    <div v-if="errors.terms" class="text-error text-caption mt-1 ml-9">
                      {{ errors.terms }}
                    </div>
                  </div> -->

                  <!-- Error Alert -->
                  <v-alert
                    v-if="errors.general"
                    type="error"
                    variant="tonal"
                    density="compact"
                    class="mb-4 rounded-lg"
                  >
                    <template v-slot:prepend>
                      <v-icon icon="mdi-alert-circle" color="error"></v-icon>
                    </template>
                    {{ errors.general }}
                  </v-alert>

                  <!-- Action Buttons -->
                  <div class="d-flex flex-column flex-sm-row gap-4">
                    <v-btn
                      color="secondary"
                      variant="outlined"
                      :to="{ name: 'login' }"
                      size="x-large"
                      class="rounded-lg flex-grow-1"
                      height="52"
                    >
                      <template v-slot:prepend>
                        <v-icon icon="mdi-arrow-left" size="20"></v-icon>
                      </template>
                      <span class="text-button font-weight-medium">Back to Login</span>
                    </v-btn>

                    <v-btn
                    color="primary"
                    @click="register"
                    :loading="loading"
                    size="x-large"
                    class="register-btn rounded-lg flex-grow-1"
                    height="52"
                    >
                    <template v-slot:prepend>
                        <v-icon icon="mdi-rocket-launch" size="20"></v-icon>
                    </template>
                    <span class="text-button font-weight-bold">Create Church Account</span>
                    </v-btn>
                  </div>

                  <!-- Features List -->
                  <v-card class="features-card mt-8" variant="flat">
                    <v-card-text class="text-center">
                      <p class="text-body-1 font-weight-medium mb-4">What you'll get with ChurchFlow:</p>
                      <v-row class="text-center">
                        <v-col cols="6" md="3">
                          <v-icon icon="mdi-account-group" color="primary" size="32" class="mb-2"></v-icon>
                          <p class="text-caption font-weight-medium">Member Management</p>
                        </v-col>
                        <v-col cols="6" md="3">
                          <v-icon icon="mdi-calendar-check" color="primary" size="32" class="mb-2"></v-icon>
                          <p class="text-caption font-weight-medium">Event Scheduling</p>
                        </v-col>
                        <v-col cols="6" md="3">
                          <v-icon icon="mdi-cash-multiple" color="primary" size="32" class="mb-2"></v-icon>
                          <p class="text-caption font-weight-medium">Donation Tracking</p>
                        </v-col>
                        <v-col cols="6" md="3">
                          <v-icon icon="mdi-chart-bar" color="primary" size="32" class="mb-2"></v-icon>
                          <p class="text-caption font-weight-medium">Growth Analytics</p>
                        </v-col>
                      </v-row>
                    </v-card-text>
                  </v-card>
                </v-form>
              </v-card-text>
            </v-card>

            <!-- Footer -->
            <div class="text-center mt-6">
              <p class="text-caption text-white">
                Already have questions?
                <a href="mailto:sales@churchflow.com" class="text-white text-decoration-none font-weight-medium ml-1">
                  Contact our sales team
                </a>
                <br class="d-sm-none">
                <span class="mx-2 d-none d-sm-inline">•</span>
                <a href="tel:+2348001234567" class="text-white text-decoration-none ml-2">
                  📞 +234 800 123 4567
                </a>
              </p>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useToast } from 'vue-toastification'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()
const registerForm = ref(null)

const form = ref({
  church_name: '',
  church_email: '',
  pastor_name: '',
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: ''
})

const errors = ref({})
const loading = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)
// Remove termsAccepted since you commented out that section
// const termsAccepted = ref(false)

const rules = {
  required: value => !!value || 'This field is required',
  email: value => {
    const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    return pattern.test(value) || 'Invalid email format'
  },
  phone: value => {
    // More flexible phone validation for Nigeria
    const pattern = /^[\+]?[0-9\s\-\(\)]{10,15}$/
    return pattern.test(value) || 'Please enter a valid phone number'
  },
  minLength: value => value.length >= 8 || 'Minimum 8 characters required'
}

const passwordMatch = computed(() => {
  return value => value === form.value.password || 'Passwords do not match'
})

const validateForm = async () => {
  try {
    const { valid } = await registerForm.value.validate()
    return valid
  } catch (error) {
    console.error('Validation error:', error)
    return false
  }
}

const register = async () => {
  errors.value = {}

  // Simple validation
  if (!form.value.church_name) {
    errors.value.church_name = ['Church name is required']
    toast.error('Please enter church name')
    return
  }
  if (!form.value.email) {
    errors.value.email = ['Email is required']
    toast.error('Please enter your email')
    return
  }
  if (!form.value.password) {
    errors.value.password = ['Password is required']
    toast.error('Please enter password')
    return
  }
  if (form.value.password !== form.value.password_confirmation) {
    errors.value.password_confirmation = ['Passwords do not match']
    toast.error('Passwords do not match')
    return
  }

  loading.value = true

  try {
    console.log('Attempting registration...')
    console.log('Auth store:', authStore)
    console.log('Register method available:', typeof authStore.register)

    // Use the auth store to register
    const result = await authStore.register(form.value)

    console.log('Registration result:', result)

    if (result.success) {
      toast.success('🎉 Church registered successfully!')
      // Give a small delay for the toast to show
      setTimeout(() => {
        router.push('/')
      }, 1000)
    } else {
      // Handle backend errors
      if (result.errors) {
        errors.value = result.errors
        toast.error('Please check the form for errors')
      } else if (result.message) {
        errors.value = { general: result.message }
        toast.error(result.message)
      }
    }
  } catch (error) {
    console.error('Registration error:', error)
    errors.value = { general: 'An unexpected error occurred. Please try again.' }
    toast.error('Registration failed. Please try again.')
  } finally {
    loading.value = false
  }
}

// Add a test function to check if register method exists
const testRegisterMethod = () => {
  console.log('Auth store methods:', Object.keys(authStore))
  console.log('Register method type:', typeof authStore.register)
}
</script>

<style scoped>
.register-background {
  background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
  min-height: 100vh;
}

.register-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  overflow: hidden;
}

.register-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #1a237e 0%, #283593 100%);
}

.register-header {
  padding: 2rem 2rem 1rem;
  text-align: center;
}

.gradient-text {
  background: linear-gradient(90deg, #1a237e 0%, #283593 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.progress-steps {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem 2rem 0;
  margin-bottom: 1rem;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: all 0.3s ease;
}

.step-number {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #e0e0e0;
  color: #757575;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  margin-bottom: 8px;
  transition: all 0.3s ease;
}

.step.active .step-number {
  background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(26, 35, 126, 0.3);
}

.step-label {
  font-size: 0.75rem;
  color: #757575;
  font-weight: 500;
}

.step.active .step-label {
  color: #1a237e;
  font-weight: bold;
}

.step-divider {
  width: 80px;
  height: 2px;
  background: #e0e0e0;
  margin: 0 20px 20px;
}

.section-card {
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 16px !important;
  background: rgba(255, 255, 255, 0.7);
  transition: all 0.3s ease;
}

.section-card:hover {
  border-color: rgba(26, 35, 126, 0.2);
  box-shadow: 0 4px 20px rgba(26, 35, 126, 0.1);
}

.section-title {
  background: linear-gradient(to right, rgba(26, 35, 126, 0.05), transparent);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 20px 24px;
  font-size: 1.25rem;
  font-weight: 600;
}

.input-label {
  display: flex;
  align-items: center;
  margin-bottom: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  color: rgba(0, 0, 0, 0.87);
}

.input-container :deep(.v-field) {
  border-radius: 12px !important;
}

.input-container :deep(.v-field__outline) {
  border-radius: 12px !important;
}

.register-btn {
  background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
  box-shadow: 0 4px 15px rgba(26, 35, 126, 0.4);
  transition: all 0.3s ease;
}

.register-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(26, 35, 126, 0.6);
}

.register-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.features-card {
  background: linear-gradient(to right, rgba(26, 35, 126, 0.03), rgba(26, 35, 126, 0.08));
  border-radius: 16px !important;
  border: 1px dashed rgba(26, 35, 126, 0.2);
}

.terms-section {
  padding: 16px;
  background: rgba(26, 35, 126, 0.03);
  border-radius: 12px;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

:deep(.v-alert) {
  border-radius: 12px !important;
}

@media (max-width: 600px) {
  .register-header {
    padding: 1.5rem 1rem 0.5rem;
  }

  .progress-steps {
    padding: 1rem 1rem 0;
  }

  .step-divider {
    width: 40px;
  }

  :deep(.v-card-text) {
    padding: 16px !important;
  }
}
</style>
