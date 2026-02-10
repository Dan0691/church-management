<template>
  <v-app>
    <v-main class="auth-background">
      <v-container class="fill-height" fluid>
        <v-row align="center" justify="center">
          <v-col cols="12" xl="5" lg="6" md="7" sm="10">
            <v-card class="auth-card elevation-10 rounded-lg">
              <!-- Card Header with Logo -->
              <div class="auth-header">
                <v-avatar color="primary" size="64" class="mb-4">
                  <v-icon icon="mdi-church" size="32"></v-icon>
                </v-avatar>
                <div class="text-center mb-2">
                  <h1 class="text-h4 font-weight-bold gradient-text">ChurchFlow</h1>
                  <p class="text-subtitle-1 text-medium-emphasis">Church Management System</p>
                </div>
              </div>

              <v-card-text class="px-8 pb-8 pt-2">
                <!-- Demo Credentials Banner -->
                <v-alert
                  v-if="demoCredentials"
                  type="info"
                  variant="tonal"
                  class="mb-6 rounded-lg"
                  border="start"
                  density="comfortable"
                >
                  <template v-slot:prepend>
                    <v-icon icon="mdi-information" color="info"></v-icon>
                  </template>
                  <div class="d-flex flex-column">
                    <span class="font-weight-bold">Demo Account Available</span>
                    <div class="demo-credentials mt-2">
                      <div class="d-flex align-center mb-1">
                        <v-icon icon="mdi-email" size="16" class="mr-2"></v-icon>
                        <span class="text-caption">admin@church.com</span>
                      </div>
                      <div class="d-flex align-center">
                        <v-icon icon="mdi-lock" size="16" class="mr-2"></v-icon>
                        <span class="text-caption">password</span>
                      </div>
                    </div>
                    <v-btn
                      color="info"
                      variant="text"
                      size="x-small"
                      @click="fillDemoCredentials"
                      class="mt-2 align-self-start"
                    >
                      Try Demo
                    </v-btn>
                  </div>
                </v-alert>

                <v-form @submit.prevent="login" ref="loginForm" class="auth-form">
                  <!-- Email Field -->
                  <div class="input-container mb-4">
                    <label class="input-label">Email Address</label>
                    <v-text-field
                      v-model="form.email"
                      placeholder="Enter your email"
                      prepend-inner-icon="mdi-email-outline"
                      type="email"
                      variant="outlined"
                      density="comfortable"
                      :error-messages="errors.email"
                      :rules="[rules.required, rules.email]"
                      autocomplete="email"
                      hide-details="auto"
                      class="rounded-lg"
                      bg-color="background"
                    ></v-text-field>
                  </div>

                  <!-- Password Field -->
                  <div class="input-container mb-2">
                    <div class="d-flex justify-space-between align-center mb-1">
                      <label class="input-label">Password</label>
                      <a href="#" class="text-primary text-decoration-none text-caption font-weight-medium">
                        Forgot Password?
                      </a>
                    </div>
                    <v-text-field
                      v-model="form.password"
                      placeholder="Enter your password"
                      prepend-inner-icon="mdi-lock-outline"
                      :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                      :type="showPassword ? 'text' : 'password'"
                      variant="outlined"
                      density="comfortable"
                      @click:append-inner="showPassword = !showPassword"
                      :error-messages="errors.password"
                      :rules="[rules.required]"
                      autocomplete="current-password"
                      hide-details="auto"
                      class="rounded-lg"
                      bg-color="background"
                    ></v-text-field>
                  </div>

                  <!-- Remember Me -->
                  <div class="d-flex align-center justify-space-between mt-4 mb-6">
                    <v-checkbox
                      v-model="form.remember"
                      label="Remember me"
                      density="compact"
                      hide-details
                      color="primary"
                    ></v-checkbox>
                    <v-btn
                      color="primary"
                      variant="text"
                      size="small"
                      @click="fillDemoCredentials"
                      prepend-icon="mdi-account-clock"
                    >
                      Demo Login
                    </v-btn>
                  </div>

                  <!-- Error Alert -->
                  <v-alert
                    v-if="errors.general"
                    type="error"
                    variant="tonal"
                    density="compact"
                    class="mb-4 rounded-lg"
                  >
                    {{ errors.general }}
                  </v-alert>

                  <!-- Login Button -->
                  <v-btn
                    color="primary"
                    @click="login"
                    :loading="loading"
                    block
                    size="x-large"
                    class="auth-btn rounded-lg mt-2"
                    height="52"
                  >
                    <template v-slot:prepend>
                      <v-icon icon="mdi-login" size="20"></v-icon>
                    </template>
                    <span class="text-button font-weight-bold">Sign In</span>
                  </v-btn>
                </v-form>

                <!-- Divider -->
                <div class="my-6">
                  <v-divider>
                    <span class="text-caption text-medium-emphasis px-3">Or continue with</span>
                  </v-divider>
                </div>

                <!-- Register CTA -->
                <div class="text-center">
                  <p class="text-body-1 text-medium-emphasis mb-3">
                    Don't have a church account?
                  </p>
                  <v-btn
                    color="secondary"
                    variant="outlined"
                    :to="{ name: 'register' }"
                    block
                    size="large"
                    class="rounded-lg"
                    height="48"
                  >
                    <template v-slot:prepend>
                      <v-icon icon="mdi-account-plus" size="20"></v-icon>
                    </template>
                    <span class="text-button font-weight-medium">Create Church Account</span>
                  </v-btn>
                </div>

                <!-- Support Link -->
                <div class="text-center mt-6 pt-4 border-t">
                  <p class="text-caption text-medium-emphasis">
                    Need assistance?
                    <a href="mailto:support@churchflow.com" class="text-primary text-decoration-none font-weight-medium">
                      Contact our support team
                    </a>
                  </p>
                </div>
              </v-card-text>
            </v-card>

            <!-- Copyright -->
            <div class="text-center mt-6">
              <p class="text-caption text-white">
                © {{ new Date().getFullYear() }} ChurchFlow. All rights reserved.
                <a href="#" class="text-white text-decoration-none ml-2">Privacy Policy</a> •
                <a href="#" class="text-white text-decoration-none ml-1">Terms of Service</a>
              </p>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useToast } from 'vue-toastification'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()
const loginForm = ref(null)

const form = ref({
  email: '',
  password: '',
  remember: false
})

const errors = ref({})
const loading = ref(false)
const showPassword = ref(false)
const demoCredentials = ref(true)

const rules = {
  required: value => !!value || 'This field is required',
  email: value => {
    const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    return pattern.test(value) || 'Invalid email format'
  }
}

const validateForm = async () => {
  const { valid } = await loginForm.value.validate()
  return valid
}

const login = async () => {
  errors.value = {}

  if (!await validateForm()) {
    toast.error('Please fix the form errors')
    return
  }

  loading.value = true

  try {
    const result = await authStore.login(form.value)

    if (result.success) {
      // Success! The auth store already handled token storage and redirection
      router.push('/')
    } else {
      // Handle backend errors
      if (result.errors) {
        errors.value = result.errors
      } else if (result.message) {
        errors.value = { general: result.message }
      }
    }
  } catch (error) {
    console.error('Login error:', error)
    errors.value = { general: 'An unexpected error occurred. Please try again.' }
  } finally {
    loading.value = false
  }
}

const fillDemoCredentials = () => {
  form.value.email = 'admin@church.com'
  form.value.password = 'password'
  toast.info('Demo credentials filled! Click Sign In to continue.')
}
</script>

<style scoped>
.auth-background {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  min-height: 100vh;
}

.auth-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  overflow: hidden;
}

.auth-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
}

.auth-header {
  padding: 2.5rem 2rem 0.5rem;
  text-align: center;
}

.gradient-text {
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.input-label {
  display: block;
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

.auth-btn {
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
  transition: all 0.3s ease;
}

.auth-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
}

.demo-credentials {
  background: rgba(33, 150, 243, 0.1);
  padding: 10px 12px;
  border-radius: 8px;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
}

:deep(.v-alert) {
  border-radius: 12px !important;
}
</style>
