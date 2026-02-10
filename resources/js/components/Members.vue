<template>
    <div>
        <h1>Member Directory</h1>
        <button class="btn btn-primary mb-3">Add New Member</button>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" class="text-center">No members yet</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    name: 'Members'
}
</script>
