<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Church Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">ChurchManager</a>
            <div class="navbar-nav ms-auto">
                <a href="#" class="nav-link">{{ auth()->user()->name }}</a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm ms-2">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Navigation</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="/dashboard" class="list-group-item list-group-item-action active">
                            Dashboard
                        </a>
                        <a href="/members" class="list-group-item list-group-item-action">
                            Members
                        </a>
                        <a href="/events" class="list-group-item list-group-item-action">
                            Events
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            Settings
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Church Dashboard</h4>
                    </div>
                    <div class="card-body">
                        <h5>Welcome, {{ auth()->user()->name }}!</h5>
                        <p class="text-muted">You are logged in as the administrator for <strong>{{ auth()->user()->church->name ?? 'Your Church' }}</strong></p>

                        <!-- Dashboard Stats -->
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Total Members</h6>
                                        <h2 class="mb-0">0</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Upcoming Events</h6>
                                        <h2 class="mb-0">0</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Recent Activity</h6>
                                        <h2 class="mb-0">0</h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="mt-4">
                            <h5>Quick Actions</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <a href="/members" class="btn btn-outline-primary btn-block mb-2">
                                        Add Member
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="/events" class="btn btn-outline-success btn-block mb-2">
                                        Create Event
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="#" class="btn btn-outline-info btn-block mb-2">
                                        Send Announcement
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="#" class="btn btn-outline-warning btn-block mb-2">
                                        Church Settings
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
