<template>
  <div>
    <!-- Page Header with Breadcrumbs -->
    <v-breadcrumbs class="mb-4" :items="breadcrumbs" divider=">"></v-breadcrumbs>

    <!-- Page Header with Actions -->
    <div class="d-flex justify-space-between align-center mb-6">
      <div>
        <h1 class="text-h4 font-weight-bold">Events</h1>
        <p class="text-body-1 text-medium-emphasis mt-1">
          Manage church events and schedules ({{ events.length }} total)
        </p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <v-btn color="primary" prepend-icon="mdi-calendar-plus" @click="openCreateDialog" class="mb-1">
          Add Event
        </v-btn>
        <v-btn variant="outlined" prepend-icon="mdi-calendar" @click="calendarView = !calendarView" class="mb-1">
          {{ calendarView ? 'List View' : 'Calendar View' }}
        </v-btn>
        <v-btn variant="outlined" prepend-icon="mdi-download" @click="exportEvents" class="mb-1">
          Export
        </v-btn>
        <v-btn variant="outlined" prepend-icon="mdi-bullhorn" @click="sendNotifications" class="mb-1">
          Notify
        </v-btn>
      </div>
    </div>

    <!-- Stats Cards -->
    <v-row class="mb-6">
      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card" @click="filterEvents('upcoming')">
          <v-card-text class="d-flex align-center">
            <v-avatar color="primary" size="56" class="mr-4">
              <v-icon size="32">mdi-calendar</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ upcomingEvents.length }}</div>
              <div class="text-caption text-medium-emphasis">Upcoming Events</div>
              <div v-if="nextEvent" class="text-caption text-success mt-1">
                Next: {{ formatDateTime(nextEvent.start_date) }}
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card" @click="filterEvents('past')">
          <v-card-text class="d-flex align-center">
            <v-avatar color="grey" size="56" class="mr-4">
              <v-icon size="32">mdi-calendar-check</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ pastEvents.length }}</div>
              <div class="text-caption text-medium-emphasis">Past Events</div>
              <div class="text-caption text-medium-emphasis mt-1">
                Last: {{ lastEvent ? formatDateTime(lastEvent.start_date) : 'None' }}
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card" @click="showAttendanceStats = true">
          <v-card-text class="d-flex align-center">
            <v-avatar color="success" size="56" class="mr-4">
              <v-icon size="32">mdi-chart-line</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ averageAttendance }}</div>
              <div class="text-caption text-medium-emphasis">Avg Attendance</div>
              <div class="text-caption text-success mt-1">
                {{ attendanceTrend >= 0 ? '↑' : '↓' }} {{ Math.abs(attendanceTrend) }}% trend
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card" @click="filterEvents('this_month')">
          <v-card-text class="d-flex align-center">
            <v-avatar color="warning" size="56" class="mr-4">
              <v-icon size="32">mdi-calendar-star</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ thisMonthEvents.length }}</div>
              <div class="text-caption text-medium-emphasis">This Month</div>
              <div class="text-caption text-warning mt-1">
                {{ eventsToday.length }} today
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Quick Filter Chips -->
    <div class="d-flex flex-wrap gap-2 mb-4">
      <v-chip
        v-for="filter in eventFilters"
        :key="filter.value"
        :color="filter.active ? 'primary' : 'default'"
        @click="toggleEventFilter(filter)"
        class="cursor-pointer"
      >
        {{ filter.label }}
      </v-chip>
      <v-chip
        v-if="activeEventFilter"
        color="warning"
        @click="clearEventFilters"
        class="cursor-pointer"
      >
        Clear Filters
      </v-chip>
    </div>

    <!-- Calendar View -->
    <div v-if="calendarView" class="mb-6">
      <v-card>
        <v-card-title class="d-flex justify-space-between align-center">
          <div class="d-flex align-center">
            <v-btn icon @click="prevMonth">
              <v-icon>mdi-chevron-left</v-icon>
            </v-btn>
            <h3 class="text-h5 mx-4">{{ calendarTitle }}</h3>
            <v-btn icon @click="nextMonth">
              <v-icon>mdi-chevron-right</v-icon>
            </v-btn>
          </div>
          <v-btn @click="calendarView = false" variant="text">
            List View
          </v-btn>
        </v-card-title>
        <v-card-text>
          <v-calendar
            ref="calendar"
            v-model="calendarFocus"
            :events="calendarEvents"
            type="month"
            @click:event="viewEventFromCalendar"
            @click:date="viewDayEvents"
          ></v-calendar>
        </v-card-text>
      </v-card>
    </div>

    <!-- List View -->
    <div v-else>
      <!-- Search and Filter Bar -->
      <v-card class="mb-6">
        <v-card-text>
          <v-row align="center">
            <v-col cols="12" md="4">
              <v-text-field
                v-model="search"
                placeholder="Search events by title, description, location..."
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                density="comfortable"
                hide-details
                @update:model-value="debouncedFetchEvents"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="3">
              <v-select
                v-model="eventTypeFilter"
                :items="eventTypeOptions"
                item-title="title"
                item-value="value"
                label="Event Type"
                variant="outlined"
                density="comfortable"
                hide-details
                clearable
                @update:model-value="fetchEvents"
              ></v-select>
            </v-col>

            <v-col cols="12" md="3">
              <v-select
                v-model="sortBy"
                :items="sortOptions"
                label="Sort By"
                variant="outlined"
                density="comfortable"
                hide-details
                @update:model-value="fetchEvents"
              ></v-select>
            </v-col>

            <v-col cols="12" md="2">
              <v-btn variant="tonal" color="primary" @click="resetFilters" block>
                Reset Filters
              </v-btn>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>

      <!-- Events Table -->
      <v-card>
        <v-card-text>
          <!-- Loading State -->
          <div v-if="loading" class="text-center py-12">
            <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
            <p class="mt-4 text-medium-emphasis">Loading events...</p>
          </div>

          <!-- Empty State -->
          <div v-else-if="filteredEvents.length === 0" class="text-center py-12">
            <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-calendar-remove</v-icon>
            <h3 class="text-h6 mb-2">No events found</h3>
            <p class="text-medium-emphasis mb-4">
              {{ search || activeEventFilter ? 'Try changing your search or filters' : 'Schedule your first event to get started' }}
            </p>
            <v-btn color="primary" @click="openCreateDialog">
              Schedule First Event
            </v-btn>
          </div>

          <!-- Events List -->
          <div v-else>
            <v-list lines="three">
              <v-list-item
                v-for="event in filteredEvents"
                :key="event.id"
                @click="viewEvent(event)"
                class="mb-2 event-item"
                :class="{ 'event-ongoing': isEventOngoing(event) }"
              >
                <template #prepend>
                  <v-avatar :color="getEventColor(event)" size="56" class="mr-4">
                    <v-icon color="white">{{ getEventIcon(event) }}</v-icon>
                  </v-avatar>
                </template>

                <template #title>
                  <div class="d-flex justify-space-between align-start">
                    <h3 class="text-h6 font-weight-medium">{{ event.title }}</h3>
                    <div class="d-flex gap-1">
                      <v-chip size="small" :color="getEventColor(event)" variant="tonal">
                        {{ event.type }}
                      </v-chip>
                      <v-chip size="small" :color="getEventStatusColor(event)">
                        {{ getEventStatus(event) }}
                      </v-chip>
                    </div>
                  </div>
                </template>

                <template #subtitle>
                  <div class="d-flex flex-column mt-2">
                    <div class="d-flex align-center mb-1">
                      <v-icon size="16" class="mr-1" color="primary">mdi-clock</v-icon>
                      <span>{{ formatDateTime(event.start_date) }} - {{ formatDateTime(event.end_date) }}</span>
                    </div>
                    <div v-if="event.location" class="d-flex align-center mb-1">
                      <v-icon size="16" class="mr-1" color="primary">mdi-map-marker</v-icon>
                      <span>{{ event.location }}</span>
                    </div>
                    <div v-if="event.description" class="text-caption text-medium-emphasis mt-1">
                      {{ truncateText(event.description, 100) }}
                    </div>
                    <div class="d-flex align-center mt-2">
                      <v-chip size="x-small" variant="outlined" class="mr-2">
                        <v-icon size="14" class="mr-1">mdi-account-group</v-icon>
                        {{ event.total_attendance || 0 }} attended
                      </v-chip>
                      <v-chip v-if="event.category" size="x-small" variant="outlined">
                        {{ event.category }}
                      </v-chip>
                    </div>
                  </div>
                </template>

                <template #append>
                  <div class="d-flex flex-column gap-1">
                    <v-tooltip text="Record Attendance">
                      <template #activator="{ props }">
                        <v-btn
                          v-bind="props"
                          icon
                          size="small"
                          variant="text"
                          color="success"
                          @click.stop="openAttendanceDialog(event)"
                        >
                          <v-icon>mdi-account-check</v-icon>
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
                          color="primary"
                          @click.stop="editEvent(event)"
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
                          @click.stop="deleteEventPrompt(event)"
                        >
                          <v-icon>mdi-delete</v-icon>
                        </v-btn>
                      </template>
                    </v-tooltip>
                  </div>
                </template>
              </v-list-item>
            </v-list>

            <!-- Pagination -->
            <v-row v-if="filteredEvents.length > 0" class="mt-4">
              <v-col cols="12" md="6" class="d-flex align-center">
                <div class="text-body-2 text-medium-emphasis">
                  Showing {{ filteredEvents.length }} events
                  <v-chip size="x-small" class="ml-2" color="primary" variant="outlined">
                    {{ upcomingEvents.length }} upcoming, {{ pastEvents.length }} past
                  </v-chip>
                </div>
              </v-col>
              <v-col cols="12" md="6">
                <v-pagination
                  v-if="pagination.last_page > 1"
                  v-model="pagination.current_page"
                  :length="pagination.last_page"
                  :total-visible="5"
                  @update:model-value="fetchEvents"
                  class="justify-end"
                ></v-pagination>
              </v-col>
            </v-row>
          </div>
        </v-card-text>
      </v-card>
    </div>

    <!-- Create/Edit Event Dialog -->
    <v-dialog v-model="dialog" max-width="800" scrollable persistent>
      <v-card>
        <v-card-title class="d-flex justify-space-between align-center">
          <span class="text-h5">{{ editingEvent ? 'Edit Event' : 'Create New Event' }}</span>
          <v-btn icon @click="closeDialog">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text>
          <v-form ref="eventForm" @submit.prevent="saveEvent">
            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="form.title"
                  label="Event Title *"
                  variant="outlined"
                  :rules="[v => !!v || 'Event title is required']"
                  required
                  placeholder="e.g., Sunday Service, Youth Meeting, Christmas Celebration"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-select
                  v-model="form.type"
                  :items="eventTypeOptions"
                  item-title="title"
                  item-value="value"
                  label="Event Type *"
                  variant="outlined"
                  :rules="[v => !!v || 'Event type is required']"
                  required
                ></v-select>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.category"
                  label="Category"
                  variant="outlined"
                  placeholder="e.g., Worship, Outreach, Fellowship"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formStartDate"
                  label="Start Date *"
                  type="date"
                  variant="outlined"
                  :rules="[v => !!v || 'Start date is required']"
                  required
                  @update:model-value="updateStartDateTime"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formStartTime"
                  label="Start Time *"
                  type="time"
                  variant="outlined"
                  :rules="[v => !!v || 'Start time is required']"
                  required
                  @update:model-value="updateStartDateTime"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formEndDate"
                  label="End Date *"
                  type="date"
                  variant="outlined"
                  :rules="[v => !!v || 'End date is required']"
                  required
                  @update:model-value="updateEndDateTime"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formEndTime"
                  label="End Time *"
                  type="time"
                  variant="outlined"
                  :rules="[v => !!v || 'End time is required']"
                  required
                  @update:model-value="updateEndDateTime"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-text-field
                  v-model="form.location"
                  label="Location"
                  variant="outlined"
                  placeholder="e.g., Main Sanctuary, Fellowship Hall, Online"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="form.description"
                  label="Description"
                  variant="outlined"
                  rows="3"
                  placeholder="Event details, agenda, special instructions..."
                ></v-textarea>
              </v-col>

              <v-col cols="12">
                <v-switch
                  v-model="form.recurring"
                  label="Recurring Event"
                  color="primary"
                  hide-details
                ></v-switch>

                <div v-if="form.recurring" class="mt-4">
                  <v-select
                    v-model="form.recurrence_pattern"
                    :items="recurrencePatterns"
                    label="Repeat"
                    variant="outlined"
                  ></v-select>

                  <v-text-field
                    v-if="form.recurrence_pattern"
                    v-model="form.recurrence_end_date"
                    label="Repeat Until"
                    type="date"
                    variant="outlined"
                    class="mt-2"
                  ></v-text-field>
                </div>
              </v-col>

              <v-col cols="12">
                <v-divider class="my-4"></v-divider>
                <h4 class="text-subtitle-1 mb-2">Event Settings</h4>
                <v-row>
                  <v-col cols="12" md="6">
                    <v-switch
                      v-model="form.send_notifications"
                      label="Send Notifications"
                      color="primary"
                      hide-details
                    ></v-switch>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-switch
                      v-model="form.track_attendance"
                      label="Track Attendance"
                      color="primary"
                      hide-details
                    ></v-switch>
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>

        <v-card-actions class="px-6 pb-4">
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="closeDialog">
            Cancel
          </v-btn>
          <v-btn color="primary" @click="saveEvent" :loading="saving">
            {{ editingEvent ? 'Update Event' : 'Create Event' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- View Event Dialog -->
    <v-dialog v-model="viewDialog" max-width="800">
      <v-card v-if="selectedEvent">
        <v-card-title class="d-flex justify-space-between align-center">
          <div class="d-flex align-center">
            <v-avatar :color="getEventColor(selectedEvent)" size="48" class="mr-3">
              <v-icon color="white">{{ getEventIcon(selectedEvent) }}</v-icon>
            </v-avatar>
            <div>
              <h2 class="text-h5">{{ selectedEvent.title }}</h2>
              <div class="d-flex align-center mt-1">
                <v-chip size="small" :color="getEventColor(selectedEvent)" class="mr-2">
                  {{ selectedEvent.type }}
                </v-chip>
                <v-chip size="small" :color="getEventStatusColor(selectedEvent)">
                  {{ getEventStatus(selectedEvent) }}
                </v-chip>
              </div>
            </div>
          </div>
          <v-btn icon @click="viewDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-divider></v-divider>

        <v-tabs v-model="viewTab" color="primary" class="px-4">
          <v-tab value="details">Details</v-tab>
          <v-tab value="attendance">Attendance</v-tab>
          <v-tab value="resources">Resources</v-tab>
        </v-tabs>

        <v-divider></v-divider>

        <v-card-text>
          <v-window v-model="viewTab">
            <v-window-item value="details">
              <v-row class="mt-2">
                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">
                      <v-icon size="16" class="mr-1">mdi-clock</v-icon>
                      Start Time
                    </div>
                    <div class="text-body-1">{{ formatDateTime(selectedEvent.start_date) }}</div>
                  </div>
                </v-col>

                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">
                      <v-icon size="16" class="mr-1">mdi-clock</v-icon>
                      End Time
                    </div>
                    <div class="text-body-1">{{ formatDateTime(selectedEvent.end_date) }}</div>
                  </div>
                </v-col>

                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">
                      <v-icon size="16" class="mr-1">mdi-map-marker</v-icon>
                      Location
                    </div>
                    <div class="text-body-1">{{ selectedEvent.location || 'Not specified' }}</div>
                  </div>
                </v-col>

                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">
                      <v-icon size="16" class="mr-1">mdi-tag</v-icon>
                      Category
                    </div>
                    <div class="text-body-1">{{ selectedEvent.category || 'Not specified' }}</div>
                  </div>
                </v-col>

                <v-col cols="12">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">
                      <v-icon size="16" class="mr-1">mdi-text</v-icon>
                      Description
                    </div>
                    <div class="text-body-1">{{ selectedEvent.description || 'No description provided' }}</div>
                  </div>
                </v-col>

                <v-col cols="12">
                  <v-divider class="my-2"></v-divider>
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">Event Statistics</div>
                    <v-row class="mt-2">
                      <v-col cols="6" md="3">
                        <div class="text-center">
                          <div class="text-h6">{{ selectedEvent.total_attendance || 0 }}</div>
                          <div class="text-caption">Total Attendance</div>
                        </div>
                      </v-col>
                      <v-col cols="6" md="3">
                        <div class="text-center">
                          <div class="text-h6">{{ getDaysUntil(selectedEvent.start_date) }}</div>
                          <div class="text-caption">Days Until</div>
                        </div>
                      </v-col>
                      <v-col cols="6" md="3">
                        <div class="text-center">
                          <div class="text-h6">{{ getEventDuration(selectedEvent) }}</div>
                          <div class="text-caption">Duration (hours)</div>
                        </div>
                      </v-col>
                      <v-col cols="6" md="3">
                        <div class="text-center">
                          <div class="text-h6">{{ selectedEvent.attendances?.length || 0 }}</div>
                          <div class="text-caption">Attendance Records</div>
                        </div>
                      </v-col>
                    </v-row>
                  </div>
                </v-col>
              </v-row>
            </v-window-item>

            <v-window-item value="attendance">
              <div v-if="selectedEvent.attendances?.length > 0">
                <v-table density="compact">
                  <thead>
                    <tr>
                      <th>Date Recorded</th>
                      <th>Men</th>
                      <th>Women</th>
                      <th>Children</th>
                      <th>Visitors</th>
                      <th>Total</th>
                      <th>Recorded By</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="attendance in selectedEvent.attendances" :key="attendance.id">
                      <td>{{ formatDateTime(attendance.created_at) }}</td>
                      <td>{{ attendance.men || 0 }}</td>
                      <td>{{ attendance.women || 0 }}</td>
                      <td>{{ attendance.children || 0 }}</td>
                      <td>{{ attendance.visitors || 0 }}</td>
                      <td><strong>{{ attendance.total || 0 }}</strong></td>
                      <td>{{ attendance.recorded_by || 'System' }}</td>
                    </tr>
                  </tbody>
                </v-table>

                <v-row class="mt-4">
                  <v-col cols="12" md="6">
                    <v-card variant="outlined">
                      <v-card-title class="text-subtitle-1">Attendance Summary</v-card-title>
                      <v-card-text>
                        <div class="d-flex justify-space-between mb-2">
                          <span>Men:</span>
                          <strong>{{ getAttendanceTotal('men') }}</strong>
                        </div>
                        <div class="d-flex justify-space-between mb-2">
                          <span>Women:</span>
                          <strong>{{ getAttendanceTotal('women') }}</strong>
                        </div>
                        <div class="d-flex justify-space-between mb-2">
                          <span>Children:</span>
                          <strong>{{ getAttendanceTotal('children') }}</strong>
                        </div>
                        <div class="d-flex justify-space-between mb-2">
                          <span>Visitors:</span>
                          <strong>{{ getAttendanceTotal('visitors') }}</strong>
                        </div>
                        <v-divider class="my-2"></v-divider>
                        <div class="d-flex justify-space-between">
                          <span>Total:</span>
                          <strong class="text-h6">{{ getAttendanceTotal('total') }}</strong>
                        </div>
                      </v-card-text>
                    </v-card>
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-card variant="outlined">
                      <v-card-title class="text-subtitle-1">Quick Actions</v-card-title>
                      <v-card-text class="text-center">
                        <v-btn color="success" @click="openAttendanceDialog(selectedEvent)" class="mb-2" block>
                          <v-icon left>mdi-account-plus</v-icon>
                          Record New Attendance
                        </v-btn>
                        <v-btn color="primary" @click="exportAttendance(selectedEvent)" block>
                          <v-icon left>mdi-download</v-icon>
                          Export Attendance
                        </v-btn>
                      </v-card-text>
                    </v-card>
                  </v-col>
                </v-row>
              </div>

              <div v-else class="text-center py-8">
                <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-account-group-off</v-icon>
                <h3 class="text-h6 mb-2">No Attendance Records</h3>
                <p class="text-medium-emphasis mb-4">No attendance has been recorded for this event yet.</p>
                <v-btn color="success" @click="openAttendanceDialog(selectedEvent)">
                  <v-icon left>mdi-account-check</v-icon>
                  Record First Attendance
                </v-btn>
              </div>
            </v-window-item>

            <v-window-item value="resources">
              <div class="text-center py-8">
                <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-file-document</v-icon>
                <h3 class="text-h6 mb-2">Event Resources</h3>
                <p class="text-medium-emphasis">Upload presentations, documents, or media related to this event.</p>
                <v-btn color="primary" class="mt-2">
                  <v-icon left>mdi-upload</v-icon>
                  Upload Resources
                </v-btn>
              </div>
            </v-window-item>
          </v-window>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 pb-4">
          <v-btn color="primary" @click="editEvent(selectedEvent)">
            <v-icon left>mdi-pencil</v-icon>
            Edit Event
          </v-btn>
          <v-btn color="success" @click="openAttendanceDialog(selectedEvent)" v-if="isEventOngoing(selectedEvent) || isEventPast(selectedEvent)">
            <v-icon left>mdi-account-check</v-icon>
            Record Attendance
          </v-btn>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="viewDialog = false">
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Attendance Dialog -->
    <v-dialog v-model="attendanceDialog" max-width="500">
      <v-card v-if="selectedEvent">
        <v-card-title class="d-flex justify-space-between align-center">
          <span class="text-h5">Record Attendance - {{ selectedEvent.title }}</span>
          <v-btn icon @click="attendanceDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>
          <!-- <v-form ref="attendanceForm" v-model="attendanceFormValid"> -->
          <v-form ref="attendanceFormRef" v-model="attendanceFormValid">
            <v-row>
              <v-col cols="6">
                <v-text-field
                  v-model.number="attendanceForm.men"
                  label="Men"
                  type="number"
                  variant="outlined"
                  :rules="[v => v == null || v === '' || v >= 0 || 'Must be a positive number']"
                  min="0"
                  @input="updateAttendanceTotal"
                ></v-text-field>
              </v-col>
              <v-col cols="6">
                <v-text-field
                  v-model.number="attendanceForm.women"
                  label="Women"
                  type="number"
                  variant="outlined"
                  :rules="[v => v == null || v === '' || v >= 0 || 'Must be a positive number']"
                  min="0"
                  @input="updateAttendanceTotal"
                ></v-text-field>
              </v-col>
              <v-col cols="6">
                <v-text-field
                  v-model.number="attendanceForm.children"
                  label="Children"
                  type="number"
                  variant="outlined"
                  :rules="[v => v == null || v === '' || v >= 0 || 'Must be a positive number']"
                  min="0"
                  @input="updateAttendanceTotal"
                ></v-text-field>
              </v-col>
              <v-col cols="6">
                <v-text-field
                  v-model.number="attendanceForm.visitors"
                  label="Visitors"
                  type="number"
                  variant="outlined"
                  :rules="[v => v == null || v === '' || v >= 0 || 'Must be a positive number']"
                  min="0"
                  @input="updateAttendanceTotal"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  v-model="attendanceForm.total"
                  label="Total"
                  readonly
                  variant="outlined"
                  hint="Automatically calculated"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-textarea
                  v-model="attendanceForm.notes"
                  label="Notes"
                  variant="outlined"
                  rows="2"
                  placeholder="Any additional notes about the attendance..."
                ></v-textarea>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue-darken-1" variant="text" @click="attendanceDialog = false">
            Cancel
          </v-btn>
          <v-btn color="success" variant="text" @click="saveAttendance" :loading="savingAttendance">
            Save Attendance
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title class="text-h5">Delete Event</v-card-title>
        <v-card-text>
          Are you sure you want to delete "<strong>{{ selectedEventToDelete?.title }}</strong>"?
          This action cannot be undone.
          <v-alert v-if="selectedEventToDelete?.attendances?.length > 0" type="warning" class="mt-4">
            This event has {{ selectedEventToDelete.attendances.length }} attendance records that will also be deleted.
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey-darken-1" variant="text" @click="deleteDialog = false">
            Cancel
          </v-btn>
          <v-btn color="error" variant="text" @click="deleteEvent" :loading="deleting">
            Delete
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Attendance Stats Dialog -->
    <v-dialog v-model="showAttendanceStats" max-width="600">
      <v-card>
        <v-card-title>Attendance Statistics</v-card-title>
        <v-card-text>
          <v-row>
            <v-col cols="6" v-for="stat in attendanceStatistics" :key="stat.label">
              <v-card variant="outlined" class="text-center pa-3">
                <div class="text-h4 font-weight-bold">{{ stat.value }}</div>
                <div class="text-caption text-medium-emphasis">{{ stat.label }}</div>
              </v-card>
            </v-col>
          </v-row>

          <v-divider class="my-4"></v-divider>

          <h4 class="text-subtitle-1 mb-2">Top Events by Attendance</h4>
          <v-list>
            <v-list-item v-for="event in topEventsByAttendance" :key="event.id">
              <template #prepend>
                <v-avatar :color="getEventColor(event)" size="40">
                  <v-icon color="white" size="20">{{ getEventIcon(event) }}</v-icon>
                </v-avatar>
              </template>
              <v-list-item-title>{{ event.title }}</v-list-item-title>
              <v-list-item-subtitle>{{ formatDate(event.start_date) }}</v-list-item-subtitle>
              <template #append>
                <v-chip size="small" color="primary">
                  {{ event.total_attendance || 0 }}
                </v-chip>
              </template>
            </v-list-item>
          </v-list>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="showAttendanceStats = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import * as XLSX from 'xlsx'
import { saveAs } from 'file-saver'
import { useAuthStore } from '../../stores/auth'

const toast = useToast()
const auth = useAuthStore()

// Data
const loading = ref(false)
const saving = ref(false)
const savingAttendance = ref(false)
const deleting = ref(false)
const dialog = ref(false)
const deleteDialog = ref(false)
const viewDialog = ref(false)
const attendanceDialog = ref(false)
const showAttendanceStats = ref(false)
const calendarView = ref(false)
const search = ref('')
const eventTypeFilter = ref('')
const sortBy = ref('start_date_desc')
const events = ref([])
const selectedEvent = ref(null)
const selectedEventToDelete = ref(null)
const editingEvent = ref(null)
const calendarFocus = ref(new Date().toISOString().substring(0, 10))
const viewTab = ref('details')
const attendanceFormValid = ref(false)

// Pagination
const pagination = ref({
  current_page: 1,
  per_page: 10,
  total: 0,
  last_page: 1
})

// Form
const form = ref({
  title: '',
  type: '',
  category: '',
  start_date: '',
  end_date: '',
  location: '',
  description: '',
  recurring: false,
  recurrence_pattern: '',
  recurrence_end_date: '',
  send_notifications: true,
  track_attendance: true
})

const formStartDate = ref('')
const formStartTime = ref('09:00')
const formEndDate = ref('')
const formEndTime = ref('11:00')

const attendanceForm = ref({
  men: 0,
  women: 0,
  children: 0,
  visitors: 0,
  total: 0,
  notes: ''
})

// Options
const eventTypeOptions = ref([
  { title: 'Service', value: 'service' },
  { title: 'Meeting', value: 'meeting' },
  { title: 'Outreach', value: 'outreach' },
  { title: 'Social', value: 'social' },
  { title: 'Youth', value: 'youth' },
  { title: 'Children', value: 'children' },
  { title: 'Women', value: 'women' },
  { title: 'Men', value: 'men' },
  { title: 'Prayer', value: 'prayer' },
  { title: 'Bible Study', value: 'bible_study' },
  { title: 'Training', value: 'training' },
  { title: 'Conference', value: 'conference' },
  { title: 'Other', value: 'other' }
])

const sortOptions = ref([
  { title: 'Start Date (Newest)', value: 'start_date_desc' },
  { title: 'Start Date (Oldest)', value: 'start_date_asc' },
  { title: 'Title A-Z', value: 'title_asc' },
  { title: 'Title Z-A', value: 'title_desc' },
  { title: 'Most Attendance', value: 'attendance_desc' },
  { title: 'Least Attendance', value: 'attendance_asc' }
])

const recurrencePatterns = ref([
  'Daily',
  'Weekly',
  'Bi-weekly',
  'Monthly',
  'Yearly'
])

// Breadcrumbs
const breadcrumbs = ref([
  { title: 'Dashboard', to: '/' },
  { title: 'Events', disabled: true }
])

// Quick filters
const eventFilters = ref([
  { label: 'Upcoming', value: 'upcoming', active: false },
  { label: 'Past', value: 'past', active: false },
  { label: 'This Week', value: 'this_week', active: false },
  { label: 'This Month', value: 'this_month', active: false },
  { label: 'Today', value: 'today', active: false }
])

// Form refs
const eventForm = ref(null)
const attendanceFormRef = ref(null)

// Computed properties
const filteredEvents = computed(() => {
  let filtered = events.value

  // Apply active filters
  if (activeEventFilter.value === 'upcoming') {
    filtered = filtered.filter(e => new Date(e.start_date) >= new Date())
  } else if (activeEventFilter.value === 'past') {
    filtered = filtered.filter(e => new Date(e.end_date) < new Date())
  } else if (activeEventFilter.value === 'this_week') {
    const now = new Date()
    const startOfWeek = new Date(now.setDate(now.getDate() - now.getDay()))
    const endOfWeek = new Date(now.setDate(now.getDate() + 6))
    filtered = filtered.filter(e => {
      const eventDate = new Date(e.start_date)
      return eventDate >= startOfWeek && eventDate <= endOfWeek
    })
  } else if (activeEventFilter.value === 'this_month') {
    const now = new Date()
    const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1)
    const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0)
    filtered = filtered.filter(e => {
      const eventDate = new Date(e.start_date)
      return eventDate >= startOfMonth && eventDate <= endOfMonth
    })
  } else if (activeEventFilter.value === 'today') {
    const today = new Date().toDateString()
    filtered = filtered.filter(e => new Date(e.start_date).toDateString() === today)
  }

  // Apply event type filter
  if (eventTypeFilter.value) {
    filtered = filtered.filter(e => e.type === eventTypeFilter.value)
  }

  // Apply search
  if (search.value) {
    const searchTerm = search.value.toLowerCase()
    filtered = filtered.filter(e =>
      e.title.toLowerCase().includes(searchTerm) ||
      (e.description && e.description.toLowerCase().includes(searchTerm)) ||
      (e.location && e.location.toLowerCase().includes(searchTerm)) ||
      e.type.toLowerCase().includes(searchTerm)
    )
  }

  // Apply sorting
  filtered = [...filtered].sort((a, b) => {
    switch (sortBy.value) {
      case 'start_date_desc':
        return new Date(b.start_date) - new Date(a.start_date)
      case 'start_date_asc':
        return new Date(a.start_date) - new Date(b.start_date)
      case 'title_asc':
        return a.title.localeCompare(b.title)
      case 'title_desc':
        return b.title.localeCompare(a.title)
      case 'attendance_desc':
        return (b.total_attendance || 0) - (a.total_attendance || 0)
      case 'attendance_asc':
        return (a.total_attendance || 0) - (b.total_attendance || 0)
      default:
        return new Date(b.start_date) - new Date(a.start_date)
    }
  })

  return filtered
})

const upcomingEvents = computed(() => {
  return events.value.filter(e => new Date(e.start_date) >= new Date())
})

const pastEvents = computed(() => {
  return events.value.filter(e => new Date(e.end_date) < new Date())
})

const nextEvent = computed(() => {
  return upcomingEvents.value.sort((a, b) => new Date(a.start_date) - new Date(b.start_date))[0] || null
})

const lastEvent = computed(() => {
  return pastEvents.value.sort((a, b) => new Date(b.start_date) - new Date(a.start_date))[0] || null
})

const thisMonthEvents = computed(() => {
  const now = new Date()
  const currentMonth = now.getMonth()
  const currentYear = now.getFullYear()

  return events.value.filter(e => {
    const eventDate = new Date(e.start_date)
    return eventDate.getMonth() === currentMonth && eventDate.getFullYear() === currentYear
  })
})

const eventsToday = computed(() => {
  const today = new Date().toDateString()
  return events.value.filter(e => new Date(e.start_date).toDateString() === today)
})

const calendarEvents = computed(() => {
  return events.value.map(e => ({
    name: e.title,
    start: new Date(e.start_date),
    end: new Date(e.end_date),
    timed: true,
    color: getEventColor(e)
  }))
})

const calendarTitle = computed(() => {
  const date = new Date(calendarFocus.value)
  return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
})

const activeEventFilter = computed(() => {
  const active = eventFilters.value.find(f => f.active)
  return active ? active.value : null
})

const averageAttendance = computed(() => {
  if (events.value.length === 0) return 0
  const total = events.value.reduce((sum, e) => sum + (e.total_attendance || 0), 0)
  return Math.round(total / events.value.length)
})

const attendanceTrend = computed(() => {
  // Simplified trend calculation
  const recentEvents = events.value.slice(0, 5)
  if (recentEvents.length < 2) return 0

  const first = recentEvents[0].total_attendance || 0
  const last = recentEvents[recentEvents.length - 1].total_attendance || 0

  if (first === 0) return 100
  return Math.round(((last - first) / first) * 100)
})

const attendanceStatistics = computed(() => {
  const totalEvents = events.value.length
  const eventsWithAttendance = events.value.filter(e => e.total_attendance > 0).length
  const totalAttendance = events.value.reduce((sum, e) => sum + (e.total_attendance || 0), 0)
  const avgPerEvent = totalEvents > 0 ? Math.round(totalAttendance / totalEvents) : 0

  return [
    { label: 'Total Events', value: totalEvents },
    { label: 'Events with Attendance', value: eventsWithAttendance },
    { label: 'Total Attendance', value: totalAttendance },
    { label: 'Average per Event', value: avgPerEvent }
  ]
})

const topEventsByAttendance = computed(() => {
  return [...events.value]
    .sort((a, b) => (b.total_attendance || 0) - (a.total_attendance || 0))
    .slice(0, 5)
})

// Methods
const fetchEvents = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')

    if (!token) {
      toast.error('No authentication token found. Please login.')
      return
    }

    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
      search: search.value,
      type: eventTypeFilter.value,
      sort_by: sortBy.value
    }

    const response = await axios.get('/api/events', {
      headers: { 'Authorization': `Bearer ${token}` },
      params: params
    })

    console.log('Events API Response:', response.data)

    if (response.data.success) {
      events.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page || 1,
        per_page: response.data.per_page || 10,
        total: response.data.total || events.value.length,
        last_page: response.data.last_page || 1
      }
    } else {
      toast.error(response.data.message || 'Failed to load events')
    }
  } catch (error) {
    console.error('Error fetching events:', error)

    // Fallback with sample data
    events.value = [
      {
        id: 1,
        title: 'Sunday Morning Service',
        type: 'service',
        category: 'Worship',
        start_date: new Date(Date.now() + 86400000).toISOString(),
        end_date: new Date(Date.now() + 86400000 + 7200000).toISOString(),
        location: 'Main Sanctuary',
        description: 'Regular Sunday worship service with communion',
        total_attendance: 150,
        attendances: [
          { id: 1, men: 70, women: 65, children: 15, visitors: 10, total: 160, recorded_by: 'Admin' }
        ],
        created_at: '2023-01-15T10:30:00Z',
        updated_at: '2023-01-15T10:30:00Z'
      },
      {
        id: 2,
        title: 'Youth Bible Study',
        type: 'youth',
        category: 'Bible Study',
        start_date: new Date(Date.now() + 172800000).toISOString(),
        end_date: new Date(Date.now() + 172800000 + 5400000).toISOString(),
        location: 'Youth Room',
        description: 'Weekly youth gathering for Bible study and fellowship',
        total_attendance: 45,
        attendances: [
          { id: 2, men: 20, women: 20, children: 0, visitors: 5, total: 45, recorded_by: 'Youth Leader' }
        ],
        created_at: '2023-02-10T14:20:00Z',
        updated_at: '2023-02-10T14:20:00Z'
      }
    ]
    pagination.value = {
      current_page: 1,
      per_page: 10,
      total: 2,
      last_page: 1
    }

    toast.error('Failed to load events. Showing sample data.')
  } finally {
    loading.value = false
  }
}

const openCreateDialog = () => {
  resetForm()
  editingEvent.value = null
  dialog.value = true

  // Set default dates
  const now = new Date()
  const oneHourLater = new Date(now.getTime() + 60 * 60 * 1000)

  formStartDate.value = now.toISOString().split('T')[0]
  formStartTime.value = now.toHoursMinutes()
  formEndDate.value = oneHourLater.toISOString().split('T')[0]
  formEndTime.value = oneHourLater.toHoursMinutes()

  updateStartDateTime()
  updateEndDateTime()
}

const editEvent = (event) => {
  editingEvent.value = event
  form.value = { ...event }

  // Parse dates
  if (event.start_date) {
    const startDate = new Date(event.start_date)
    formStartDate.value = startDate.toISOString().split('T')[0]
    formStartTime.value = startDate.toHoursMinutes()
    updateStartDateTime()
  }

  if (event.end_date) {
    const endDate = new Date(event.end_date)
    formEndDate.value = endDate.toISOString().split('T')[0]
    formEndTime.value = endDate.toHoursMinutes()
    updateEndDateTime()
  }

  dialog.value = true
}

const viewEvent = (event) => {
  selectedEvent.value = event
  viewTab.value = 'details'
  viewDialog.value = true
}

const viewEventFromCalendar = ({ event }) => {
  const foundEvent = events.value.find(e => e.title === event.name)
  if (foundEvent) {
    viewEvent(foundEvent)
  }
}

const viewDayEvents = ({ date }) => {
  const dayEvents = events.value.filter(e => {
    const eventDate = new Date(e.start_date).toDateString()
    return eventDate === new Date(date).toDateString()
  })

  if (dayEvents.length > 0) {
    toast.info(`${dayEvents.length} events on ${formatDate(date)}`)
  } else {
    toast.info(`No events scheduled for ${formatDate(date)}`)
  }
}

const deleteEventPrompt = (event) => {
  selectedEventToDelete.value = event
  deleteDialog.value = true
}

const openAttendanceDialog = (event) => {
  selectedEvent.value = event
  resetAttendanceForm()

  // Load existing attendance if available
  if (event.attendances?.length > 0) {
    const latest = event.attendances[event.attendances.length - 1]
    attendanceForm.value = {
      men: latest.men || 0,
      women: latest.women || 0,
      children: latest.children || 0,
      visitors: latest.visitors || 0,
      total: latest.total || 0,
      notes: latest.notes || ''
    }
  }

  attendanceDialog.value = true
}

const saveEvent = async () => {
  if (!eventForm.value) return

  const { valid } = await eventForm.value.validate()
  if (!valid) {
    toast.error('Please fill in all required fields correctly')
    return
  }

  saving.value = true

  try {
    const token = localStorage.getItem('token')

    if (!token) {
      toast.error('No authentication token found')
      return
    }

    // Prepare payload
    const payload = {
      title: form.value.title,
      type: form.value.type,
      category: form.value.category,
      start_date: form.value.start_date,
      end_date: form.value.end_date,
      location: form.value.location,
      description: form.value.description,
      recurring: form.value.recurring,
      recurrence_pattern: form.value.recurrence_pattern,
      recurrence_end_date: form.value.recurrence_end_date,
      send_notifications: form.value.send_notifications,
      track_attendance: form.value.track_attendance,
      church_id: auth.church?.id
    }

    let response

    if (editingEvent.value) {
      response = await axios.put(
        `/api/events/${editingEvent.value.id}`,
        payload,
        {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        }
      )
    } else {
      response = await axios.post(
        '/api/events',
        payload,
        {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        }
      )
    }

    if (response.data.success) {
      toast.success(response.data.message || 'Event saved successfully')
      closeDialog()
      fetchEvents()
    } else {
      toast.error(response.data.message || 'Failed to save event')
    }
  } catch (error) {
    console.error('Error saving event:', error)

    if (error.response?.status === 422) {
      const errors = error.response.data.errors
      Object.keys(errors).forEach(key => {
        toast.error(`${key}: ${errors[key][0]}`)
      })
    } else {
      toast.error(error.response?.data?.message || 'Failed to save event. Please try again.')
    }
  } finally {
    saving.value = false
  }
}

const saveAttendance = async () => {
  if (!attendanceFormRef.value) return

  const { valid } = await attendanceFormRef.value.validate()
  if (!valid) {
    toast.error('Please fill in all fields correctly')
    return
  }

  savingAttendance.value = true

  try {
    const token = localStorage.getItem('token')

    if (!token || !selectedEvent.value) {
      toast.error('Missing required information')
      return
    }

    const payload = {
      men: attendanceForm.value.men || 0,
      women: attendanceForm.value.women || 0,
      children: attendanceForm.value.children || 0,
      visitors: attendanceForm.value.visitors || 0,
      total: attendanceForm.value.total || 0,
      notes: attendanceForm.value.notes || '',
      event_id: selectedEvent.value.id
    }

    const response = await axios.post(
      `/api/events/${selectedEvent.value.id}/attend`,
      payload,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      }
    )

    if (response.data.success) {
      toast.success('Attendance recorded successfully')
      attendanceDialog.value = false
      fetchEvents()

      // Refresh selected event if viewing
      if (viewDialog.value) {
        const updatedEvent = events.value.find(e => e.id === selectedEvent.value.id)
        if (updatedEvent) {
          selectedEvent.value = updatedEvent
        }
      }
    } else {
      toast.error(response.data.message || 'Failed to record attendance')
    }
  } catch (error) {
    console.error('Error saving attendance:', error)
    toast.error(error.response?.data?.message || 'Failed to record attendance')
  } finally {
    savingAttendance.value = false
  }
}

const deleteEvent = async () => {
  deleting.value = true

  try {
    const token = localStorage.getItem('token')

    if (!token || !selectedEventToDelete.value) {
      toast.error('Missing required information')
      return
    }

    const response = await axios.delete(
      `/api/events/${selectedEventToDelete.value.id}`,
      {
        headers: { Authorization: `Bearer ${token}` }
      }
    )

    if (response.data.success) {
      toast.success('Event deleted successfully')
      deleteDialog.value = false
      selectedEventToDelete.value = null
      fetchEvents()
    } else {
      toast.error(response.data.message || 'Failed to delete event')
    }
  } catch (error) {
    console.error('Error deleting event:', error)
    toast.error(error.response?.data?.message || 'Failed to delete event')
  } finally {
    deleting.value = false
  }
}

const closeDialog = () => {
  dialog.value = false
  editingEvent.value = null
  resetForm()
}

const resetForm = () => {
  form.value = {
    title: '',
    type: '',
    category: '',
    start_date: '',
    end_date: '',
    location: '',
    description: '',
    recurring: false,
    recurrence_pattern: '',
    recurrence_end_date: '',
    send_notifications: true,
    track_attendance: true
  }

  formStartDate.value = ''
  formStartTime.value = '09:00'
  formEndDate.value = ''
  formEndTime.value = '11:00'
}

const resetAttendanceForm = () => {
  attendanceForm.value = {
    men: 0,
    women: 0,
    children: 0,
    visitors: 0,
    total: 0,
    notes: ''
  }
}

const updateStartDateTime = () => {
  if (formStartDate.value && formStartTime.value) {
    form.value.start_date = `${formStartDate.value} ${formStartTime.value}:00`
  }
}

const updateEndDateTime = () => {
  if (formEndDate.value && formEndTime.value) {
    form.value.end_date = `${formEndDate.value} ${formEndTime.value}:00`
  }
}

const updateAttendanceTotal = () => {
  const men = parseInt(attendanceForm.value.men) || 0
  const women = parseInt(attendanceForm.value.women) || 0
  const children = parseInt(attendanceForm.value.children) || 0
  const visitors = parseInt(attendanceForm.value.visitors) || 0
  attendanceForm.value.total = men + women + children + visitors
}

const prevMonth = () => {
  const date = new Date(calendarFocus.value)
  date.setMonth(date.getMonth() - 1)
  calendarFocus.value = date.toISOString().substring(0, 10)
}

const nextMonth = () => {
  const date = new Date(calendarFocus.value)
  date.setMonth(date.getMonth() + 1)
  calendarFocus.value = date.toISOString().substring(0, 10)
}

const toggleEventFilter = (filter) => {
  // Deactivate all other filters
  eventFilters.value.forEach(f => {
    f.active = f.value === filter.value ? !f.active : false
  })
}

const clearEventFilters = () => {
  eventFilters.value.forEach(f => f.active = false)
}

const filterEvents = (type) => {
  eventFilters.value.forEach(f => {
    f.active = f.value === type
  })
}

const resetFilters = () => {
  search.value = ''
  eventTypeFilter.value = ''
  sortBy.value = 'start_date_desc'
  clearEventFilters()
  fetchEvents()
}

const exportEvents = async () => {
  try {
    const token = localStorage.getItem('token')

    // Fetch all events
    const response = await axios.get('/api/events', {
      headers: { Authorization: `Bearer ${token}` },
      params: {
        per_page: 10000,
        search: search.value,
        type: eventTypeFilter.value
      }
    })

    let data = []
    if (response.data.success) {
      data = response.data.data
    } else {
      data = events.value
    }

    const exportData = data.map(e => ({
      'ID': e.id,
      'Title': e.title,
      'Type': e.type,
      'Category': e.category || '',
      'Start Date': formatDateTime(e.start_date),
      'End Date': formatDateTime(e.end_date),
      'Location': e.location || '',
      'Description': e.description || '',
      'Total Attendance': e.total_attendance || 0,
      'Status': getEventStatus(e),
      'Created At': formatDateTime(e.created_at),
      'Updated At': formatDateTime(e.updated_at)
    }))

    const worksheet = XLSX.utils.json_to_sheet(exportData)
    const workbook = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Events')

    // Auto-size columns
    const wscols = Object.keys(exportData[0] || {}).map(() => ({ wch: 20 }))
    worksheet['!cols'] = wscols

    // Generate Excel file
    const excelBuffer = XLSX.write(workbook, { bookType: 'xlsx', type: 'array' })
    const blob = new Blob([excelBuffer], { type: 'application/octet-stream' })

    const filename = `Church_Events_${new Date().toISOString().split('T')[0]}.xlsx`
    saveAs(blob, filename)

    toast.success(`Exported ${exportData.length} events successfully!`)

  } catch (error) {
    console.error('Error exporting events:', error)
    toast.error('Failed to export events. Please try again.')
  }
}

const exportAttendance = (event) => {
  if (!event.attendances || event.attendances.length === 0) {
    toast.info('No attendance records to export')
    return
  }

  const exportData = event.attendances.map(a => ({
    'Event': event.title,
    'Date Recorded': formatDateTime(a.created_at),
    'Men': a.men || 0,
    'Women': a.women || 0,
    'Children': a.children || 0,
    'Visitors': a.visitors || 0,
    'Total': a.total || 0,
    'Recorded By': a.recorded_by || 'System',
    'Notes': a.notes || ''
  }))

  const worksheet = XLSX.utils.json_to_sheet(exportData)
  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Attendance')

  const excelBuffer = XLSX.write(workbook, { bookType: 'xlsx', type: 'array' })
  const blob = new Blob([excelBuffer], { type: 'application/octet-stream' })

  const filename = `Attendance_${event.title.replace(/[^a-z0-9]/gi, '_')}_${new Date().toISOString().split('T')[0]}.xlsx`
  saveAs(blob, filename)

  toast.success(`Exported ${exportData.length} attendance records!`)
}

const sendNotifications = () => {
  toast.info('Notification feature coming soon!')
}

const formatDate = (dateString, format = 'standard') => {
  if (!dateString) return 'N/A'

  try {
    const date = new Date(dateString)

    if (format === 'short') {
      return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      })
    }

    if (format === 'time') {
      return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  } catch {
    return 'Invalid Date'
  }
}

const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'

  try {
    const date = new Date(dateString)
    return date.toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return 'Invalid Date'
  }
}

const truncateText = (text, length) => {
  if (!text) return ''
  if (text.length <= length) return text
  return text.substring(0, length) + '...'
}

const getEventColor = (event) => {
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

const getEventIcon = (event) => {
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
  return icons[event.type] || 'mdi-calendar'
}

const getEventStatus = (event) => {
  const now = new Date()
  const start = new Date(event.start_date)
  const end = new Date(event.end_date)

  if (now < start) return 'Upcoming'
  if (now >= start && now <= end) return 'Ongoing'
  return 'Past'
}

const getEventStatusColor = (event) => {
  const status = getEventStatus(event)
  const colors = {
    'Upcoming': 'success',
    'Ongoing': 'warning',
    'Past': 'grey'
  }
  return colors[status] || 'grey'
}

const isEventOngoing = (event) => {
  const now = new Date()
  const start = new Date(event.start_date)
  const end = new Date(event.end_date)
  return now >= start && now <= end
}

const isEventPast = (event) => {
  const now = new Date()
  const end = new Date(event.end_date)
  return now > end
}

const getDaysUntil = (dateString) => {
  if (!dateString) return 'N/A'

  try {
    const eventDate = new Date(dateString)
    const now = new Date()
    const diff = eventDate - now
    const days = Math.ceil(diff / (1000 * 60 * 60 * 24))

    if (days < 0) return 'Past'
    if (days === 0) return 'Today'
    if (days === 1) return 'Tomorrow'
    return `${days} days`
  } catch {
    return 'N/A'
  }
}

const getEventDuration = (event) => {
  if (!event.start_date || !event.end_date) return 'N/A'

  try {
    const start = new Date(event.start_date)
    const end = new Date(event.end_date)
    const duration = (end - start) / (1000 * 60 * 60) // hours
    return Math.round(duration * 10) / 10
  } catch {
    return 'N/A'
  }
}

const getAttendanceTotal = (type) => {
  if (!selectedEvent.value?.attendances) return 0

  return selectedEvent.value.attendances.reduce((total, attendance) => {
    return total + (attendance[type] || 0)
  }, 0)
}

// Add toHoursMinutes method to Date prototype
Date.prototype.toHoursMinutes = function() {
  const hours = this.getHours().toString().padStart(2, '0')
  const minutes = this.getMinutes().toString().padStart(2, '0')
  return `${hours}:${minutes}`
}

// Debounced search
let searchTimeout = null
const debouncedFetchEvents = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchEvents()
  }, 500)
}

// Lifecycle
onMounted(async () => {
  await fetchEvents()
})

// Watch for changes
watch([eventTypeFilter, sortBy], () => {
  pagination.value.current_page = 1
  fetchEvents()
})

watch(calendarFocus, () => {
  // You could filter events for the selected month here
})
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

.cursor-pointer {
  cursor: pointer;
}

.info-item {
  padding: 8px 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.info-item:last-child {
  border-bottom: none;
}

.event-item {
  border-radius: 8px;
  transition: background-color 0.2s ease;
}

.event-item:hover {
  background-color: rgba(var(--v-theme-primary), 0.04);
}

.event-ongoing {
  border-left: 4px solid var(--v-warning-base);
  background-color: rgba(var(--v-warning-base), 0.05);
}

/* Calendar customizations */
:deep(.v-calendar-month__day) {
  min-height: 100px;
}

:deep(.v-calendar-month__day-content) {
  height: 100%;
}

/* Responsive adjustments */
@media (max-width: 600px) {
  .v-card-title {
    flex-direction: column;
    align-items: flex-start !important;
  }

  .v-card-title .d-flex {
    margin-top: 8px;
  }

  .stats-card .v-avatar {
    width: 48px !important;
    height: 48px !important;
    min-width: 48px !important;
  }

  .stats-card .text-h5 {
    font-size: 1.25rem;
  }

  .event-item .v-list-item__prepend {
    margin-right: 12px !important;
  }

  .event-item .v-list-item__append {
    margin-left: 12px !important;
  }
}
</style>
