<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Message Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 20px;
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #4f46e5;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #e5e7eb;
            margin-bottom: 20px;
        }
        .footer {
            font-size: 12px;
            color: #6b7280;
            text-align: center;
            margin-top: 20px;
        }
        .button {
            display: inline-block;
            background-color: #4f46e5;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .message-meta {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .message-body {
            white-space: pre-line;
            padding: 15px;
            background-color: #f9fafb;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>New Message: {{ $messageTitle }}</h2>
    </div>
    
    <div class="content">
        <p>Hello {{ $recipientName }},</p>
        
        <p>You have received a new message from <strong>{{ $senderName }}</strong> in the Project Hub system.</p>
        
        <div class="message-meta">
            <p><strong>Subject:</strong> {{ $messageSubject }}</p>
        </div>
        
        <div class="message-body">
            {{ $messageBody }}
        </div>
        
        <p>To view the full message and reply, please click the button below:</p>
        
        <a href="{{ $messageUrl }}" class="button">View Message</a>
    </div>
    
    <div class="footer">
        <p>This is an automated message from the Project Hub system. Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} Project Hub. All rights reserved.</p>
    </div>
</body>
</html>
