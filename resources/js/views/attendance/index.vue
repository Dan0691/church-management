<template>
  <div>
    <!-- Page Header -->
    <v-breadcrumbs class="mb-4" :items="breadcrumbs" divider=">"></v-breadcrumbs>

    <!-- Page Header with Actions -->
    <div class="d-flex justify-space-between align-center mb-6">
      <div>
        <h1 class="text-h4 font-weight-bold">Attendance Management</h1>
        <p class="text-body-1 text-medium-emphasis mt-1">
          Track and manage all attendance records across events ({{ attendanceRecords.length }} records)
        </p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <v-btn color="primary" prepend-icon="mdi-account-plus" @click="openCreateDialog" class="mb-1">
          Record Attendance
        </v-btn>
        <v-btn variant="outlined" prepend-icon="mdi-chart-line" @click="showTrends = true" class="mb-1">
          View Trends
        </v-btn>
        <v-btn variant="outlined" prepend-icon="mdi-download" @click="exportAttendanceData" class="mb-1">
          Export Data
        </v-btn>
        <v-btn variant="outlined" prepend-icon="mdi-calendar-month" @click="showMonthlyView = true" class="mb-1">
          Monthly View
        </v-btn>
      </div>
    </div>

    <!-- Stats Cards -->
    <v-row class="mb-6">
      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card" @click="filterByDate('today')">
          <v-card-text class="d-flex align-center">
            <v-avatar color="primary" size="56" class="mr-4">
              <v-icon size="32">mdi-account-group</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ formatNumber(stats.today_attendance) }}</div>
              <div class="text-caption text-medium-emphasis">Today's Attendance</div>
              <div class="text-caption text-success mt-1">
                {{ stats.today_records }} records
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card" @click="filterByDate('this_week')">
          <v-card-text class="d-flex align-center">
            <v-avatar color="success" size="56" class="mr-4">
              <v-icon size="32">mdi-calendar-week</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ formatNumber(stats.this_week_attendance) }}</div>
              <div class="text-caption text-medium-emphasis">This Week</div>
              <div class="text-caption text-success mt-1">
                {{ stats.this_week_records }} records
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card" @click="filterByDate('this_month')">
          <v-card-text class="d-flex align-center">
            <v-avatar color="warning" size="56" class="mr-4">
              <v-icon size="32">mdi-chart-bar</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ formatNumber(stats.this_month_attendance) }}</div>
              <div class="text-caption text-medium-emphasis">This Month</div>
              <div class="text-caption text-warning mt-1">
                Avg: {{ stats.avg_daily_attendance }}/day
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card" @click="showStatsDialog = true">
          <v-card-text class="d-flex align-center">
            <v-avatar color="info" size="56" class="mr-4">
              <v-icon size="32">mdi-chart-pie</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ formatNumber(stats.total_attendance) }}</div>
              <div class="text-caption text-medium-emphasis">Total Attendance</div>
              <div class="text-caption text-info mt-1">
                {{ stats.total_records }} total records
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Attendance Composition -->
    <v-row class="mb-6">
      <v-col cols="12" md="8">
        <v-card>
          <v-card-title>Attendance by Category</v-card-title>
          <v-card-text>
            <div style="height: 300px">
              <canvas ref="compositionChart"></canvas>
            </div>
            <v-row class="mt-4">
              <v-col cols="6" sm="3" class="text-center">
                <div class="text-h6 text-primary">{{ stats.men_total }}</div>
                <div class="text-caption">Men</div>
                <div class="text-caption text-medium-emphasis">
                  {{ getPercentage(stats.men_total, stats.total_attendance) }}%
                </div>
              </v-col>
              <v-col cols="6" sm="3" class="text-center">
                <div class="text-h6 text-pink">{{ stats.women_total }}</div>
                <div class="text-caption">Women</div>
                <div class="text-caption text-medium-emphasis">
                  {{ getPercentage(stats.women_total, stats.total_attendance) }}%
                </div>
              </v-col>
              <v-col cols="6" sm="3" class="text-center">
                <div class="text-h6 text-orange">{{ stats.children_total }}</div>
                <div class="text-caption">Children</div>
                <div class="text-caption text-medium-emphasis">
                  {{ getPercentage(stats.children_total, stats.total_attendance) }}%
                </div>
              </v-col>
              <v-col cols="6" sm="3" class="text-center">
                <div class="text-h6 text-cyan">{{ stats.visitors_total }}</div>
                <div class="text-caption">Visitors</div>
                <div class="text-caption text-medium-emphasis">
                  {{ getPercentage(stats.visitors_total, stats.total_attendance) }}%
                </div>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" md="4">
        <v-card class="h-100">
          <v-card-title>Top Events by Attendance</v-card-title>
          <v-card-text>
            <v-list lines="two" density="compact">
              <v-list-item
                v-for="event in topEvents"
                :key="event.id"
                @click="viewEvent(event)"
                class="mb-2"
              >
                <template #prepend>
                  <v-avatar size="40" :color="getEventColor(event)" class="mr-3">
                    <v-icon size="20" color="white">{{ getEventIcon(event.type) }}</v-icon>
                  </v-avatar>
                </template>
                <v-list-item-title class="text-body-2 font-weight-medium">
                  {{ event.title }}
                </v-list-item-title>
                <v-list-item-subtitle class="text-caption">
                  {{ formatDate(event.start_date) }}
                </v-list-item-subtitle>
                <template #append>
                  <v-chip size="small" color="primary">
                    {{ event.total_attendance }}
                  </v-chip>
                </template>
              </v-list-item>
              <v-list-item v-if="topEvents.length === 0" class="text-center">
                <v-list-item-title class="text-caption text-medium-emphasis">
                  No attendance records yet
                </v-list-item-title>
              </v-list-item>
            </v-list>
            <v-btn
              v-if="topEvents.length > 0"
              color="primary"
              variant="text"
              size="small"
              block
              class="mt-2"
              @click="viewAllEvents"
            >
              View All Events
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Search and Filter Bar -->
    <v-card class="mb-6">
      <v-card-text>
        <v-row align="center">
          <v-col cols="12" md="3">
            <v-text-field
              v-model="search"
              placeholder="Search by event name or notes..."
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="comfortable"
              hide-details
              @update:model-value="debouncedFetchAttendance"
            ></v-text-field>
          </v-col>

          <v-col cols="12" md="2">
            <v-select
              v-model="eventFilter"
              :items="eventOptions"
              label="Event"
              variant="outlined"
              density="comfortable"
              clearable
              hide-details
              @update:model-value="fetchAttendance"
            ></v-select>
          </v-col>

          <v-col cols="12" md="2">
            <v-select
              v-model="categoryFilter"
              :items="categoryOptions"
              label="Category"
              variant="outlined"
              density="comfortable"
              clearable
              hide-details
              @update:model-value="fetchAttendance"
            ></v-select>
          </v-col>

          <v-col cols="12" md="3">
            <v-menu v-model="dateMenu" :close-on-content-click="false">
              <template #activator="{ props }">
                <v-text-field
                  v-bind="props"
                  :model-value="dateRangeText"
                  label="Date Range"
                  prepend-inner-icon="mdi-calendar"
                  variant="outlined"
                  density="comfortable"
                  readonly
                  hide-details
                ></v-text-field>
              </template>
              <v-date-picker
                v-model="dateRange"
                range
                @update:model-value="dateMenu = false"
              ></v-date-picker>
            </v-menu>
          </v-col>

          <v-col cols="12" md="2">
            <v-btn
              color="primary"
              variant="tonal"
              @click="fetchAttendance"
              block
              :loading="loading"
            >
              Apply Filters
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Attendance Records Table -->
    <v-card>
      <v-card-title class="d-flex justify-space-between align-center">
        <span>Attendance Records</span>
        <v-chip color="primary" variant="tonal">
          {{ filteredAttendance.length }} records
        </v-chip>
      </v-card-title>
      <v-card-text>
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
          <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
          <p class="mt-4 text-medium-emphasis">Loading attendance records...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredAttendance.length === 0" class="text-center py-12">
          <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-account-group-off</v-icon>
          <h3 class="text-h6 mb-2">No attendance records found</h3>
          <p class="text-medium-emphasis mb-4">
            {{ search || eventFilter || categoryFilter ? 'Try changing your filters' : 'Record your first attendance to get started' }}
          </p>
          <v-btn color="primary" @click="openCreateDialog">
            Record First Attendance
          </v-btn>
        </div>

        <!-- Attendance Table -->
        <div v-else>
          <v-data-table
            :headers="headers"
            :items="filteredAttendance"
            :items-per-page="10"
            class="elevation-1"
            @click:row="viewAttendance"
          >
            <template #item.event.title="{ item }">
              <div class="d-flex align-center">
                <v-avatar size="32" :color="getEventColor(item.event)" class="mr-2">
                  <v-icon size="16" color="white">{{ getEventIcon(item.event.type) }}</v-icon>
                </v-avatar>
                <div>
                  <div class="text-body-2 font-weight-medium">{{ item.event.title }}</div>
                  <div class="text-caption text-medium-emphasis">{{ item.event.type }}</div>
                </div>
              </div>
            </template>

            <template #item.men="{ item }">
              <v-chip size="small" color="primary" variant="outlined">
                {{ item.men }}
              </v-chip>
            </template>

            <template #item.women="{ item }">
              <v-chip size="small" color="pink" variant="outlined">
                {{ item.women }}
              </v-chip>
            </template>

            <template #item.children="{ item }">
              <v-chip size="small" color="orange" variant="outlined">
                {{ item.children }}
              </v-chip>
            </template>

            <template #item.visitors="{ item }">
              <v-chip size="small" color="cyan" variant="outlined">
                {{ item.visitors }}
              </v-chip>
            </template>

            <template #item.total="{ item }">
              <div class="text-h6 font-weight-bold">{{ item.total }}</div>
            </template>

            <template #item.created_at="{ item }">
              <div class="text-caption">
                {{ formatDateTime(item.created_at) }}
              </div>
            </template>

            <template #item.actions="{ item }">
              <div class="d-flex gap-1">
                <v-tooltip text="View Details">
                  <template #activator="{ props }">
                    <v-btn
                      v-bind="props"
                      icon
                      size="small"
                      variant="text"
                      color="info"
                      @click.stop="viewAttendance(item)"
                    >
                      <v-icon>mdi-eye</v-icon>
                    </v-btn>
                  </template>
                </v-tooltip>

                <v-tooltip text="Edit">
                  <template #activator="{ props }">
                    <v-btn
                      v-bind="props"
                      icon
                      size="small"
                      variant="text"
                      color="warning"
                      @click.stop="editAttendance(item)"
                    >
                      <v-icon>mdi-pencil</v-icon>
                    </v-btn>
                  </template>
                </v-tooltip>

                <v-tooltip text="Delete">
                  <template #activator="{ props }">
                    <v-btn
                      v-bind="props"
                      icon
                      size="small"
                      variant="text"
                      color="error"
                      @click.stop="deleteAttendancePrompt(item)"
                    >
                      <v-icon>mdi-delete</v-icon>
                    </v-btn>
                  </template>
                </v-tooltip>
              </div>
            </template>
          </v-data-table>
        </div>
      </v-card-text>
    </v-card>

    <!-- Create/Edit Attendance Dialog -->
    <v-dialog v-model="dialog" max-width="800">
      <v-card>
        <v-card-title class="d-flex justify-space-between align-center">
          <span class="text-h5">{{ editingAttendance ? 'Edit Attendance' : 'Record New Attendance' }}</span>
          <v-btn icon @click="closeDialog">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text>
          <v-form ref="attendanceForm" v-model="formValid">
            <v-row>
              <v-col cols="12">
                <v-select
                  v-model="form.event_id"
                  :items="events"
                  item-title="title"
                  item-value="id"
                  label="Select Event *"
                  variant="outlined"
                  :rules="[v => !!v || 'Event is required']"
                  required
                  :loading="loadingEvents"
                  @update:model-value="onEventSelect"
                >
                  <template #item="{ props, item }">
                    <v-list-item v-bind="props">
                      <template #prepend>
                        <v-avatar :color="getEventColor(item.raw)" size="36" class="mr-2">
                          <v-icon color="white">{{ getEventIcon(item.raw.type) }}</v-icon>
                        </v-avatar>
                      </template>
                      <v-list-item-title>{{ item.title }}</v-list-item-title>
                      <v-list-item-subtitle>
                        {{ formatDate(item.raw.start_date) }} • {{ item.raw.type }}
                      </v-list-item-subtitle>
                    </v-list-item>
                  </template>
                </v-select>
              </v-col>

              <v-divider class="my-4"></v-divider>

              <v-col cols="12">
                <h4 class="text-h6 mb-4">Attendance Breakdown</h4>
                <v-row>
                  <v-col cols="12" sm="6" md="3">
                    <v-text-field
                      v-model.number="form.men"
                      label="Men"
                      type="number"
                      variant="outlined"
                      :rules="[v => v >= 0 || 'Must be 0 or greater']"
                      min="0"
                      @input="updateTotal"
                    >
                      <template #prepend>
                        <v-icon color="primary">mdi-account-male</v-icon>
                      </template>
                    </v-text-field>
                  </v-col>

                  <v-col cols="12" sm="6" md="3">
                    <v-text-field
                      v-model.number="form.women"
                      label="Women"
                      type="number"
                      variant="outlined"
                      :rules="[v => v >= 0 || 'Must be 0 or greater']"
                      min="0"
                      @input="updateTotal"
                    >
                      <template #prepend>
                        <v-icon color="pink">mdi-account-female</v-icon>
                      </template>
                    </v-text-field>
                  </v-col>

                  <v-col cols="12" sm="6" md="3">
                    <v-text-field
                      v-model.number="form.children"
                      label="Children"
                      type="number"
                      variant="outlined"
                      :rules="[v => v >= 0 || 'Must be 0 or greater']"
                      min="0"
                      @input="updateTotal"
                    >
                      <template #prepend>
                        <v-icon color="orange">mdi-human-child</v-icon>
                      </template>
                    </v-text-field>
                  </v-col>

                  <v-col cols="12" sm="6" md="3">
                    <v-text-field
                      v-model.number="form.visitors"
                      label="Visitors"
                      type="number"
                      variant="outlined"
                      :rules="[v => v >= 0 || 'Must be 0 or greater']"
                      min="0"
                      @input="updateTotal"
                    >
                      <template #prepend>
                        <v-icon color="cyan">mdi-account-star</v-icon>
                      </template>
                    </v-text-field>
                  </v-col>
                </v-row>

                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.total"
                      label="Total Attendance"
                      variant="outlined"
                      readonly
                      hint="Automatically calculated"
                      persistent-hint
                    >
                      <template #prepend>
                        <v-icon color="success">mdi-account-group</v-icon>
                      </template>
                    </v-text-field>
                  </v-col>
                </v-row>
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="form.notes"
                  label="Notes"
                  variant="outlined"
                  rows="3"
                  placeholder="Any additional notes about the attendance..."
                ></v-textarea>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>

        <v-card-actions class="px-6 pb-4">
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="closeDialog">
            Cancel
          </v-btn>
          <v-btn
            color="primary"
            @click="saveAttendance"
            :loading="saving"
            :disabled="!formValid"
          >
            {{ editingAttendance ? 'Update Attendance' : 'Save Attendance' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- View Attendance Details Dialog -->
    <v-dialog v-model="viewDialog" max-width="600">
      <v-card v-if="selectedAttendance">
        <v-card-title class="d-flex justify-space-between align-center">
          <div class="d-flex align-center">
            <v-avatar :color="getEventColor(selectedAttendance.event)" size="48" class="mr-3">
              <v-icon color="white">{{ getEventIcon(selectedAttendance.event.type) }}</v-icon>
            </v-avatar>
            <div>
              <h2 class="text-h5">{{ selectedAttendance.event.title }}</h2>
              <div class="text-caption text-medium-emphasis">
                {{ formatDateTime(selectedAttendance.created_at) }}
              </div>
            </div>
          </div>
          <v-btn icon @click="viewDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-divider></v-divider>

        <v-card-text>
          <v-row class="mt-4">
            <v-col cols="12" md="6">
              <v-card variant="outlined">
                <v-card-title class="text-subtitle-1">Attendance Breakdown</v-card-title>
                <v-card-text>
                  <div class="d-flex align-center justify-space-between mb-3">
                    <div class="d-flex align-center">
                      <v-icon color="primary" class="mr-2">mdi-account-male</v-icon>
                      <span>Men</span>
                    </div>
                    <span class="text-h6 font-weight-bold">{{ selectedAttendance.men }}</span>
                  </div>
                  <div class="d-flex align-center justify-space-between mb-3">
                    <div class="d-flex align-center">
                      <v-icon color="pink" class="mr-2">mdi-account-female</v-icon>
                      <span>Women</span>
                    </div>
                    <span class="text-h6 font-weight-bold">{{ selectedAttendance.women }}</span>
                  </div>
                  <div class="d-flex align-center justify-space-between mb-3">
                    <div class="d-flex align-center">
                      <v-icon color="orange" class="mr-2">mdi-human-child</v-icon>
                      <span>Children</span>
                    </div>
                    <span class="text-h6 font-weight-bold">{{ selectedAttendance.children }}</span>
                  </div>
                  <div class="d-flex align-center justify-space-between mb-3">
                    <div class="d-flex align-center">
                      <v-icon color="cyan" class="mr-2">mdi-account-star</v-icon>
                      <span>Visitors</span>
                    </div>
                    <span class="text-h6 font-weight-bold">{{ selectedAttendance.visitors }}</span>
                  </div>
                  <v-divider class="my-2"></v-divider>
                  <div class="d-flex align-center justify-space-between">
                    <div class="d-flex align-center">
                      <v-icon color="success" class="mr-2">mdi-account-group</v-icon>
                      <span class="text-h6">Total</span>
                    </div>
                    <span class="text-h3 font-weight-bold text-success">{{ selectedAttendance.total }}</span>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>

            <v-col cols="12" md="6">
              <v-card variant="outlined" class="h-100">
                <v-card-title class="text-subtitle-1">Event Details</v-card-title>
                <v-card-text>
                  <div class="mb-3">
                    <div class="text-caption text-medium-emphasis">Event Type</div>
                    <v-chip size="small" :color="getEventColor(selectedAttendance.event)" class="mt-1">
                      {{ selectedAttendance.event.type }}
                    </v-chip>
                  </div>
                  <div class="mb-3">
                    <div class="text-caption text-medium-emphasis">Date & Time</div>
                    <div class="text-body-1">{{ formatDateTime(selectedAttendance.event.start_date) }}</div>
                  </div>
                  <div class="mb-3">
                    <div class="text-caption text-medium-emphasis">Location</div>
                    <div class="text-body-1">{{ selectedAttendance.event.location || 'Not specified' }}</div>
                  </div>
                  <div v-if="selectedAttendance.notes">
                    <div class="text-caption text-medium-emphasis">Notes</div>
                    <div class="text-body-1 mt-1">{{ selectedAttendance.notes }}</div>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <div v-if="selectedAttendance.recorder" class="mt-4">
            <v-divider></v-divider>
            <div class="d-flex align-center mt-3">
              <v-avatar size="40" color="grey-lighten-2" class="mr-3">
                <v-icon>mdi-account</v-icon>
              </v-avatar>
              <div>
                <div class="text-caption text-medium-emphasis">Recorded By</div>
                <div class="text-body-1">{{ selectedAttendance.recorder.name }}</div>
              </div>
            </div>
          </div>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 pb-4">
          <v-btn color="primary" @click="editAttendance(selectedAttendance)">
            <v-icon left>mdi-pencil</v-icon>
            Edit
          </v-btn>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="viewDialog = false">
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Stats Dialog -->
    <v-dialog v-model="showStatsDialog" max-width="800">
      <v-card>
        <v-card-title>Attendance Statistics</v-card-title>
        <v-card-text>
          <v-row>
            <v-col cols="12" md="6" v-for="(value, key) in detailedStats" :key="key">
              <v-card variant="outlined" class="h-100">
                <v-card-title class="text-subtitle-1">{{ key.replace(/_/g, ' ').toUpperCase() }}</v-card-title>
                <v-card-text class="text-center">
                  <div class="text-h3 font-weight-bold">{{ value }}</div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <v-divider class="my-4"></v-divider>

          <h4 class="text-subtitle-1 mb-2">Attendance Trends</h4>
          <div style="height: 200px">
            <canvas ref="trendsChart"></canvas>
          </div>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="showStatsDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Trends Dialog -->
    <v-dialog v-model="showTrends" max-width="1000" fullscreen>
      <v-card>
        <v-card-title class="d-flex justify-space-between align-center">
          <span class="text-h5">Attendance Trends Analysis</span>
          <v-btn icon @click="showTrends = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>
          <v-tabs v-model="trendsTab" color="primary">
            <v-tab value="daily">Daily Trends</v-tab>
            <v-tab value="weekly">Weekly Trends</v-tab>
            <v-tab value="monthly">Monthly Trends</v-tab>
            <v-tab value="comparison">Comparison</v-tab>
          </v-tabs>

          <v-window v-model="trendsTab" class="mt-4">
            <v-window-item value="daily">
              <div style="height: 400px">
                <canvas ref="dailyTrendsChart"></canvas>
              </div>
            </v-window-item>
            <v-window-item value="weekly">
              <div style="height: 400px">
                <canvas ref="weeklyTrendsChart"></canvas>
              </div>
            </v-window-item>
            <v-window-item value="monthly">
              <div style="height: 400px">
                <canvas ref="monthlyTrendsChart"></canvas>
              </div>
            </v-window-item>
            <v-window-item value="comparison">
              <v-row>
                <v-col cols="12" md="6">
                  <v-card>
                    <v-card-title>This Month vs Last Month</v-card-title>
                    <v-card-text style="height: 300px">
                      <canvas ref="comparisonChart"></canvas>
                    </v-card-text>
                  </v-card>
                </v-col>
                <v-col cols="12" md="6">
                  <v-card>
                    <v-card-title>Top Event Types</v-card-title>
                    <v-card-text style="height: 300px">
                      <canvas ref="eventTypeChart"></canvas>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
            </v-window-item>
          </v-window>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- Monthly View Dialog -->
    <v-dialog v-model="showMonthlyView" max-width="1200">
      <v-card>
        <v-card-title class="d-flex justify-space-between align-center">
          <div class="d-flex align-center">
            <v-btn icon @click="prevMonth">
              <v-icon>mdi-chevron-left</v-icon>
            </v-btn>
            <h3 class="text-h5 mx-4">{{ monthlyViewTitle }}</h3>
            <v-btn icon @click="nextMonth">
              <v-icon>mdi-chevron-right</v-icon>
            </v-btn>
          </div>
          <v-btn icon @click="showMonthlyView = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>
          <v-calendar
            ref="calendar"
            v-model="calendarDate"
            :events="calendarEvents"
            type="month"
            @click:event="viewAttendanceFromCalendar"
            @click:date="viewDayAttendance"
          ></v-calendar>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title class="text-h5">Delete Attendance Record</v-card-title>
        <v-card-text>
          Are you sure you want to delete this attendance record?
          <v-alert type="warning" class="mt-4">
            This action cannot be undone. This will permanently remove the attendance data.
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="deleteDialog = false">
            Cancel
          </v-btn>
          <v-btn color="error" @click="deleteAttendance" :loading="deleting">
            Delete
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import Chart from 'chart.js/auto'
import * as XLSX from 'xlsx'
import { saveAs } from 'file-saver'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const toast = useToast()
const auth = useAuthStore()

// Refs
const loading = ref(false)
const loadingEvents = ref(false)
const saving = ref(false)
const deleting = ref(false)
const dialog = ref(false)
const viewDialog = ref(false)
const deleteDialog = ref(false)
const showStatsDialog = ref(false)
const showTrends = ref(false)
const showMonthlyView = ref(false)
const formValid = ref(false)
const dateMenu = ref(false)
const trendsTab = ref('daily')

// Data
const attendanceRecords = ref([])
const events = ref([])
const selectedAttendance = ref(null)
const selectedAttendanceToDelete = ref(null)
const editingAttendance = ref(null)
const calendarDate = ref(new Date().toISOString().substring(0, 10))

// Filters
const search = ref('')
const eventFilter = ref('')
const categoryFilter = ref('')
const dateRange = ref([null, null])

// Form
const form = ref({
  event_id: null,
  men: 0,
  women: 0,
  children: 0,
  visitors: 0,
  total: 0,
  notes: ''
})

// Charts refs
const compositionChart = ref(null)
const trendsChart = ref(null)
const dailyTrendsChart = ref(null)
const weeklyTrendsChart = ref(null)
const monthlyTrendsChart = ref(null)
const comparisonChart = ref(null)
const eventTypeChart = ref(null)

// Stats
const stats = ref({
  total_attendance: 0,
  total_records: 0,
  today_attendance: 0,
  today_records: 0,
  this_week_attendance: 0,
  this_week_records: 0,
  this_month_attendance: 0,
  this_month_records: 0,
  avg_daily_attendance: 0,
  men_total: 0,
  women_total: 0,
  children_total: 0,
  visitors_total: 0
})

const detailedStats = ref({})

// Breadcrumbs
const breadcrumbs = ref([
  { title: 'Dashboard', to: '/' },
  { title: 'Attendance', disabled: true }
])

// Table headers
const headers = ref([
  { title: 'Event', key: 'event.title', sortable: true },
  { title: 'Men', key: 'men', sortable: true },
  { title: 'Women', key: 'women', sortable: true },
  { title: 'Children', key: 'children', sortable: true },
  { title: 'Visitors', key: 'visitors', sortable: true },
  { title: 'Total', key: 'total', sortable: true },
  { title: 'Date Recorded', key: 'created_at', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false }
])

// Options
const eventOptions = computed(() => {
  return events.value.map(event => ({
    title: event.title,
    value: event.id
  }))
})

const categoryOptions = ref([
  'Service', 'Meeting', 'Outreach', 'Social', 'Youth', 'Children',
  'Women', 'Men', 'Prayer', 'Bible Study', 'Training', 'Conference'
])

// Form refs
const attendanceForm = ref(null)

// Computed properties
const filteredAttendance = computed(() => {
  let filtered = attendanceRecords.value

  // Apply search filter
  if (search.value) {
    const searchTerm = search.value.toLowerCase()
    filtered = filtered.filter(record =>
      record.event?.title?.toLowerCase().includes(searchTerm) ||
      record.notes?.toLowerCase().includes(searchTerm) ||
      record.event?.type?.toLowerCase().includes(searchTerm)
    )
  }

  // Apply event filter
  if (eventFilter.value) {
    filtered = filtered.filter(record => record.event_id === eventFilter.value)
  }

  // Apply category filter
  if (categoryFilter.value) {
    filtered = filtered.filter(record => record.event?.type === categoryFilter.value)
  }

  // Apply date range filter
  if (dateRange.value[0] && dateRange.value[1]) {
    const startDate = new Date(dateRange.value[0])
    const endDate = new Date(dateRange.value[1])
    endDate.setHours(23, 59, 59, 999)

    filtered = filtered.filter(record => {
      const recordDate = new Date(record.created_at)
      return recordDate >= startDate && recordDate <= endDate
    })
  }

  return filtered
})

const topEvents = computed(() => {
  // Group attendance by event
  const eventMap = new Map()

  attendanceRecords.value.forEach(record => {
    if (record.event) {
      const eventId = record.event.id
      if (!eventMap.has(eventId)) {
        eventMap.set(eventId, {
          ...record.event,
          total_attendance: 0
        })
      }
      eventMap.get(eventId).total_attendance += record.total
    }
  })

  return Array.from(eventMap.values())
    .sort((a, b) => b.total_attendance - a.total_attendance)
    .slice(0, 5)
})

const dateRangeText = computed(() => {
  if (!dateRange.value[0] && !dateRange.value[1]) return 'All dates'
  if (dateRange.value[0] && !dateRange.value[1]) return formatDate(dateRange.value[0])
  if (dateRange.value[0] && dateRange.value[1]) {
    return `${formatDate(dateRange.value[0])} - ${formatDate(dateRange.value[1])}`
  }
  return 'All dates'
})

const calendarEvents = computed(() => {
  return attendanceRecords.value.map(record => ({
    name: `${record.event?.title}: ${record.total} attendees`,
    start: new Date(record.created_at),
    color: getEventColor(record.event),
    timed: true
  }))
})

const monthlyViewTitle = computed(() => {
  const date = new Date(calendarDate.value)
  return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
})

// Methods
const fetchAttendance = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const params = {
      search: search.value,
      event_id: eventFilter.value,
      category: categoryFilter.value,
      start_date: dateRange.value[0],
      end_date: dateRange.value[1]
    }

    // Clean up params
    Object.keys(params).forEach(key => {
      if (!params[key]) delete params[key]
    })

    const response = await axios.get('/api/attendance', {
      headers: { 'Authorization': `Bearer ${token}` },
      params
    })

    if (response.data.success) {
      attendanceRecords.value = response.data.data || []
    } else {
      toast.error(response.data.message || 'Failed to load attendance records')
    }
  } catch (error) {
    console.error('Error fetching attendance:', error)
    toast.error('Failed to load attendance records')
  } finally {
    loading.value = false
  }
}

const fetchEvents = async () => {
  loadingEvents.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/events', {
      headers: { 'Authorization': `Bearer ${token}` },
      params: { per_page: 100 } // Fetch all events for dropdown
    })

    if (response.data.success) {
      events.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error fetching events:', error)
  } finally {
    loadingEvents.value = false
  }
}

const fetchStats = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/attendance/stats', {
      headers: { 'Authorization': `Bearer ${token}` }
    })

    if (response.data.success) {
      stats.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

const openCreateDialog = async () => {
  editingAttendance.value = null
  resetForm()
  await fetchEvents() // Refresh events list
  dialog.value = true
}

const editAttendance = (attendance) => {
  editingAttendance.value = attendance
  form.value = {
    event_id: attendance.event_id,
    men: attendance.men,
    women: attendance.women,
    children: attendance.children,
    visitors: attendance.visitors,
    total: attendance.total,
    notes: attendance.notes || ''
  }
  dialog.value = true
}

const viewAttendance = (attendance) => {
  selectedAttendance.value = attendance
  viewDialog.value = true
}

const viewAttendanceFromCalendar = ({ event }) => {
  // Find the attendance record
  const attendance = attendanceRecords.value.find(
    record => record.event?.title === event.name.split(':')[0]
  )
  if (attendance) {
    viewAttendance(attendance)
  }
}

const viewDayAttendance = ({ date }) => {
  const dayAttendances = attendanceRecords.value.filter(record => {
    const recordDate = new Date(record.created_at).toDateString()
    return recordDate === new Date(date).toDateString()
  })

  if (dayAttendances.length > 0) {
    toast.info(`${dayAttendances.length} attendance records on ${formatDate(date)}`)
  } else {
    toast.info(`No attendance records for ${formatDate(date)}`)
  }
}

const saveAttendance = async () => {
  if (!formValid.value) {
    toast.error('Please fill in all required fields correctly')
    return
  }

  saving.value = true
  try {
    const token = localStorage.getItem('token')
    const endpoint = editingAttendance.value
      ? `/api/attendance/${editingAttendance.value.id}`
      : '/api/attendance'

    const method = editingAttendance.value ? 'put' : 'post'

    const response = await axios[method](endpoint, form.value, {
      headers: { 'Authorization': `Bearer ${token}` }
    })

    if (response.data.success) {
      toast.success(response.data.message || 'Attendance saved successfully')
      closeDialog()
      fetchAttendance()
      fetchStats()
    } else {
      toast.error(response.data.message || 'Failed to save attendance')
    }
  } catch (error) {
    console.error('Error saving attendance:', error)
    if (error.response?.status === 422) {
      const errors = error.response.data.errors
      Object.keys(errors).forEach(key => {
        toast.error(`${key}: ${errors[key][0]}`)
      })
    } else {
      toast.error(error.response?.data?.message || 'Failed to save attendance')
    }
  } finally {
    saving.value = false
  }
}

const deleteAttendancePrompt = (attendance) => {
  selectedAttendanceToDelete.value = attendance
  deleteDialog.value = true
}

const deleteAttendance = async () => {
  deleting.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.delete(
      `/api/attendance/${selectedAttendanceToDelete.value.id}`,
      {
        headers: { 'Authorization': `Bearer ${token}` }
      }
    )

    if (response.data.success) {
      toast.success('Attendance record deleted successfully')
      deleteDialog.value = false
      selectedAttendanceToDelete.value = null
      fetchAttendance()
      fetchStats()
    } else {
      toast.error(response.data.message || 'Failed to delete attendance')
    }
  } catch (error) {
    console.error('Error deleting attendance:', error)
    toast.error(error.response?.data?.message || 'Failed to delete attendance')
  } finally {
    deleting.value = false
  }
}

const closeDialog = () => {
  dialog.value = false
  editingAttendance.value = null
  resetForm()
}

const resetForm = () => {
  form.value = {
    event_id: null,
    men: 0,
    women: 0,
    children: 0,
    visitors: 0,
    total: 0,
    notes: ''
  }
}

const updateTotal = () => {
  const men = parseInt(form.value.men) || 0
  const women = parseInt(form.value.women) || 0
  const children = parseInt(form.value.children) || 0
  const visitors = parseInt(form.value.visitors) || 0
  form.value.total = men + women + children + visitors
}

const onEventSelect = (eventId) => {
  const selectedEvent = events.value.find(e => e.id === eventId)
  if (selectedEvent) {
    // You could pre-fill some values based on the event if needed
  }
}

const filterByDate = (period) => {
  const now = new Date()
  let startDate = null
  let endDate = null

  switch (period) {
    case 'today':
      startDate = now.toISOString().split('T')[0]
      endDate = now.toISOString().split('T')[0]
      break
    case 'this_week':
      const startOfWeek = new Date(now)
      startOfWeek.setDate(now.getDate() - now.getDay())
      const endOfWeek = new Date(startOfWeek)
      endOfWeek.setDate(startOfWeek.getDate() + 6)
      startDate = startOfWeek.toISOString().split('T')[0]
      endDate = endOfWeek.toISOString().split('T')[0]
      break
    case 'this_month':
      const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1)
      const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0)
      startDate = startOfMonth.toISOString().split('T')[0]
      endDate = endOfMonth.toISOString().split('T')[0]
      break
  }

  if (startDate && endDate) {
    dateRange.value = [startDate, endDate]
    fetchAttendance()
  }
}

const exportAttendanceData = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/attendance/export', {
      headers: { 'Authorization': `Bearer ${token}` },
      responseType: 'blob'
    })

    const filename = `attendance_export_${new Date().toISOString().split('T')[0]}.xlsx`
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    link.remove()

    toast.success('Attendance data exported successfully')
  } catch (error) {
    console.error('Error exporting attendance:', error)
    toast.error('Failed to export attendance data')
  }
}

const viewAllEvents = () => {
  router.push('/events')
}

const viewEvent = (event) => {
  router.push(`/events/${event.id}`)
}

const prevMonth = () => {
  const date = new Date(calendarDate.value)
  date.setMonth(date.getMonth() - 1)
  calendarDate.value = date.toISOString().substring(0, 10)
}

const nextMonth = () => {
  const date = new Date(calendarDate.value)
  date.setMonth(date.getMonth() + 1)
  calendarDate.value = date.toISOString().substring(0, 10)
}

// Chart methods
const initCharts = async () => {
  await nextTick()

  // Destroy existing charts
  [compositionChart, trendsChart, dailyTrendsChart, weeklyTrendsChart,
   monthlyTrendsChart, comparisonChart, eventTypeChart].forEach(chartRef => {
    if (chartRef.value && chartRef.value.chart) {
      chartRef.value.chart.destroy()
    }
  })

  // Initialize composition chart
  if (compositionChart.value) {
    const ctx = compositionChart.value.getContext('2d')
    new Chart(ctx, {
      type: 'pie',
      data: {
        labels: ['Men', 'Women', 'Children', 'Visitors'],
        datasets: [{
          data: [stats.value.men_total, stats.value.women_total, stats.value.children_total, stats.value.visitors_total],
          backgroundColor: ['#1976D2', '#E91E63', '#FF9800', '#00BCD4'],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    })
  }

  // Initialize trends chart (simplified)
  // You would fetch trend data from your API
}
const getEventColor = (event) => {
  if (!event) return 'grey'
  const colors = {
    service: 'primary',
    midweek: 'secondary',
    prayer: 'success',
    bible_study: 'info',
    youth: 'warning',
    children: 'pink',
    women: 'purple',
    men: 'blue',
    outreach: 'teal',
    social: 'orange',
    training: 'indigo',
    conference: 'cyan',
    other: 'grey'
  }
  return colors[event.type] || 'grey'
}

const getEventIcon = (type) => {
  const icons = {
    service: 'mdi-church',
    midweek: 'mdi-calendar',
    prayer: 'mdi-hand-heart',
    bible_study: 'mdi-book-open-variant',
    youth: 'mdi-account-group',
    children: 'mdi-human-child',
    women: 'mdi-human-female',
    men: 'mdi-human-male',
    outreach: 'mdi-hand-heart',
    social: 'mdi-party-popper',
    training: 'mdi-school',
    conference: 'mdi-microphone',
    other: 'mdi-calendar'
  }
  return icons[type] || 'mdi-calendar'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num)
}

const getPercentage = (part, total) => {
  if (total === 0) return 0
  return Math.round((part / total) * 100)
}

// Debounced search
let searchTimeout = null
const debouncedFetchAttendance = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchAttendance()
  }, 500)
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    fetchAttendance(),
    fetchEvents(),
    fetchStats()
  ])
  await initCharts()
})

// Watchers
watch([eventFilter, categoryFilter], () => {
  fetchAttendance()
})

watch(dateRange, () => {
  if (dateRange.value[0] && dateRange.value[1]) {
    fetchAttendance()
  }
})

watch(() => stats.value, () => {
  initCharts()
}, { deep: true })
</script>

<style scoped>
.stats-card {
  cursor: pointer;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  height: 100%;
}

.stats-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.h-100 {
  height: 100%;
}

/* Custom scrollbar for tables */
:deep(.v-data-table) {
  scrollbar-width: thin;
  scrollbar-color: #ccc transparent;
}

:deep(.v-data-table)::-webkit-scrollbar {
  height: 8px;
  width: 8px;
}

:deep(.v-data-table)::-webkit-scrollbar-track {
  background: transparent;
}

:deep(.v-data-table)::-webkit-scrollbar-thumb {
  background-color: #ccc;
  border-radius: 4px;
}

/* Calendar customizations */
:deep(.v-calendar-month__day) {
  min-height: 80px;
}

/* Responsive adjustments */
@media (max-width: 600px) {
  .stats-card .v-avatar {
    width: 48px !important;
    height: 48px !important;
    min-width: 48px !important;
  }

  .stats-card .text-h5 {
    font-size: 1.25rem;
  }

  .v-card-title {
    flex-direction: column;
    align-items: flex-start !important;
  }
}
</style>
