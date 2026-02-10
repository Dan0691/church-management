<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Your Church</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Register Your Church</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('church.register') }}">
                            @csrf

                            <!-- Church Information -->
                            <h5 class="mb-3">Church Information</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="church_name" class="form-label">Church Name *</label>
                                    <input type="text" class="form-control @error('church_name') is-invalid @enderror"
                                           id="church_name" name="church_name" value="{{ old('church_name') }}" required>
                                    @error('church_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="pastor_name" class="form-label">Pastor's Name *</label>
                                    <input type="text" class="form-control @error('pastor_name') is-invalid @enderror"
                                           id="pastor_name" name="pastor_name" value="{{ old('pastor_name') }}" required>
                                    @error('pastor_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="church_email" class="form-label">Church Email *</label>
                                <input type="email" class="form-control @error('church_email') is-invalid @enderror"
                                       id="church_email" name="church_email" value="{{ old('church_email') }}" required>
                                @error('church_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Admin User Information -->
                            <h5 class="mb-3 mt-4">Your Information (Church Admin)</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Your Name *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                           id="phone" name="phone" value="{{ old('phone') }}" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Your Email *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control"
                                           id="password_confirmation" name="password_confirmation" required>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Register Church</button>
                            </div>

                            <div class="text-center mt-3">
                                <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
