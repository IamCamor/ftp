# FishTrackPro AI Service Setup

## Overview

The FishTrackPro AI Service provides intelligent image analysis and content moderation capabilities for the fishing application. It uses AI models to analyze uploaded images for safety, categorize content, generate alt text for accessibility, and detect fish species.

## Features

- **Image Analysis**: Analyzes images for safety and content categorization
- **Content Moderation**: Automatically moderates uploaded content based on AI analysis
- **Alt Text Generation**: Generates accessibility-friendly alt text for images
- **Fish Species Detection**: Identifies fish species in uploaded images
- **Batch Processing**: Supports batch analysis of multiple images
- **RESTful API**: Clean REST API for easy integration

## Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Laravel       │    │   AI Service    │
│   (React)       │◄──►│   Backend       │◄──►│   (FastAPI)     │
│                 │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## Installation

### 1. Prerequisites

- Python 3.12+
- Node.js 22+
- PHP 8.2+
- Composer
- Virtual environment support

### 2. Setup AI Service

```bash
# Navigate to project root
cd /var/www/ftp

# Create virtual environment
python3 -m venv venv
source venv/bin/activate

# Install AI service dependencies
pip install -r ai-service/requirements.txt

# Make startup script executable
chmod +x ai-service/start.sh
```

### 3. Configure Backend

Add AI service configuration to your `.env` file:

```env
# AI Service Configuration
AI_SERVICE_URL=http://localhost:8001
AI_SERVICE_TIMEOUT=30
AI_SERVICE_ENABLED=true
AI_MIN_CONFIDENCE=0.7
AI_AUTO_APPROVE_THRESHOLD=0.9
AI_AUTO_REJECT_THRESHOLD=0.3
AI_MAX_FILE_SIZE=10485760
```

## Usage

### Starting the AI Service

```bash
# Start AI service
cd ai-service
./start.sh

# Or manually
source ../venv/bin/activate
python api.py
```

The AI service will start on `http://localhost:8001`

### Starting the Backend

```bash
# Start Laravel backend
cd backend
php artisan serve --host=0.0.0.0 --port=8000
```

The backend will start on `http://localhost:8000`

## API Endpoints

### AI Service Direct Endpoints

#### Health Check
```http
GET /health
```

#### Image Analysis
```http
POST /analyze
Content-Type: application/json

{
  "image_data": "base64_encoded_image",
  "content_type": "catch|profile|place"
}
```

#### Content Moderation
```http
POST /moderate
Content-Type: application/json

{
  "image_data": "base64_encoded_image",
  "content_type": "catch|profile|place"
}
```

#### Alt Text Generation
```http
POST /alt-text
Content-Type: application/json

{
  "image_data": "base64_encoded_image",
  "context": "optional_context"
}
```

#### Species Detection
```http
POST /detect-species
Content-Type: application/json

{
  "image_data": "base64_encoded_image"
}
```

#### File Upload
```http
POST /upload-analyze
Content-Type: multipart/form-data

file: image_file
content_type: catch|profile|place
context: optional_context
```

### Backend Integration Endpoints

#### Health Check
```http
GET /api/ai/health
```

#### Image Analysis
```http
POST /api/ai/analyze
Content-Type: multipart/form-data

image: image_file
content_type: catch|profile|place
```

#### Content Moderation
```http
POST /api/ai/moderate
Content-Type: multipart/form-data

image: image_file
content_type: catch|profile|place
```

#### Alt Text Generation
```http
POST /api/ai/alt-text
Content-Type: multipart/form-data

image: image_file
context: optional_context
```

#### Species Detection
```http
POST /api/ai/detect-species
Content-Type: multipart/form-data

image: image_file
```

#### Configuration
```http
GET /api/ai/config
```

## Testing

### Run Test Suite

```bash
# Run comprehensive tests
node test-ai-service.js
```

### Manual Testing

```bash
# Test AI service health
curl http://localhost:8001/health

# Test image analysis
curl -X POST -F "file=@test-image.png" -F "content_type=catch" \
  http://localhost:8001/upload-analyze

# Test backend integration
curl http://localhost:8000/api/ai/health
```

## Configuration

### AI Service Configuration

The AI service can be configured through environment variables:

- `AI_SERVICE_HOST`: Host to bind to (default: 0.0.0.0)
- `AI_SERVICE_PORT`: Port to listen on (default: 8001)
- `AI_SERVICE_WORKERS`: Number of worker processes (default: 1)

### Backend Configuration

Configuration is managed through `config/ai.php`:

```php
return [
    'service_url' => env('AI_SERVICE_URL', 'http://localhost:8001'),
    'timeout' => env('AI_SERVICE_TIMEOUT', 30),
    'enabled' => env('AI_SERVICE_ENABLED', true),
    
    'moderation' => [
        'min_confidence' => env('AI_MIN_CONFIDENCE', 0.7),
        'auto_approve_threshold' => env('AI_AUTO_APPROVE_THRESHOLD', 0.9),
        'auto_reject_threshold' => env('AI_AUTO_REJECT_THRESHOLD', 0.3),
    ],
    
    'image_analysis' => [
        'max_file_size' => env('AI_MAX_FILE_SIZE', 10485760),
        'allowed_formats' => ['jpg', 'jpeg', 'png', 'webp'],
        'min_dimensions' => ['width' => 50, 'height' => 50],
        'max_dimensions' => ['width' => 5000, 'height' => 5000],
    ],
    
    'content_types' => [
        'catch' => [
            'required_categories' => ['water', 'nature'],
            'forbidden_categories' => ['explicit'],
            'min_confidence' => 0.6,
        ],
        'profile' => [
            'required_categories' => ['person'],
            'forbidden_categories' => ['explicit'],
            'min_confidence' => 0.7,
        ],
        'place' => [
            'required_categories' => ['nature'],
            'forbidden_categories' => ['explicit'],
            'min_confidence' => 0.5,
        ],
    ],
];
```

## Integration with Frontend

### React Component Example

```jsx
import React, { useState } from 'react';

const ImageUpload = () => {
  const [file, setFile] = useState(null);
  const [analysis, setAnalysis] = useState(null);

  const handleFileUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('image', file);
    formData.append('content_type', 'catch');

    try {
      const response = await fetch('/api/ai/analyze', {
        method: 'POST',
        body: formData,
      });

      const result = await response.json();
      setAnalysis(result.analysis);
    } catch (error) {
      console.error('Analysis failed:', error);
    }
  };

  return (
    <div>
      <input type="file" onChange={handleFileUpload} accept="image/*" />
      {analysis && (
        <div>
          <p>Safe: {analysis.is_safe ? 'Yes' : 'No'}</p>
          <p>Confidence: {analysis.confidence}</p>
          <p>Categories: {analysis.categories.join(', ')}</p>
        </div>
      )}
    </div>
  );
};
```

## Production Deployment

### Using PM2

```bash
# Install PM2
npm install -g pm2

# Start AI service with PM2
pm2 start ai-service/api.py --name "ai-service" --interpreter python3

# Start backend with PM2
pm2 start "php artisan serve --host=0.0.0.0 --port=8000" --name "backend"
```

### Using Docker

```dockerfile
# AI Service Dockerfile
FROM python:3.12-slim

WORKDIR /app
COPY ai-service/requirements.txt .
RUN pip install -r requirements.txt

COPY ai-service/ .
EXPOSE 8001

CMD ["python", "api.py"]
```

### Using Nginx

```nginx
# AI Service upstream
upstream ai_service {
    server localhost:8001;
}

# Backend upstream
upstream backend {
    server localhost:8000;
}

server {
    listen 80;
    server_name your-domain.com;

    # AI Service routes
    location /ai/ {
        proxy_pass http://ai_service/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }

    # Backend routes
    location /api/ {
        proxy_pass http://backend/api/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

## Monitoring and Logging

### Health Monitoring

```bash
# Check AI service health
curl http://localhost:8001/health

# Check backend AI integration
curl http://localhost:8000/api/ai/health
```

### Logs

- AI Service logs: Check console output or configure logging
- Backend logs: `storage/logs/laravel.log`

## Troubleshooting

### Common Issues

1. **AI Service not starting**
   - Check Python version (3.12+)
   - Verify virtual environment activation
   - Check port availability (8001)

2. **Backend integration failing**
   - Verify AI service is running
   - Check configuration in `.env`
   - Verify network connectivity

3. **Image analysis errors**
   - Check file format (jpg, png, webp)
   - Verify file size limits
   - Check image dimensions

### Debug Mode

Enable debug mode by setting environment variables:

```bash
export AI_SERVICE_DEBUG=true
export LARAVEL_DEBUG=true
```

## Security Considerations

- Configure CORS properly for production
- Use HTTPS in production
- Implement rate limiting
- Validate file uploads
- Monitor for abuse

## Performance Optimization

- Use multiple worker processes
- Implement caching for repeated analyses
- Optimize image processing
- Use CDN for static assets

## Future Enhancements

- Advanced fish species detection models
- Real-time content moderation
- Machine learning model updates
- Advanced image enhancement
- Video content analysis

