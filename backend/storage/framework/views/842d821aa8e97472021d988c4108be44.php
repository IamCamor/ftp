<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FishTrackPro API</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
        }
        .api-info {
            background: #e8f4f8;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .endpoint {
            background: #f8f9fa;
            padding: 10px;
            margin: 10px 0;
            border-left: 4px solid #007bff;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎣 FishTrackPro API</h1>
        
        <div class="api-info">
            <h2>Welcome to FishTrackPro Backend API</h2>
            <p>This is the backend API server for FishTrackPro application.</p>
        </div>

        <h3>Available API Endpoints:</h3>
        
        <div class="endpoint">
            <strong>GET</strong> /api/v1/ - API documentation
        </div>
        
        <div class="endpoint">
            <strong>POST</strong> /api/v1/auth/login - User login
        </div>
        
        <div class="endpoint">
            <strong>POST</strong> /api/v1/auth/register - User registration
        </div>
        
        <div class="endpoint">
            <strong>GET</strong> /api/v1/catches - Get catches
        </div>
        
        <div class="endpoint">
            <strong>GET</strong> /api/v1/points - Get fishing points
        </div>
        
        <div class="endpoint">
            <strong>GET</strong> /api/v1/events - Get events
        </div>

        <p style="text-align: center; margin-top: 30px; color: #666;">
            For more information, please refer to the API documentation.
        </p>
    </div>
</body>
</html>
<?php /**PATH /Users/alexandershumilov/Documents/Product Owner/Cursor/backend/resources/views/welcome.blade.php ENDPATH**/ ?>