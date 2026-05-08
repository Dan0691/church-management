<template>
  <v-app>
    <v-main class="auth-background">
      <v-container class="fill-height" fluid>
        <v-row align="center" justify="center">
          <v-col cols="12" xl="5" lg="6" md="7" sm="10">
            <v-card class="auth-card elevation-10 rounded-lg">
              <div class="auth-header">
                <v-avatar color="primary" size="64" class="mb-4">
                  <v-icon icon="mdi-church" size="32"></v-icon>
                </v-avatar>
                <h1 class="text-h4 font-weight-bold gradient-text">Set New Password</h1>
                <p class="text-subtitle-1 text-medium-emphasis">Enter your new password below</p>
              </div>

              <v-card-text class="px-8 pb-8 pt-2">
                <v-form @submit.prevent="submit" ref="form">
                  <div class="input-container mb-4">
                    <label class="input-label">Email Address</label>
                    <v-text-field
                      v-model="email"
                      placeholder="Your email"
                      prepend-inner-icon="mdi-email-outline"
                      type="email"
                      variant="outlined"
                      density="comfortable"
                      readonly
                      :error-messages="errors.email"
                      :rules="[rules.required, rules.email]"
                      hide-details="auto"
                      class="rounded-lg"
                      bg-color="background"
                    ></v-text-field>
                  </div>

                  <div class="input-container mb-4">
                    <label class="input-label">New Password</label>
                    <v-text-field
                      v-model="password"
                      placeholder="Enter new password"
                      prepend-inner-icon="mdi-lock-outline"
                      :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                      :type="showPassword ? 'text' : 'password'"
                      variant="outlined"
                      density="comfortable"
                      @click:append-inner="showPassword = !showPassword"
                      :error-messages="errors.password"
                      :rules="[rules.required, rules.minLength]"
                      autocomplete="new-password"
                      hide-details="auto"
                      class="rounded-lg"
                      bg-color="background"
                    ></v-text-field>
                  </div>

                  <div class="input-container mb-4">
                    <label class="input-label">Confirm Password</label>
                    <v-text-field
                      v-model="passwordConfirmation"
                      placeholder="Confirm new password"
                      prepend-inner-icon="mdi-lock-outline"
                      :type="showPasswordConfirm ? 'text' : 'password'"
                      :append-inner-icon="showPasswordConfirm ? 'mdi-eye-off' : 'mdi-eye'"
                      variant="outlined"
                      density="comfortable"
                      @click:append-inner="showPasswordConfirm = !showPasswordConfirm"
                      :error-messages="errors.password_confirmation"
                      :rules="[rules.required, rules.matchPassword]"
                      autocomplete="new-password"
                      hide-details="auto"
                      class="rounded-lg"
                      bg-color="background"
                    ></v-text-field>
                  </div>

                  <v-alert v-if="message" type="success" variant="tonal" class="mb-4 rounded-lg">
                    {{ message }}
                  </v-alert>
                  <v-alert v-if="errors.general" type="error" variant="tonal" class="mb-4 rounded-lg">
                    {{ errors.general }}
                  </v-alert>

                  <v-btn
                    color="primary"
                    @click="submit"
                    :loading="loading"
                    block
                    size="x-large"
                    class="auth-btn rounded-lg mt-2"
                    height="52"
                  >
                    <span class="text-button font-weight-bold">Reset Password</span>
                  </v-btn>

                  <div class="text-center mt-4">
                    <router-link :to="{ name: 'login' }" class="text-primary text-decoration-none">
                      Back to Login
                    </router-link>
                  </div>
                </v-form>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useToast } from 'vue-toastification'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()
const form = ref(null)

const email = ref(route.query.email || '')
const password = ref('')
const passwordConfirmation = ref('')
const showPassword = ref(false)
const showPasswordConfirm = ref(false)
const errors = ref({})
const message = ref('')
const loading = ref(false)

const rules = {
  required: value => !!value || 'This field is required',
  email: value => {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return pattern.test(value) || 'Invalid email format'
  },
  minLength: value => value.length >= 8 || 'Password must be at least 8 characters',
  matchPassword: value => value === password.value || 'Passwords do not match'
}

const validateForm = async () => {
  const { valid } = await form.value.validate()
  return valid
}

const submit = async () => {
  errors.value = {}
  message.value = ''

  if (!await validateForm()) {
    toast.error('Please fix the form errors')
    return
  }

  if (!route.query.token) {
    errors.value = { general: 'Reset token is missing' }
    return
  }

  loading.value = true

  try {
    const result = await authStore.resetPassword({
      token: route.query.token,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value
    })

    if (result.success) {
      message.value = result.message || 'Password reset successfully'
      toast.success('Password reset! You can now login.')
      setTimeout(() => router.push({ name: 'login' }), 2000)
    } else {
      if (result.errors) {
        errors.value = result.errors
      } else if (result.message) {
        errors.value = { general: result.message }
      }
    }
  } catch (error) {
    console.error('Reset password error:', error)
    errors.value = { general: 'An unexpected error occurred. Please try again.' }
  } finally {
    loading.value = false
  }
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
</style>