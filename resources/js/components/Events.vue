<template>
  <div>
    <h1>Events Calendar</h1>
    <div style="margin: 1rem 0;">
      <button style="background: #4f46e5; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 5px; cursor: pointer;">
        + Create New Event
      </button>
    </div>

    <div style="display: grid; gap: 1rem;">
      <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3 style="margin: 0 0 0.5rem 0;">Sunday Service</h3>
        <p style="margin: 0 0 0.5rem 0; color: #6b7280;">Every Sunday at 10:00 AM</p>
        <p style="margin: 0; color: #6b7280;">Main Sanctuary</p>
      </div>

      <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3 style="margin: 0 0 0.5rem 0;">Bible Study</h3>
        <p style="margin: 0 0 0.5rem 0; color: #6b7280;">Wednesday at 7:00 PM</p>
        <p style="margin: 0; color: #6b7280;">Fellowship Hall</p>
      </div>

      <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3 style="margin: 0 0 0.5rem 0;">Youth Group</h3>
        <p style="margin: 0 0 0.5rem 0; color: #6b7280;">Friday at 6:00 PM</p>
        <p style="margin: 0; color: #6b7280;">Youth Center</p>
      </div>
    </div>

    <div style="margin-top: 2rem; color: #6b7280; font-size: 0.875rem;">
      <p>Events page loaded successfully.</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Events'
}
</script>
