<template>
  <v-dialog v-model="dialogOpen" max-width="600px" persistent>
    <v-card>
      <v-card-title class="bg-primary text-white d-flex align-center">
        <v-icon left>{{ icon }}</v-icon>
        {{ title }}
      </v-card-title>

      <v-card-text class="pt-6">
        <v-form ref="formRef" @submit.prevent="handleSubmit">
          <slot :formData="localFormData" :updateForm="updateForm" :errors="errors">
            <!-- Default form fields rendered by parent -->
          </slot>

          <!-- Error alert -->
          <v-alert v-if="apiError" type="error" dismissible class="mt-4 mb-4">
            {{ apiError }}
          </v-alert>

          <!-- Form actions -->
          <v-row class="mt-6">
            <v-col cols="6">
              <v-btn variant="outlined" block @click="closeDialog" :disabled="isSubmitting">
                Cancel
              </v-btn>
            </v-col>
            <v-col cols="6">
              <v-btn
                color="primary"
                block
                type="submit"
                :loading="isSubmitting"
                :disabled="isSubmitting"
              >
                {{ isSubmitting ? 'Saving...' : submitButtonText }}
              </v-btn>
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  modelValue: Boolean,
  title: String,
  icon: String,
  initialData: Object,
  onSubmit: Function,
  isLoading: Boolean,
  error: String,
  isEditing: Boolean,
});

const emit = defineEmits(['update:modelValue']);

const dialogOpen = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
});

const submitButtonText = computed(() =>
  props.isEditing ? 'Update' : 'Create'
);

const formRef = ref(null);
const localFormData = ref({ ...props.initialData });
const isSubmitting = ref(false);
const apiError = ref(null);
const errors = ref({});

watch(() => props.initialData, (newVal) => {
  localFormData.value = { ...newVal };
}, { deep: true });

watch(() => props.error, (newVal) => {
  apiError.value = newVal;
});

const updateForm = (updates) => {
  localFormData.value = { ...localFormData.value, ...updates };
  Object.keys(updates).forEach(key => {
    delete errors.value[key];
  });
};

const handleSubmit = async () => {
  if (!await formRef.value?.validate()) return;

  isSubmitting.value = true;
  apiError.value = null;

  try {
    await props.onSubmit(localFormData.value);
    closeDialog();
  } catch (err) {
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors;
    } else {
      apiError.value = err.message || 'An error occurred';
    }
  } finally {
    isSubmitting.value = false;
  }
};

const closeDialog = () => {
  emit('update:modelValue', false);
  apiError.value = null;
  errors.value = {};
};
</script>
