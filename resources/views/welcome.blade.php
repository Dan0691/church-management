<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Church Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        h1 {
            color: #333;
            margin-bottom: 1rem;
        }
        p {
            color: #666;
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-block;
            background: #4f46e5;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 0 10px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #4338ca;
        }
        .btn-secondary {
            background: #6b7280;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .login-info {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏛️ Church Management System</h1>
        <p>Manage your church members, events, donations, and more with our comprehensive management platform.</p>

        <div>
            <a href="/login" class="btn">Login</a>
            <a href="/register" class="btn btn-secondary">Register Church</a>
        </div>

        <div class="login-info">
            <strong>Test Login:</strong><br>
            Email: <code>admin@church.com</code><br>
            Password: <code>password123</code>
        </div>
    </div>
</body>
</html>
