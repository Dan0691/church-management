<!-- resources/js/App.vue -->
<template>
  <router-view />
  <v-overlay
    v-model="globalLoading"
    class="align-center justify-center"
    persistent
  >
    <v-progress-circular
      indeterminate
      size="64"
    ></v-progress-circular>
  </v-overlay>
</template>

<script setup>
import { ref, provide } from 'vue'
// import { ref } from 'vue'


const globalLoading = ref(false)

// Provide global loading state
provide('globalLoading', {
  show: () => globalLoading.value = true,
  hide: () => globalLoading.value = false
})
</script>
