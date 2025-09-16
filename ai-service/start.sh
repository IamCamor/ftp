#!/bin/bash

# AI Service startup script for FishTrackPro

# Set environment variables
export AI_SERVICE_HOST=0.0.0.0
export AI_SERVICE_PORT=8001
export AI_SERVICE_WORKERS=1

# Activate virtual environment
source ../venv/bin/activate

# Install dependencies if needed
pip install -r requirements.txt

# Start the AI service
echo "Starting FishTrackPro AI Service..."
echo "Host: $AI_SERVICE_HOST"
echo "Port: $AI_SERVICE_PORT"
echo "Workers: $AI_SERVICE_WORKERS"

python api.py

