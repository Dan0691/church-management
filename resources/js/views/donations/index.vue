<template>
  <div>
    <!-- Quick Stats -->
    <v-row class="mb-6">
      <v-col cols="6" md="3" v-for="stat in donationStats" :key="stat.title">
        <v-card class="stats-card">
          <v-card-text class="d-flex align-center">
            <v-avatar :color="stat.color" size="56" class="mr-4">
              <v-icon size="32">{{ stat.icon }}</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ stat.value }}</div>
              <div class="text-caption">{{ stat.title }}</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Recent Donations Table -->
    <v-card>
      <v-card-title>Recent Donations</v-card-title>
      <v-card-text>
        <v-data-table
          :headers="headers"
          :items="donations"
          :loading="loading"
        >
          <template #item.amount="{ item }">
            <span class="font-weight-bold text-success">
              {{ formatCurrency(item.amount, item.currency) }}
            </span>
          </template>

          <template #item.member="{ item }">
            <span v-if="item.member">
              {{ item.member.first_name }} {{ item.member.last_name }}
            </span>
            <span v-else class="text-medium-emphasis">Anonymous</span>
          </template>

          <template #item.is_verified="{ item }">
            <v-chip :color="item.is_verified ? 'success' : 'warning'" size="small">
              {{ item.is_verified ? 'Verified' : 'Pending' }}
            </v-chip>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
  </div>
</template>
