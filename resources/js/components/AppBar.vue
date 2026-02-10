<template>
  <v-app-bar app color="primary" dark>
    <v-app-bar-title>Church Management</v-app-bar-title>

    <!-- RIGHT SIDE ICONS -->
    <template v-slot:append>

      <!-- ACCOUNT MENU -->
      <v-menu>
        <template #activator="{ props }">
          <v-btn icon v-bind="props">
            <v-icon>mdi-account-circle</v-icon>
          </v-btn>
        </template>

        <v-list>
          <v-list-item @click="goTo('/members')">
            <v-list-item-title>Members</v-list-item-title>
          </v-list-item>

          <v-list-item @click="goTo('/events')">
            <v-list-item-title>Events</v-list-item-title>
          </v-list-item>

          <v-divider />

          <v-list-item @click="logout">
            <v-list-item-title class="text-error">
              Logout
            </v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>

    </template>
  </v-app-bar>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const goTo = (path) => {
  router.push(path)
}

const logout = () => {
  // later: clear auth token / pinia store
  router.push('/login')
}
</script>
