<template>
  <div>
    <h1>Member Directory</h1>
    <div style="margin: 1rem 0;">
      <button style="background: #4f46e5; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 5px; cursor: pointer;">
        + Add New Member
      </button>
    </div>

    <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
      <table style="width: 100%; border-collapse: collapse;">
        <thead style="background: #f3f4f6;">
          <tr>
            <th style="padding: 1rem; text-align: left;">Name</th>
            <th style="padding: 1rem; text-align: left;">Email</th>
            <th style="padding: 1rem; text-align: left;">Phone</th>
            <th style="padding: 1rem; text-align: left;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr style="border-top: 1px solid #e5e7eb;">
            <td style="padding: 1rem;">John Doe</td>
            <td style="padding: 1rem;">john@example.com</td>
            <td style="padding: 1rem;">123-456-7890</td>
            <td style="padding: 1rem;">
              <button style="background: #3b82f6; color: white; border: none; padding: 0.25rem 0.5rem; border-radius: 3px; margin-right: 0.5rem; cursor: pointer;">Edit</button>
              <button style="background: #ef4444; color: white; border: none; padding: 0.25rem 0.5rem; border-radius: 3px; cursor: pointer;">Delete</button>
            </td>
          </tr>
          <tr style="border-top: 1px solid #e5e7eb;">
            <td style="padding: 1rem;">Jane Smith</td>
            <td style="padding: 1rem;">jane@example.com</td>
            <td style="padding: 1rem;">123-456-7891</td>
            <td style="padding: 1rem;">
              <button style="background: #3b82f6; color: white; border: none; padding: 0.25rem 0.5rem; border-radius: 3px; margin-right: 0.5rem; cursor: pointer;">Edit</button>
              <button style="background: #ef4444; color: white; border: none; padding: 0.25rem 0.5rem; border-radius: 3px; cursor: pointer;">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div style="margin-top: 2rem; color: #6b7280; font-size: 0.875rem;">
      <p>Members page loaded successfully.</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Members'
}
</script>
