<template>
  <div>
    <!-- Page Header with Breadcrumbs -->
    <v-breadcrumbs class="mb-4" :items="breadcrumbs" divider=">"></v-breadcrumbs>

    <!-- Page Header with Actions -->
    <div class="d-flex justify-space-between align-center mb-6">
      <div>
        <h1 class="text-h4 font-weight-bold">Members</h1>
        <p class="text-body-1 text-medium-emphasis mt-1">
          Manage your church members database ({{ pagination.total }} total)
        </p>
      </div>
      <div class="d-flex gap-2 flex-wrap">

        <!-- Add this to your stats cards row -->
        <v-btn
          color="primary"
          prepend-icon="mdi-account-plus"
          @click="openCreateDialog"
          class="mb-1"
        >
          Add Member
        </v-btn>
        <v-btn
          variant="outlined"
          prepend-icon="mdi-download"
          @click="exportMembers"
          class="mb-1"
        >
          Export
        </v-btn>
        <v-btn
          variant="outlined"
          prepend-icon="mdi-printer"
          @click="printMembers"
          class="mb-1"
        >
          Print
        </v-btn>
        <v-btn
          variant="outlined"
          prepend-icon="mdi-upload"
          @click="openImportDialog"
          class="mb-1"
        >
          Import
        </v-btn>
      </div>
    </div>

    <!-- Stats Cards -->
    <v-row class="mb-6">
      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card" @click="showAllMembers">
          <v-card-text class="d-flex align-center">
            <v-avatar color="primary" size="56" class="mr-4">
              <v-icon size="32">mdi-account-group</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ stats.total || 0 }}</div>
              <div class="text-caption text-medium-emphasis">Total Members</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card" @click="filterActiveMembers">
          <v-card-text class="d-flex align-center">
            <v-avatar color="success" size="56" class="mr-4">
              <v-icon size="32">mdi-account-check</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ stats.active || 0 }}</div>
              <div class="text-caption text-medium-emphasis">Active Members</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card" @click="filterVisitors">
          <v-card-text class="d-flex align-center">
            <v-avatar color="warning" size="56" class="mr-4">
              <v-icon size="32">mdi-account-clock</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ stats.visitors || 0 }}</div>
              <div class="text-caption text-medium-emphasis">Visitors</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="stats-card" @click="filterNewThisMonth">
          <v-card-text class="d-flex align-center">
            <v-avatar color="info" size="56" class="mr-4">
              <v-icon size="32">mdi-chart-pie</v-icon>
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ stats.new_this_month || 0 }}</div>
              <div class="text-caption text-medium-emphasis">New This Month</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>


    <!-- Quick Filter Chips -->
    <div class="d-flex flex-wrap gap-2 mb-4">
      <v-chip
        v-for="filter in quickFilters"
        :key="filter.value"
        :color="filter.active ? 'primary' : 'default'"
        @click="applyQuickFilter(filter)"
        class="cursor-pointer"
      >
        {{ filter.label }}
      </v-chip>
      <v-chip
        v-if="hasActiveFilters"
        color="warning"
        @click="resetAllFilters"
        class="cursor-pointer"
      >
        Clear All Filters
      </v-chip>
    </div>

    <!-- Advanced Filters -->
    <v-expansion-panels class="mb-6" variant="accordion">
      <v-expansion-panel>
        <v-expansion-panel-title>
          <v-icon left>mdi-filter</v-icon>
          Advanced Filters
          <v-badge v-if="activeFilterCount > 0" color="primary" :content="activeFilterCount" inline class="ml-2"></v-badge>
        </v-expansion-panel-title>
        <v-expansion-panel-text>
          <v-row>
            <v-col cols="12" md="3">
              <v-select
                v-model="advancedFilters.gender"
                :items="genderOptions"
                label="Gender"
                item-title="title"
                item-value="value"
                clearable
                multiple
                chips
              ></v-select>
            </v-col>
            <v-col cols="12" md="3">
              <v-select
                v-model="advancedFilters.marital_status"
                :items="maritalStatusOptions"
                label="Marital Status"
                item-title="title"
                item-value="value"
                clearable
                multiple
                chips
              ></v-select>
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="advancedFilters.city"
                label="City"
                clearable
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="advancedFilters.occupation"
                label="Occupation"
                clearable
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="advancedFilters.joinDateRange.start"
                label="Join Date From"
                type="date"
                clearable
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="advancedFilters.joinDateRange.end"
                label="Join Date To"
                type="date"
                clearable
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="advancedFilters.birthYear"
                label="Birth Year"
                type="number"
                clearable
                min="1900"
                :max="new Date().getFullYear()"
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <div class="d-flex justify-end gap-2">
                <v-btn @click="resetAdvancedFilters" variant="text" color="error">
                  Clear Filters
                </v-btn>
                <v-btn @click="applyAdvancedFilters" color="primary">
                  Apply Filters
                </v-btn>
              </div>
            </v-col>
          </v-row>
        </v-expansion-panel-text>
      </v-expansion-panel>
    </v-expansion-panels>

    <!-- Search and Filter Bar -->
    <v-card class="mb-6">
      <v-card-text>
        <v-row align="center">
          <v-col cols="12" md="4">
            <v-text-field
              v-model="search"
              placeholder="Search members by name, email, phone, occupation..."
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="comfortable"
              hide-details
              @update:model-value="debouncedFetchMembers"
            ></v-text-field>
          </v-col>

          <v-col cols="12" md="3">
            <v-select
              v-model="statusFilter"
              :items="statusOptions"
              label="Status"
              item-title="title"
              item-value="value"
              variant="outlined"
              density="comfortable"
              hide-details
              clearable
              @update:model-value="fetchMembers"
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
              @update:model-value="fetchMembers"
            ></v-select>
          </v-col>

          <v-col cols="12" md="2">
            <v-btn
              variant="tonal"
              color="primary"
              @click="resetAllFilters"
              block
            >
              Reset All
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Members Table -->
    <v-card>
      <v-card-text>
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
          <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
          <p class="mt-4 text-medium-emphasis">Loading members...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="members.length === 0" class="text-center py-12">
          <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-account-group-off</v-icon>
          <h3 class="text-h6 mb-2">No members found</h3>
          <p class="text-medium-emphasis mb-4">
            {{ hasActiveFilters ? 'Try changing your search or filters' : 'Add your first member to get started' }}
          </p>
          <v-btn color="primary" @click="openCreateDialog">
            Add First Member
          </v-btn>
        </div>

        <!-- Members Table -->
        <v-table v-else density="comfortable" hover>
          <thead>
            <tr>
              <th>Name</th>
              <th>Contact</th>
              <th>Join Date</th>
              <th>Status</th>
              <th>Age</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="member in members" :key="member.id" @click="viewMember(member)" class="cursor-pointer">
              <td>
                <div class="d-flex align-center">
                  <v-avatar size="36" class="mr-3" :color="getAvatarColor(member)">
                    <span class="text-white">{{ getInitials(member) }}</span>
                  </v-avatar>
                  <div>
                    <div class="font-weight-medium">{{ member.first_name }} {{ member.last_name }}</div>
                    <div class="text-caption text-medium-emphasis">
                      {{ member.occupation || 'No occupation' }}
                      <v-chip v-if="member.gender" size="x-small" class="ml-1">{{ formatGender(member.gender) }}</v-chip>
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <div>
                  <v-icon size="16" class="mr-1" color="primary">mdi-email</v-icon>
                  {{ member.email || 'No email' }}
                </div>
                <div class="text-caption text-medium-emphasis mt-1">
                  <v-icon size="16" class="mr-1" color="primary">mdi-phone</v-icon>
                  {{ member.phone || 'No phone' }}
                </div>
              </td>
              <td>
                <div class="d-flex flex-column">
                  <span>{{ formatDate(member.join_date) }}</span>
                  <span class="text-caption text-medium-emphasis">
                    {{ calculateDuration(member.join_date) }}
                  </span>
                </div>
              </td>
              <td>
                <v-chip
                  :color="getStatusColor(member.membership_status)"
                  size="small"
                  :variant="member.membership_status === 'active' ? 'elevated' : 'flat'"
                >
                  {{ formatStatus(member.membership_status) }}
                </v-chip>
                <div v-if="member.marital_status" class="text-caption mt-1">
                  {{ formatMaritalStatus(member.marital_status) }}
                </div>
              </td>
              <td>
                <div class="d-flex flex-column">
                  <span>{{ calculateAge(member.birth_date) }}</span>
                  <span v-if="member.birth_date" class="text-caption text-medium-emphasis">
                    {{ formatDate(member.birth_date, 'short') }}
                  </span>
                </div>
              </td>
              <td class="text-right">
                <div class="d-flex justify-end gap-1">
                  <v-tooltip text="Edit">
                    <template #activator="{ props }">
                      <v-btn
                        v-bind="props"
                        icon
                        size="small"
                        variant="text"
                        color="primary"
                        @click.stop="editMember(member)"
                      >
                        <v-icon size="18">mdi-pencil</v-icon>
                      </v-btn>
                    </template>
                  </v-tooltip>

                  <v-tooltip text="View">
                    <template #activator="{ props }">
                      <v-btn
                        v-bind="props"
                        icon
                        size="small"
                        variant="text"
                        color="info"
                        @click.stop="viewMember(member)"
                      >
                        <v-icon size="18">mdi-eye</v-icon>
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
                        @click.stop="deleteMember(member)"
                      >
                        <v-icon size="18">mdi-delete</v-icon>
                      </v-btn>
                    </template>
                  </v-tooltip>
                </div>
              </td>
            </tr>
          </tbody>
        </v-table>

        <!-- Pagination -->
        <v-row v-if="members.length > 0" class="mt-4">
          <v-col cols="12" md="6" class="d-flex align-center">
            <div class="text-body-2 text-medium-emphasis">
              Showing {{ members.length }} of {{ pagination.total }} members
              <v-chip size="x-small" class="ml-2" color="primary" variant="outlined">
                Page {{ pagination.current_page }} of {{ pagination.last_page }}
              </v-chip>
            </div>
          </v-col>
          <v-col cols="12" md="6">
            <v-pagination
              v-if="pagination.last_page > 1"
              v-model="pagination.current_page"
              :length="pagination.last_page"
              :total-visible="5"
              @update:model-value="fetchMembers"
              class="justify-end"
            ></v-pagination>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Create/Edit Dialog -->
    <v-dialog v-model="dialog" max-width="800" scrollable persistent>
      <v-card>
        <v-card-title class="d-flex justify-space-between align-center">
          <span class="text-h5">{{ editingMember ? 'Edit Member' : 'Add New Member' }}</span>
          <v-btn icon @click="closeDialog">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text>
          <!-- Custom Stepper Indicator -->
          <div class="d-flex justify-center mb-6">
            <div class="d-flex align-center">
              <template v-for="(step, index) in stepperSteps" :key="index">
                <div class="d-flex flex-column align-center" style="min-width: 120px">
                  <v-avatar
                    :color="formStep > index + 1 ? 'primary' : formStep === index + 1 ? 'primary' : 'grey-lighten-2'"
                    size="32"
                    class="mb-2"
                  >
                    <v-icon v-if="formStep > index + 1" color="white">mdi-check</v-icon>
                    <span v-else class="text-white">{{ index + 1 }}</span>
                  </v-avatar>
                  <span class="text-caption" :class="formStep === index + 1 ? 'text-primary font-weight-bold' : 'text-grey'">
                    {{ step }}
                  </span>
                </div>
                <v-divider v-if="index < stepperSteps.length - 1" class="mx-4" style="width: 80px"></v-divider>
              </template>
            </div>
          </div>

          <!-- Step 1: Personal Info -->
          <div v-if="formStep === 1">
            <v-form ref="personalForm">
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.first_name"
                    label="First Name *"
                    variant="outlined"
                    :rules="[v => !!v || 'First name is required']"
                    required
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.last_name"
                    label="Last Name *"
                    variant="outlined"
                    :rules="[v => !!v || 'Last name is required']"
                    required
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-select
                    v-model="form.gender"
                    :items="genderOptions"
                    label="Gender"
                    item-title="title"
                    item-value="value"
                    variant="outlined"
                    clearable
                  ></v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-select
                    v-model="form.marital_status"
                    :items="maritalStatusOptions"
                    label="Marital Status"
                    item-title="title"
                    item-value="value"
                    variant="outlined"
                    clearable
                  ></v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.birth_date"
                    label="Birth Date"
                    type="date"
                    variant="outlined"
                    :max="new Date().toISOString().split('T')[0]"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.occupation"
                    label="Occupation"
                    variant="outlined"
                    placeholder="e.g., Software Developer, Teacher"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-form>
          </div>

          <!-- Step 2: Contact Info -->
          <div v-else-if="formStep === 2">
            <v-form ref="contactForm">
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.email"
                    label="Email"
                    type="email"
                    variant="outlined"
                    :rules="[v => !v || /.+@.+\..+/.test(v) || 'Email must be valid']"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.phone"
                    label="Phone"
                    variant="outlined"
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    v-model="form.address"
                    label="Address"
                    variant="outlined"
                    placeholder="Street address"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.city"
                    label="City"
                    variant="outlined"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.state"
                    label="State/Province"
                    variant="outlined"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="form.zip_code"
                    label="ZIP/Postal Code"
                    variant="outlined"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-form>
          </div>

          <!-- Step 3: Church Info -->
          <div v-else-if="formStep === 3">
            <v-form ref="churchForm">
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.join_date"
                    label="Join Date *"
                    type="date"
                    variant="outlined"
                    :rules="[v => !!v || 'Join date is required']"
                    required
                    :max="new Date().toISOString().split('T')[0]"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-select
                    v-model="form.membership_status"
                    :items="membershipStatusOptions"
                    label="Membership Status *"
                    item-title="title"
                    item-value="value"
                    variant="outlined"
                    :rules="[v => !!v || 'Membership status is required']"
                    required
                  ></v-select>
                </v-col>
                <v-col cols="12">
                  <v-textarea
                    v-model="form.notes"
                    label="Notes"
                    variant="outlined"
                    rows="3"
                    placeholder="Additional information about the member..."
                  ></v-textarea>
                </v-col>
              </v-row>
            </v-form>
          </div>

          <!-- Step 4: Review -->
          <div v-else-if="formStep === 4">
            <div class="review-section">
              <h3 class="text-h6 mb-4">Review Member Information</h3>
              <v-row>
                <v-col cols="12" md="6">
                  <v-card variant="outlined" class="mb-3">
                    <v-card-title class="text-subtitle-1">Personal Information</v-card-title>
                    <v-card-text>
                      <div><strong>Name:</strong> {{ form.first_name }} {{ form.last_name }}</div>
                      <div><strong>Gender:</strong> {{ form.gender ? formatGender(form.gender) : 'Not specified' }}</div>
                      <div><strong>Marital Status:</strong> {{ form.marital_status ? formatMaritalStatus(form.marital_status) : 'Not specified' }}</div>
                      <div><strong>Birth Date:</strong> {{ form.birth_date ? formatDate(form.birth_date) : 'Not specified' }}</div>
                      <div><strong>Occupation:</strong> {{ form.occupation || 'Not specified' }}</div>
                    </v-card-text>
                  </v-card>
                </v-col>
                <v-col cols="12" md="6">
                  <v-card variant="outlined" class="mb-3">
                    <v-card-title class="text-subtitle-1">Contact Information</v-card-title>
                    <v-card-text>
                      <div><strong>Email:</strong> {{ form.email || 'Not specified' }}</div>
                      <div><strong>Phone:</strong> {{ form.phone || 'Not specified' }}</div>
                      <div><strong>Address:</strong> {{ form.address || 'Not specified' }}</div>
                      <div><strong>City:</strong> {{ form.city || 'Not specified' }}</div>
                      <div><strong>State:</strong> {{ form.state || 'Not specified' }}</div>
                      <div><strong>ZIP Code:</strong> {{ form.zip_code || 'Not specified' }}</div>
                    </v-card-text>
                  </v-card>
                </v-col>
                <v-col cols="12">
                  <v-card variant="outlined">
                    <v-card-title class="text-subtitle-1">Church Information</v-card-title>
                    <v-card-text>
                      <div><strong>Join Date:</strong> {{ form.join_date ? formatDate(form.join_date) : 'Not specified' }}</div>
                      <div><strong>Membership Status:</strong> {{ form.membership_status ? formatStatus(form.membership_status) : 'Not specified' }}</div>
                      <div><strong>Notes:</strong> {{ form.notes || 'No notes' }}</div>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
            </div>
          </div>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-6 pb-4">
          <v-btn v-if="formStep > 1" variant="text" @click="formStep--">
            Back
          </v-btn>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="closeDialog">
            Cancel
          </v-btn>
          <v-btn v-if="formStep < 4" color="primary" @click="goToNextStep">
            Next
          </v-btn>
          <v-btn v-if="formStep === 4" color="primary" @click="saveMember" :loading="saving">
            {{ editingMember ? 'Update Member' : 'Save Member' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- View Member Dialog -->
    <v-dialog v-model="viewDialog" max-width="800">
      <v-card v-if="selectedMember">
        <v-card-title class="d-flex justify-space-between align-center">
          <div class="d-flex align-center">
            <v-avatar size="48" class="mr-3" :color="getAvatarColor(selectedMember)">
              <span class="text-white text-h6">{{ getInitials(selectedMember) }}</span>
            </v-avatar>
            <div>
              <h2 class="text-h5">{{ selectedMember.first_name }} {{ selectedMember.last_name }}</h2>
              <div class="d-flex align-center mt-1">
                <v-chip size="small" :color="getStatusColor(selectedMember.membership_status)" class="mr-2">
                  {{ formatStatus(selectedMember.membership_status) }}
                </v-chip>
                <span class="text-caption text-medium-emphasis">ID: {{ selectedMember.id }}</span>
              </div>
            </div>
          </div>
          <v-btn icon @click="viewDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-divider></v-divider>

        <v-tabs v-model="viewTab" color="primary" class="px-4">
          <v-tab value="personal">Personal</v-tab>
          <v-tab value="contact">Contact</v-tab>
          <v-tab value="church">Church</v-tab>
          <v-tab value="history">History</v-tab>
        </v-tabs>

        <v-divider></v-divider>

        <v-card-text>
          <v-window v-model="viewTab">
            <v-window-item value="personal">
              <v-row>
                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">Full Name</div>
                    <div class="text-body-1">{{ selectedMember.first_name }} {{ selectedMember.last_name }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">Gender</div>
                    <div class="text-body-1">{{ formatGender(selectedMember.gender) || 'Not specified' }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">Marital Status</div>
                    <div class="text-body-1">{{ formatMaritalStatus(selectedMember.marital_status) || 'Not specified' }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">Birth Date</div>
                    <div class="text-body-1">{{ formatDate(selectedMember.birth_date) }}</div>
                    <div class="text-caption text-medium-emphasis">
                      Age: {{ calculateAge(selectedMember.birth_date) }}
                    </div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">Occupation</div>
                    <div class="text-body-1">{{ selectedMember.occupation || 'Not specified' }}</div>
                  </div>
                </v-col>
              </v-row>
            </v-window-item>

            <v-window-item value="contact">
              <v-row>
                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">Email</div>
                    <div class="text-body-1">{{ selectedMember.email || 'Not specified' }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">Phone</div>
                    <div class="text-body-1">{{ selectedMember.phone || 'Not specified' }}</div>
                  </div>
                </v-col>
                <v-col cols="12">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">Address</div>
                    <div class="text-body-1">{{ selectedMember.address || 'Not specified' }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="4">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">City</div>
                    <div class="text-body-1">{{ selectedMember.city || 'Not specified' }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="4">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">State</div>
                    <div class="text-body-1">{{ selectedMember.state || 'Not specified' }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="4">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">ZIP Code</div>
                    <div class="text-body-1">{{ selectedMember.zip_code || 'Not specified' }}</div>
                  </div>
                </v-col>
              </v-row>
            </v-window-item>

            <v-window-item value="church">
              <v-row>
                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">Join Date</div>
                    <div class="text-body-1">{{ formatDate(selectedMember.join_date) }}</div>
                    <div class="text-caption text-medium-emphasis">
                      Member for {{ calculateDuration(selectedMember.join_date) }}
                    </div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">Membership Status</div>
                    <v-chip :color="getStatusColor(selectedMember.membership_status)" size="small">
                      {{ formatStatus(selectedMember.membership_status) }}
                    </v-chip>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">Created By</div>
                    <div class="text-body-1">{{ selectedMember.created_by || 'System' }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">Last Updated</div>
                    <div class="text-body-1">{{ formatDate(selectedMember.updated_at) }}</div>
                  </div>
                </v-col>
                <v-col cols="12">
                  <div class="info-item mb-3">
                    <div class="text-caption text-medium-emphasis">Notes</div>
                    <div class="text-body-1">{{ selectedMember.notes || 'No notes' }}</div>
                  </div>
                </v-col>
              </v-row>
            </v-window-item>

            <v-window-item value="history">
              <div class="text-center py-8">
                <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-history</v-icon>
                <h3 class="text-h6 mb-2">Member History</h3>
                <p class="text-medium-emphasis">Attendance and activity history coming soon...</p>
              </div>
            </v-window-item>
          </v-window>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="px-4 pb-4">
          <v-btn color="primary" @click="editMember(selectedMember)">
            <v-icon left>mdi-pencil</v-icon>
            Edit Member
          </v-btn>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="viewDialog = false">
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="500">
      <v-card>
        <v-card-title class="text-h5">Delete Member</v-card-title>
        <v-card-text>
          Are you sure you want to delete <strong>{{ selectedMemberToDelete?.first_name }} {{ selectedMemberToDelete?.last_name }}</strong>?
          This action cannot be undone.
          <v-alert v-if="selectedMemberToDelete?.membership_status === 'active'" type="warning" class="mt-4">
            This member is currently active. Consider changing status to "inactive" instead.
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="deleteDialog = false">
            Cancel
          </v-btn>
          <v-btn color="error" @click="confirmDelete" :loading="deleting">
            Delete
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Import Dialog -->
    <v-dialog v-model="importDialog" max-width="600">
      <v-card>
        <v-card-title>Import Members</v-card-title>
        <v-card-text>
          <v-alert type="info" class="mb-4">
            <template #title>
              Import Instructions
            </template>
            Download the template file, fill in member data, and upload it here.
          </v-alert>

          <v-file-input
            v-model="importFile"
            label="Choose CSV or Excel file"
            accept=".csv,.xlsx,.xls"
            prepend-icon="mdi-file"
            variant="outlined"
            @change="handleFileChange"
            clearable
          ></v-file-input>

          <div v-if="importPreview.length > 0" class="mt-4">
            <h4 class="text-subtitle-1 mb-2">Preview (first 5 rows):</h4>
            <v-table density="compact">
              <thead>
                <tr>
                  <th v-for="header in importHeaders" :key="header">{{ header }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, index) in importPreview" :key="index">
                  <td v-for="header in importHeaders" :key="header">{{ row[header] }}</td>
                </tr>
              </tbody>
            </v-table>
            <div class="text-caption text-medium-emphasis mt-2">
              Total rows to import: {{ importPreview.length }}
            </div>
          </div>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="downloadTemplate" variant="text" color="primary">
            Download Template
          </v-btn>
          <v-btn @click="importDialog = false" variant="text">
            Cancel
          </v-btn>
          <v-btn color="primary" @click="processImport" :loading="importing" :disabled="!importFile">
            Import
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Age Groups Dialog -->
    <v-dialog v-model="showAgeGroups" max-width="500">
      <v-card>
        <v-card-title>Age Distribution</v-card-title>
        <v-card-text>
          <v-list>
            <v-list-item v-for="(count, group) in ageGroups" :key="group">
              <template #prepend>
                <v-progress-circular
                  :model-value="(count / members.length) * 100"
                  :size="40"
                  :width="4"
                  color="primary"
                >
                  {{ Math.round((count / members.length) * 100) }}%
                </v-progress-circular>
              </template>
              <v-list-item-title>{{ group }}</v-list-item-title>
              <v-list-item-subtitle>{{ count }} members</v-list-item-subtitle>
            </v-list-item>
          </v-list>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="showAgeGroups = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useToast } from 'vue-toastification'
import { useRouter } from 'vue-router'
import axios from 'axios'
import * as XLSX from 'xlsx'
import { saveAs } from 'file-saver'
import { useAuthStore } from '../../stores/auth'

const toast = useToast()
const router = useRouter()
const auth = useAuthStore()

// Data
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const importing = ref(false)
const dialog = ref(false)
const deleteDialog = ref(false)
const viewDialog = ref(false)
const importDialog = ref(false)
const showAgeGroups = ref(false)
const search = ref('')
const statusFilter = ref('')
const sortBy = ref('created_at_desc')
const members = ref([])
const stats = ref({
  total: 0,
  active: 0,
  visitors: 0,
  new_this_month: 0
})
const selectedMember = ref(null)
const selectedMemberToDelete = ref(null)
const editingMember = ref(null)
const importFile = ref(null)
const importPreview = ref([])
const importHeaders = ref([])
const viewTab = ref('personal')
const formStep = ref(1)

// Pagination
const pagination = ref({
  current_page: 1,
  per_page: 15,
  total: 0,
  last_page: 1
})

// Form
const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  birth_date: '',
  join_date: new Date().toISOString().split('T')[0],
  address: '',
  city: '',
  state: '',
  zip_code: '',
  gender: '',
  marital_status: '',
  occupation: '',
  notes: '',
  membership_status: 'active',
  church_id: auth.church?.id || null
})

// Options
const statusOptions = ref([
  { title: 'Active', value: 'active' },
  { title: 'Inactive', value: 'inactive' },
  { title: 'Visitor', value: 'visitor' }
])

const sortOptions = ref([
  { title: 'Newest First', value: 'created_at_desc' },
  { title: 'Oldest First', value: 'created_at_asc' },
  { title: 'Name A-Z', value: 'name_asc' },
  { title: 'Name Z-A', value: 'name_desc' },
  { title: 'Join Date', value: 'join_date' },
  { title: 'Birth Date', value: 'birth_date' }
])

const genderOptions = ref([
  { title: 'Male', value: 'Male' },
  { title: 'Female', value: 'Female' },
  { title: 'Other', value: 'Other' }
])

const maritalStatusOptions = ref([
  { title: 'Single', value: 'Single' },
  { title: 'Married', value: 'Married' },
  { title: 'Divorced', value: 'Divorced' },
  { title: 'Widowed', value: 'Widowed' }
])

const membershipStatusOptions = ref([
  { title: 'Active', value: 'Active' },
  { title: 'Inactive', value: 'Inactive' },
  { title: 'Visitor', value: 'Visitor' }
])

const stepperSteps = ref(['Personal', 'Contact', 'Church', 'Review'])

// Breadcrumbs
const breadcrumbs = ref([
  { title: 'Dashboard', to: '/' },
  { title: 'Members', disabled: true }
])

// Quick filters
const quickFilters = ref([
  { label: 'Active Members', value: 'active', active: false },
  { label: 'New This Month', value: 'new_month', active: false },
  { label: 'Without Email', value: 'no_email', active: false },
  { label: 'Without Phone', value: 'no_phone', active: false },
  { label: 'Birthday This Month', value: 'birthday', active: false }
])

// Advanced filters
const advancedFilters = ref({
  gender: [],
  marital_status: [],
  city: '',
  occupation: '',
  joinDateRange: { start: '', end: '' },
  birthYear: ''
})

// Form refs
const personalForm = ref(null)
const contactForm = ref(null)
const churchForm = ref(null)

// Computed properties
const activeFilterCount = computed(() => {
  let count = 0
  if (advancedFilters.value.gender.length > 0) count++
  if (advancedFilters.value.marital_status.length > 0) count++
  if (advancedFilters.value.city) count++
  if (advancedFilters.value.occupation) count++
  if (advancedFilters.value.joinDateRange.start || advancedFilters.value.joinDateRange.end) count++
  if (advancedFilters.value.birthYear) count++
  if (statusFilter.value) count++
  if (search.value) count++
  return count
})

const hasActiveFilters = computed(() => {
  return activeFilterCount.value > 0
})

const ageGroups = computed(() => {
  const groups = {
    '0-18': 0,
    '19-35': 0,
    '36-50': 0,
    '51-65': 0,
    '65+': 0
  }

  members.value.forEach(member => {
    const age = calculateAge(member.birth_date)
    if (age <= 18) groups['0-18']++
    else if (age <= 35) groups['19-35']++
    else if (age <= 50) groups['36-50']++
    else if (age <= 65) groups['51-65']++
    else groups['65+']++
  })

  return groups
})


// Remove stats update from fetchMembers entirely
const fetchMembers = async () => {
  loading.value = true
  try {
    // ... existing code ...

    if (response.data.success) {
      members.value = response.data.data || []

      // Update pagination ONLY, not stats
      if (response.data.meta) {
        pagination.value = {
          current_page: response.data.meta.current_page || 1,
          per_page: response.data.meta.per_page || 15,
          total: response.data.meta.total || 0,
          last_page: response.data.meta.last_page || 1
        }
      }
      // DO NOT update stats here!

    } else {
      members.value = []
      toast.error(response.data.message || 'Failed to fetch members')
    }

  } catch (error) {
    console.error('Error fetching members:', error)
    handleError(error)
    members.value = []
  } finally {
    loading.value = false
  }
}

// Create a separate function for stats that only fetches once
const fetchStats = async (force = false) => {
  // Only fetch stats once unless forced
  if (stats.value.total > 0 && !force) {
    return
  }

  try {
    const token = localStorage.getItem('token')
    if (!token) return

    // Use the dedicated stats endpoint if available
    try {
      const response = await axios.get('/api/members/stats', {
        headers: { 'Authorization': `Bearer ${token}` }
      })

      if (response.data.success && response.data.data) {
        stats.value = {
          total: response.data.data.total || 0,
          active: response.data.data.active || 0,
          visitors: response.data.data.visitors || 0,
          new_this_month: response.data.data.new_this_month || 0
        }
        return
      }
    } catch (error) {
      console.log('Stats endpoint not available')
    }

    // Fallback: fetch first page without filters to get stats
    const response = await axios.get('/api/members', {
      headers: { 'Authorization': `Bearer ${token}` },
      params: { per_page: 1, page: 1 }
    })

    if (response.data.success && response.data.stats) {
      stats.value = response.data.stats
    }

  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

// Update card click handlers to NOT trigger stats refresh
const filterActiveMembers = () => {
  resetAllFilters()
  statusFilter.value = 'active'
  quickFilters.value.forEach(f => {
    f.active = f.value === 'active'
  })
  fetchMembers() // Don't call fetchStats here
}

const filterVisitors = () => {
  resetAllFilters()
  statusFilter.value = 'visitor'
  quickFilters.value.forEach(f => {
    f.active = f.value === 'visitor'
  })
  fetchMembers() // Don't call fetchStats here
}

const filterNewThisMonth = () => {
  resetAllFilters()
  const now = new Date()
  const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
  const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0)
  advancedFilters.value.joinDateRange.start = firstDay.toISOString().split('T')[0]
  advancedFilters.value.joinDateRange.end = lastDay.toISOString().split('T')[0]
  quickFilters.value.forEach(f => {
    f.active = f.value === 'new_month'
  })
  fetchMembers() // Don't call fetchStats here
}

const showAllMembers = () => {
  resetAllFilters()
  fetchMembers()
  // Only fetch stats when showing all members
  fetchStats(true)
}

  // Update your fetchMembers function
const fetchMembers = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')

    if (!token) {
      toast.error('No authentication token found. Please login.')
      router.push('/login')
      return
    }

    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
      sort_by: sortBy.value,
      search: search.value,
      status: statusFilter.value,
      ...advancedFilters.value
    }

    // Clean up params
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null || params[key] === undefined ||
          (Array.isArray(params[key]) && params[key].length === 0)) {
        delete params[key]
      }
    })

    const response = await axios.get('/api/members', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      params: params
    })

    if (response.data.success) {
      members.value = response.data.data || []

      // Update pagination
      if (response.data.meta) {
        pagination.value = {
          current_page: response.data.meta.current_page || 1,
          per_page: response.data.meta.per_page || 15,
          total: response.data.meta.total || 0,
          last_page: response.data.meta.last_page || 1
        }
      }

      // IMPORTANT: Only update stats if we're viewing ALL members (no filters)
      // This prevents stats from changing when filtering
      const hasNoFilters = !search.value &&
                          !statusFilter.value &&
                          !advancedFilters.value.gender.length &&
                          !advancedFilters.value.marital_status.length &&
                          !advancedFilters.value.city &&
                          !advancedFilters.value.occupation &&
                          !advancedFilters.value.joinDateRange.start &&
                          !advancedFilters.value.joinDateRange.end &&
                          !advancedFilters.value.birthYear;

      if (hasNoFilters && response.data.stats) {
        stats.value = response.data.stats
        console.log('Updating stats (no filters)')
      } else if (hasNoFilters) {
        computeLocalStats()
      }
      // If filters are active, DON'T update stats - keep showing total counts

    } else {
      members.value = []
      toast.error(response.data.message || 'Failed to fetch members')
    }

  } catch (error) {
    console.error('Error fetching members:', error)
    handleError(error)
    members.value = []
  } finally {
    loading.value = false
  }
}
// Remove the fetchStats function entirely or simplify it:
const fetchStats = async () => {
  // Since stats come with members, just compute from current data
  computeLocalStats()

  // Optional: If you want fresh stats, fetch a single member (page 1, per_page=1)
  // to trigger the stats computation in the backend
  try {
    const token = localStorage.getItem('token')
    if (token) {
      const response = await axios.get('/api/members', {
        headers: { 'Authorization': `Bearer ${token}` },
        params: { per_page: 1, page: 1 }
      })
      if (response.data.success && response.data.stats) {
        stats.value = response.data.stats
      }
    }
  } catch (error) {
    console.log('Using local stats')
  }
}

// Alternative: Create a separate function to get ALL members for stats
const fetchAllMembersForStats = async () => {
  try {
    const token = localStorage.getItem('token')
    let allMembers = []
    let currentPage = 1
    let hasMorePages = true

    while (hasMorePages) {
      const response = await axios.get('/api/members', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        },
        params: {
          per_page: 100,
          page: currentPage
        }
      })

      if (response.data.success && response.data.data) {
        allMembers = [...allMembers, ...response.data.data]

        // Check if there are more pages
        if (response.data.meta) {
          hasMorePages = currentPage < response.data.meta.last_page
          currentPage++
        } else if (response.data.links && response.data.links.next) {
          currentPage++
        } else {
          hasMorePages = false
        }
      } else {
        hasMorePages = false
      }
    }

    return allMembers
  } catch (error) {
    console.error('Error fetching all members:', error)
    return []
  }
}


// Helper method to compute stats from member array
const computeStatsFromMembers = (membersArray) => {
  const now = new Date()
  const currentMonth = now.getMonth()
  const currentYear = now.getFullYear()

  stats.value = {
    total: membersArray.length,
    active: membersArray.filter(m => m.membership_status === 'active').length,
    visitors: membersArray.filter(m => m.membership_status === 'visitor').length,
    new_this_month: membersArray.filter(m => {
      if (!m.join_date) return false
      const joinDate = new Date(m.join_date)
      return joinDate.getMonth() === currentMonth &&
             joinDate.getFullYear() === currentYear
    }).length
  }
}

const computeLocalStats = () => {
  // This computes from current (filtered) members - which is WRONG
  // We should get stats from backend or compute from unfiltered data

  console.log('WARNING: computeLocalStats uses filtered data!')

  const now = new Date()
  const currentMonth = now.getMonth()
  const currentYear = now.getFullYear()

  stats.value = {
    total: members.value.length,
    active: members.value.filter(m =>
      m.membership_status && m.membership_status.toLowerCase() === 'active'
    ).length,
    visitors: members.value.filter(m =>
      m.membership_status && m.membership_status.toLowerCase() === 'visitor'
    ).length,
    new_this_month: members.value.filter(m => {
      if (!m.join_date) return false
      try {
        const joinDate = new Date(m.join_date)
        return joinDate.getMonth() === currentMonth &&
               joinDate.getFullYear() === currentYear
      } catch {
        return false
      }
    }).length
  }
}

const handleError = (error) => {
  if (error.response) {
    if (error.response.status === 401) {
      toast.error('Session expired. Please login again.')
      router.push('/login')
    } else if (error.response.status === 422) {
      const errors = error.response.data.errors
      Object.keys(errors).forEach(key => {
        toast.error(`${key}: ${errors[key][0]}`)
      })
    } else {
      toast.error(error.response.data.message || `Server error: ${error.response.status}`)
    }
  } else if (error.request) {
    toast.error('No response from server. Check your connection.')
  } else {
    toast.error('Failed to load members: ' + error.message)
  }
}

// Card click handlers
const showAllMembers = () => {
  resetAllFilters()
  fetchMembers()
  fetchStats()
}

const filterActiveMembers = () => {
  resetAllFilters()
  statusFilter.value = 'active'
  // Update quick filter active state
  quickFilters.value.forEach(f => {
    f.active = f.value === 'active'
  })
  fetchMembers()
}

const filterVisitors = () => {
  resetAllFilters()
  statusFilter.value = 'visitor'
  // Update quick filter active state
  quickFilters.value.forEach(f => {
    f.active = f.value === 'visitor'
  })
  fetchMembers()
}

const filterNewThisMonth = () => {
  resetAllFilters()
  const now = new Date()
  const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
  const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0)
  advancedFilters.value.joinDateRange.start = firstDay.toISOString().split('T')[0]
  advancedFilters.value.joinDateRange.end = lastDay.toISOString().split('T')[0]
  // Update quick filter active state
  quickFilters.value.forEach(f => {
    f.active = f.value === 'new_month'
  })
  fetchMembers()
}

// Quick filter methods
const applyQuickFilter = (filter) => {
  // Reset all quick filters first
  quickFilters.value.forEach(f => f.active = false)

  // Set the clicked filter as active
  filter.active = true

  // Reset other filters
  resetAdvancedFilters()
  statusFilter.value = ''
  search.value = ''

  switch (filter.value) {
    case 'active':
      statusFilter.value = 'active'
      break
    case 'visitor':
      statusFilter.value = 'visitor'
      break
    case 'new_month':
      const now = new Date()
      const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
      const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0)
      advancedFilters.value.joinDateRange.start = firstDay.toISOString().split('T')[0]
      advancedFilters.value.joinDateRange.end = lastDay.toISOString().split('T')[0]
      break
    case 'no_email':
      search.value = 'no-email'
      break
    case 'no_phone':
      search.value = 'no-phone'
      break
    case 'birthday':
      search.value = 'birthday-month'
      break
  }

  pagination.value.current_page = 1
  fetchMembers()
}

const resetAdvancedFilters = () => {
  advancedFilters.value = {
    gender: [],
    marital_status: [],
    city: '',
    occupation: '',
    joinDateRange: { start: '', end: '' },
    birthYear: ''
  }
}

const resetAllFilters = () => {
  search.value = ''
  statusFilter.value = ''
  sortBy.value = 'created_at_desc'
  resetAdvancedFilters()

  // Reset quick filters
  quickFilters.value.forEach(f => f.active = false)

  pagination.value.current_page = 1
  fetchMembers()
}

const applyAdvancedFilters = () => {
  pagination.value.current_page = 1
  // Reset quick filters when using advanced filters
  quickFilters.value.forEach(f => f.active = false)
  fetchMembers()
}

// Formatting helpers
const formatDate = (dateString, format = 'standard') => {
  if (!dateString) return 'N/A'
  try {
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return 'Invalid Date'

    if (format === 'short') {
      return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
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

const formatGender = (gender) => {
  if (!gender) return ''
  const genderMap = {
    'male': 'Male',
    'female': 'Female',
    'other': 'Other'
  }
  return genderMap[gender.toLowerCase()] || gender
}

const formatMaritalStatus = (status) => {
  if (!status) return ''
  const statusMap = {
    'single': 'Single',
    'married': 'Married',
    'divorced': 'Divorced',
    'widowed': 'Widowed'
  }
  return statusMap[status.toLowerCase()] || status
}

const formatStatus = (status) => {
  if (!status) return ''
  const statusMap = {
    'active': 'Active',
    'inactive': 'Inactive',
    'visitor': 'Visitor'
  }
  return statusMap[status.toLowerCase()] || status
}

const getStatusColor = (status) => {
  if (!status) return 'grey'
  const statusLower = status.toLowerCase()
  const colors = {
    'active': 'success',
    'inactive': 'error',
    'visitor': 'warning'
  }
  return colors[statusLower] || 'grey'
}

const calculateAge = (birthDate) => {
  if (!birthDate) return 'N/A'
  try {
    const today = new Date()
    const birth = new Date(birthDate)
    if (isNaN(birth.getTime())) return 'N/A'

    let age = today.getFullYear() - birth.getFullYear()
    const monthDiff = today.getMonth() - birth.getMonth()

    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
      age--
    }

    return age
  } catch {
    return 'N/A'
  }
}

const calculateDuration = (joinDate) => {
  if (!joinDate) return ''
  try {
    const today = new Date()
    const join = new Date(joinDate)
    if (isNaN(join.getTime())) return ''

    const years = today.getFullYear() - join.getFullYear()
    const months = today.getMonth() - join.getMonth()

    if (years > 0) {
      return `${years} year${years > 1 ? 's' : ''}`
    } else if (months > 0) {
      return `${months} month${months > 1 ? 's' : ''}`
    } else {
      return 'Less than a month'
    }
  } catch {
    return ''
  }
}

const getInitials = (member) => {
  if (!member) return '?'
  const first = member.first_name?.[0] || ''
  const last = member.last_name?.[0] || ''
  return (first + last).toUpperCase() || '?'
}

const getAvatarColor = (member) => {
  const colors = ['primary', 'secondary', 'success', 'error', 'warning', 'info', 'purple', 'pink', 'teal']
  const name = ((member.first_name || '') + (member.last_name || '')).toLowerCase()

  if (!name) return colors[0]

  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  const index = Math.abs(hash) % colors.length
  return colors[index]
}

// Member CRUD operations
const openCreateDialog = () => {
  resetForm()
  editingMember.value = null
  formStep.value = 1
  dialog.value = true
}

const goToNextStep = async () => {
  if (formStep.value === 1) {
    if (personalForm.value) {
      const { valid } = await personalForm.value.validate()
      if (!valid) {
        toast.error('Please fill in all required fields in Personal Info')
        return
      }
    } else {
      if (!form.value.first_name.trim() || !form.value.last_name.trim()) {
        toast.error('Please fill in First Name and Last Name')
        return
      }
    }
  } else if (formStep.value === 2) {
    if (contactForm.value) {
      const { valid } = await contactForm.value.validate()
      if (!valid) {
        toast.error('Please correct the contact information')
        return
      }
    }
  } else if (formStep.value === 3) {
    if (churchForm.value) {
      const { valid } = await churchForm.value.validate()
      if (!valid) {
        toast.error('Please fill in all required fields in Church Info')
        return
      }
    } else {
      if (!form.value.join_date || !form.value.membership_status) {
        toast.error('Please fill in Join Date and Membership Status')
        return
      }
    }
  }

  formStep.value++
}

const editMember = (member) => {
  editingMember.value = member
  // Convert dates to input format
  form.value = {
    ...member,
    birth_date: member.birth_date ? formatDateForInput(member.birth_date) : '',
    join_date: member.join_date ? formatDateForInput(member.join_date) : new Date().toISOString().split('T')[0]
  }
  formStep.value = 1
  dialog.value = true
}

const viewMember = (member) => {
  selectedMember.value = member
  viewTab.value = 'personal'
  viewDialog.value = true
}

const deleteMember = (member) => {
  selectedMemberToDelete.value = member
  deleteDialog.value = true
}

const saveMember = async () => {
  saving.value = true
  try {
    const token = localStorage.getItem('token')

    // Prepare payload ensuring proper formatting
    const payload = {
      first_name: form.value.first_name.trim(),
      last_name: form.value.last_name.trim(),
      email: form.value.email?.trim() || null,
      phone: form.value.phone?.trim() || null,
      birth_date: form.value.birth_date || null,
      join_date: form.value.join_date,
      address: form.value.address?.trim() || null,
      city: form.value.city?.trim() || null,
      state: form.value.state?.trim() || null,
      zip_code: form.value.zip_code?.trim() || null,
      occupation: form.value.occupation?.trim() || null,
      notes: form.value.notes?.trim() || null,
      membership_status: form.value.membership_status || 'active',
      church_id: form.value.church_id || auth.church?.id
    }

    // Handle gender and marital_status with validation
    if (form.value.gender && genderOptions.value.some(g => g.value === form.value.gender)) {
      payload.gender = form.value.gender
    } else if (form.value.gender) {
      throw new Error(`Invalid gender value: ${form.value.gender}`)
    }

    if (form.value.marital_status && maritalStatusOptions.value.some(m => m.value === form.value.marital_status)) {
      payload.marital_status = form.value.marital_status
    } else if (form.value.marital_status) {
      throw new Error(`Invalid marital status value: ${form.value.marital_status}`)
    }

    let response
    if (editingMember.value) {
      response = await axios.put(
        `/api/members/${editingMember.value.id}`,
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
        '/api/members',
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
      toast.success(response.data.message || 'Member saved successfully')
      closeDialog()
      fetchMembers()
      fetchStats() // Refresh stats
    } else {
      toast.error(response.data.message || 'Failed to save member')
    }

    } catch (error) {
        console.error('Error saving member:', error)
        if (error.response?.status === 422) {
        const errors = error.response.data.errors
        // Show validation errors to user
        Object.keys(errors).forEach(key => {
            toast.error(`${key}: ${errors[key][0]}`)
        })
        } else {
        toast.error(error.response?.data?.message || 'Failed to save member. Please try again.')
        }
    } finally {
        saving.value = false
    }
}

const confirmDelete = async () => {
  deleting.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.delete(`/api/members/${selectedMemberToDelete.value.id}`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })

    if (response.data.success) {
      toast.success(response.data.message || 'Member deleted successfully')
      deleteDialog.value = false
      selectedMemberToDelete.value = null
      fetchMembers()
      fetchStats() // Refresh stats
    } else {
      toast.error(response.data.message || 'Failed to delete member')
    }
  } catch (error) {
    console.error('Error deleting member:', error)
    toast.error(error.response?.data?.message || 'Failed to delete member')
  } finally {
    deleting.value = false
  }
}

const closeDialog = () => {
  dialog.value = false
  editingMember.value = null
  resetForm()
}

const resetForm = () => {
  form.value = {
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    birth_date: '',
    join_date: new Date().toISOString().split('T')[0],
    address: '',
    city: '',
    state: '',
    zip_code: '',
    gender: '',
    marital_status: '',
    occupation: '',
    notes: '',
    membership_status: 'active',
    church_id: auth.church?.id || null
  }
}

const formatDateForInput = (dateString) => {
  if (!dateString) return ''
  try {
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return ''
    return date.toISOString().split('T')[0]
  } catch {
    return ''
  }
}

// Import functions
const openImportDialog = () => {
  importDialog.value = true
  importFile.value = null
  importPreview.value = []
  importHeaders.value = []
}

const handleFileChange = () => {
  if (!importFile.value) {
    importPreview.value = []
    importHeaders.value = []
    return
  }

  setTimeout(() => {
    previewImport()
  }, 100)
}

const previewImport = () => {
  if (!importFile.value) {
    toast.error('Please select a file first')
    return
  }

  try {
    const file = importFile.value
    const reader = new FileReader()

    reader.onload = (e) => {
      try {
        const data = new Uint8Array(e.target.result)
        const workbook = XLSX.read(data, {
          type: 'array',
          cellDates: true,
          cellText: false
        })

        const firstSheet = workbook.SheetNames[0]
        const worksheet = workbook.Sheets[firstSheet]
        const jsonData = XLSX.utils.sheet_to_json(worksheet, {
          raw: false,
          defval: ''
        })

        if (jsonData.length > 0) {
          importHeaders.value = Object.keys(jsonData[0])

          importPreview.value = jsonData.slice(0, 5).map(row => {
            const processedRow = {}
            importHeaders.value.forEach((header) => {
              let value = row[header]

              if (value instanceof Date) {
                value = value.toISOString().split('T')[0]
              }

              if (header.toLowerCase() === 'phone' || header.toLowerCase() === 'zip_code') {
                if (typeof value === 'number') {
                  value = value.toString()
                }
              }

              processedRow[header] = value !== undefined ? value : ''
            })
            return processedRow
          })

          toast.info(`Found ${jsonData.length} rows to import (showing first 5)`)
        } else {
          toast.warning('No data found in the file')
        }
      } catch (error) {
        console.error('Error parsing file:', error)
        toast.error('Failed to parse file. Please check the format.')
      }
    }

    reader.onerror = (error) => {
      console.error('Error reading file:', error)
      toast.error('Failed to read file')
    }

    reader.readAsArrayBuffer(file)
  } catch (error) {
    console.error('Error in previewImport:', error)
    toast.error('Failed to process file: ' + error.message)
  }
}

const processImport = async () => {
  if (!importFile.value) {
    toast.error('Please select a file to import')
    return
  }

  importing.value = true

  try {
    const token = localStorage.getItem('token')

    if (!token) {
      toast.error('Authentication required')
      importing.value = false
      return
    }

    const formData = new FormData()
    formData.append('file', importFile.value)

    const response = await axios.post('/api/members/import', formData, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'multipart/form-data'
      }
    })

    if (response.data.success) {
      toast.success(response.data.message || 'Members imported successfully!')
      importDialog.value = false
      importFile.value = null
      importPreview.value = []
      importHeaders.value = []

      await fetchMembers()
      await fetchStats()
    } else {
      toast.error(response.data.message || 'Import failed')
    }

  } catch (error) {
    console.error('Import error:', error)
    toast.error(error.response?.data?.message || 'Import failed. Please try again.')
  } finally {
    importing.value = false
  }
}

const downloadTemplate = () => {
  try {
    const workbook = XLSX.utils.book_new()
    const today = new Date().toISOString().split('T')[0]

    const sampleData = [
      {
        'first_name': 'John',
        'last_name': 'Doe',
        'email': 'john@example.com',
        'phone': '07012345678',
        'birth_date': '1990-03-03',
        'join_date': today,
        'gender': 'male',
        'marital_status': 'married',
        'occupation': 'Engineer',
        'address': '123 Main St',
        'city': 'Lagos',
        'state': 'Lagos',
        'zip_code': '100001',
        'membership_status': 'active',
        'notes': 'Sample member'
      }
    ]

    const worksheet = XLSX.utils.json_to_sheet(sampleData)
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Sample Data')

    const excelBuffer = XLSX.write(workbook, {
      bookType: 'xlsx',
      type: 'array'
    })

    const blob = new Blob([excelBuffer], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    })

    saveAs(blob, 'Church_Members_Import_Template.xlsx')
    toast.success('Template downloaded successfully!')

  } catch (error) {
    console.error('Error creating template:', error)
    toast.error('Failed to create template: ' + error.message)
  }
}

const exportMembers = async () => {
  try {
    const token = localStorage.getItem('token')

    // Fetch all members
    const response = await axios.get('/api/members', {
      headers: { Authorization: `Bearer ${token}` },
      params: {
        per_page: 10000,
        search: search.value,
        status: statusFilter.value,
        ...advancedFilters.value
      }
    })

    let data = []
    if (response.data.data) {
      data = response.data.data
    } else {
      data = members.value
    }

    const exportData = data.map((member, index) => ({
      'No.': index + 1,
      'First Name': member.first_name,
      'Last Name': member.last_name,
      'Email': member.email || '',
      'Phone': member.phone || '',
      'Gender': formatGender(member.gender),
      'Marital Status': formatMaritalStatus(member.marital_status),
      'Occupation': member.occupation || '',
      'Birth Date': member.birth_date || '',
      'Age': calculateAge(member.birth_date),
      'Join Date': member.join_date,
      'Membership Duration': calculateDuration(member.join_date),
      'Membership Status': formatStatus(member.membership_status),
      'Address': member.address || '',
      'City': member.city || '',
      'State': member.state || '',
      'ZIP Code': member.zip_code || '',
      'Notes': member.notes || ''
    }))

    const worksheet = XLSX.utils.json_to_sheet(exportData)
    const workbook = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Members')

    const excelBuffer = XLSX.write(workbook, { bookType: 'xlsx', type: 'array' })
    const blob = new Blob([excelBuffer], { type: 'application/octet-stream' })

    const filename = `Church_Members_${new Date().toISOString().split('T')[0]}.xlsx`
    saveAs(blob, filename)

    toast.success(`Exported ${exportData.length} members successfully!`)

  } catch (error) {
    console.error('Error exporting members:', error)
    toast.error('Failed to export members. Please try again.')
  }
}

const printMembers = async () => {
  try {
    const token = localStorage.getItem('token')

    // Fetch all members for printing
    const response = await axios.get('/api/members', {
      headers: { Authorization: `Bearer ${token}` },
      params: {
        per_page: 100000,
        search: search.value,
        status: statusFilter.value,
        ...advancedFilters.value
      }
    })

    let allMembers = []
    if (response.data.data) {
      allMembers = response.data.data
    } else {
      allMembers = members.value
    }

    const printContent = `
      <!DOCTYPE html>
      <html>
        <head>
          <title>Members Report - ${auth.church?.name || 'Church'}</title>
          <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
            .header { text-align: center; margin-bottom: 30px; }
            .date { color: #666; font-size: 14px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 12px; text-align: left; }
            td { border: 1px solid #dee2e6; padding: 8px; }
            .status-active { color: #28a745; }
            .status-inactive { color: #dc3545; }
            .status-visitor { color: #ffc107; }
            .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
            @media print {
              .no-print { display: none; }
            }
          </style>
        </head>
        <body>
          <div class="header">
            <h1>Members Report</h1>
            <div class="date">Generated on ${new Date().toLocaleString()}</div>
            <div>Total Members: ${allMembers.length}</div>
          </div>
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Join Date</th>
                <th>Status</th>
                <th>Age</th>
                <th>Occupation</th>
              </tr>
            </thead>
            <tbody>
              ${allMembers.map((m, index) => `
                <tr>
                  <td>${index + 1}</td>
                  <td>${m.first_name} ${m.last_name}</td>
                  <td>
                    ${m.email ? `<div>${m.email}</div>` : ''}
                    ${m.phone ? `<div>${m.phone}</div>` : ''}
                  </td>
                  <td>${formatDate(m.join_date)}</td>
                  <td class="status-${m.membership_status}">${formatStatus(m.membership_status)}</td>
                  <td>${calculateAge(m.birth_date)}</td>
                  <td>${m.occupation || 'N/A'}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
          <div class="footer">
            <p>© ${new Date().getFullYear()} ${auth.church?.name || 'Church Management System'}</p>
          </div>
        </body>
      </html>
    `

    const printWindow = window.open('', '_blank')
    printWindow.document.write(printContent)
    printWindow.document.close()
    printWindow.focus()

    setTimeout(() => {
      printWindow.print()
    }, 500)
  } catch (error) {
    console.error('Error printing members:', error)
    toast.error('Failed to print members. Please try again.')
  }
}

// Debounced search
let searchTimeout = null
const debouncedFetchMembers = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchMembers()
  }, 500)
}

// Lifecycle
onMounted(async () => {
  await Promise.all([fetchMembers(), fetchStats()])
})

// Watch for changes
watch([statusFilter, sortBy], () => {
  pagination.value.current_page = 1
  fetchMembers()
})

watch(advancedFilters, () => {
  debouncedFetchMembers()
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

.review-section {
  background-color: rgba(0, 0, 0, 0.02);
  border-radius: 8px;
  padding: 16px;
}

.v-table tr:hover {
  background-color: rgba(var(--v-theme-primary), 0.04);
  transition: background-color 0.2s ease;
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
}
</style>
