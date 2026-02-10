<template>
  <FullCalendar :options="calendarOptions" />
</template>

<script setup>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import { ref } from 'vue'

const props = defineProps({ events: Array })

const calendarOptions = ref({
  plugins: [dayGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  events: props.events.map(e => ({
    title: e.title,
    start: e.start_date,
    end: e.end_date,
  }))
})
</script>
