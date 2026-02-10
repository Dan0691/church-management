<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-6">
      <div>
        <h1 class="text-h4 font-weight-bold">Churches</h1>
        <p class="text-body-1 text-medium-emphasis mt-1">
          Manage multiple church locations
        </p>
      </div>
      <v-btn
        color="primary"
        prepend-icon="mdi-church-plus"
      >
        Add Church
      </v-btn>
    </div>

    <v-card>
      <v-card-text>
        <div class="text-center py-12">
          <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-church</v-icon>
          <h3 class="text-h6 mb-2">Churches Management</h3>
          <p class="text-medium-emphasis">Church management coming soon...</p>
        </div>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup>
// Churches logic will go here
</script>
