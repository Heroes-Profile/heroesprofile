<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - Heroes Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .error-code {
            font-size: 4rem;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .error-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 1rem;
        }
        .error-message {
            color: #666;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        .ban-details {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            margin-top: 1rem;
            text-align: left;
        }
        .ban-details h4 {
            margin-top: 0;
            color: #333;
        }
        .ban-details p {
            margin: 0.5rem 0;
            color: #666;
        }
        .contact-info {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">403</div>
        <div class="error-title">Access Denied</div>
        <div class="error-message">
            {{ $message ?? 'Your IP address has been banned from accessing this site.' }}
        </div>
        
        @if(isset($ban_reason) || isset($banned_date))
        <div class="ban-details">
            <h4>Ban Details</h4>
            @if(isset($ban_reason))
                <p><strong>Reason:</strong> {{ $ban_reason }}</p>
            @endif
            @if(isset($banned_date))
                <p><strong>Banned on:</strong> {{ $banned_date->format('Y-m-d H:i:s T') }}</p>
            @endif
        </div>
        @endif
        
        <div class="contact-info">
            If you believe this is an error, please contact the site administrators.
        </div>
    </div>
</body>
</html>
