#!/usr/bin/env python3
"""
AI Service API for FishTrackPro
FastAPI endpoints for image analysis and content moderation
"""

import os
import asyncio
import logging
from typing import Dict, List, Optional
from pathlib import Path
import base64
import io
from PIL import Image

from fastapi import FastAPI, HTTPException, UploadFile, File, Form
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from pydantic import BaseModel

from main import get_ai_service

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Initialize FastAPI app
app = FastAPI(
    title="FishTrackPro AI Service",
    description="AI-powered image analysis and content moderation for FishTrackPro",
    version="1.0.0"
)

# Add CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Configure this properly in production
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Pydantic models for request/response
class ImageAnalysisRequest(BaseModel):
    image_data: str  # Base64 encoded image
    content_type: str = "catch"
    context: Optional[str] = None

class ImageAnalysisResponse(BaseModel):
    is_safe: bool
    confidence: float
    categories: List[str]
    warnings: List[str]
    metadata: Dict
    content_type: str

class ModerationRequest(BaseModel):
    image_data: str  # Base64 encoded image
    content_type: str = "catch"

class ModerationResponse(BaseModel):
    approved: bool
    confidence: float
    reason: str
    categories: List[str]
    warnings: List[str]
    content_type: str
    metadata: Dict

class AltTextRequest(BaseModel):
    image_data: str  # Base64 encoded image
    context: Optional[str] = None

class AltTextResponse(BaseModel):
    alt_text: str

class SpeciesDetectionRequest(BaseModel):
    image_data: str  # Base64 encoded image

class SpeciesDetectionResponse(BaseModel):
    species: str
    confidence: float
    alternatives: List[str]
    metadata: Dict

# Health check endpoint
@app.get("/health")
async def health_check():
    """Health check endpoint"""
    ai_service = get_ai_service()
    return {
        "status": "healthy",
        "models_loaded": ai_service.models_loaded,
        "service": "FishTrackPro AI Service"
    }

# Image analysis endpoint
@app.post("/analyze", response_model=ImageAnalysisResponse)
async def analyze_image(request: ImageAnalysisRequest):
    """Analyze image content for safety and categorization"""
    try:
        # Decode base64 image
        image_data = base64.b64decode(request.image_data)
        
        # Get AI service
        ai_service = get_ai_service()
        
        # Analyze image
        analysis = ai_service.analyze_image_content(image_data)
        
        return ImageAnalysisResponse(
            is_safe=analysis["is_safe"],
            confidence=analysis["confidence"],
            categories=analysis["categories"],
            warnings=analysis["warnings"],
            metadata=analysis["metadata"],
            content_type=request.content_type
        )
        
    except Exception as e:
        logger.error(f"Error in analyze_image: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# Content moderation endpoint
@app.post("/moderate", response_model=ModerationResponse)
async def moderate_content(request: ModerationRequest):
    """Moderate content based on image analysis"""
    try:
        # Decode base64 image
        image_data = base64.b64decode(request.image_data)
        
        # Get AI service
        ai_service = get_ai_service()
        
        # Moderate content
        moderation = ai_service.moderate_content(image_data, request.content_type)
        
        return ModerationResponse(
            approved=moderation["approved"],
            confidence=moderation["confidence"],
            reason=moderation["reason"],
            categories=moderation["categories"],
            warnings=moderation["warnings"],
            content_type=moderation["content_type"],
            metadata=moderation["metadata"]
        )
        
    except Exception as e:
        logger.error(f"Error in moderate_content: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# Alt text generation endpoint
@app.post("/alt-text", response_model=AltTextResponse)
async def generate_alt_text(request: AltTextRequest):
    """Generate alt text for accessibility"""
    try:
        # Decode base64 image
        image_data = base64.b64decode(request.image_data)
        
        # Get AI service
        ai_service = get_ai_service()
        
        # Generate alt text
        alt_text = ai_service.generate_alt_text(image_data, request.context)
        
        return AltTextResponse(alt_text=alt_text)
        
    except Exception as e:
        logger.error(f"Error in generate_alt_text: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# Species detection endpoint
@app.post("/detect-species", response_model=SpeciesDetectionResponse)
async def detect_fish_species(request: SpeciesDetectionRequest):
    """Detect fish species in the image"""
    try:
        # Decode base64 image
        image_data = base64.b64decode(request.image_data)
        
        # Get AI service
        ai_service = get_ai_service()
        
        # Detect species
        detection = ai_service.detect_fish_species(image_data)
        
        return SpeciesDetectionResponse(
            species=detection["species"],
            confidence=detection["confidence"],
            alternatives=detection["alternatives"],
            metadata=detection["metadata"]
        )
        
    except Exception as e:
        logger.error(f"Error in detect_fish_species: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# File upload endpoint
@app.post("/upload-analyze")
async def upload_and_analyze(
    file: UploadFile = File(...),
    content_type: str = Form("catch"),
    context: Optional[str] = Form(None)
):
    """Upload file and analyze it"""
    try:
        # Read file data
        image_data = await file.read()
        
        # Get AI service
        ai_service = get_ai_service()
        
        # Analyze image
        analysis = ai_service.analyze_image_content(image_data)
        
        # Generate alt text
        alt_text = ai_service.generate_alt_text(image_data, context)
        
        return {
            "filename": file.filename,
            "content_type": content_type,
            "analysis": analysis,
            "alt_text": alt_text
        }
        
    except Exception as e:
        logger.error(f"Error in upload_and_analyze: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# Batch analysis endpoint
@app.post("/batch-analyze")
async def batch_analyze(requests: List[ImageAnalysisRequest]):
    """Analyze multiple images in batch"""
    try:
        results = []
        ai_service = get_ai_service()
        
        for request in requests:
            try:
                # Decode base64 image
                image_data = base64.b64decode(request.image_data)
                
                # Analyze image
                analysis = ai_service.analyze_image_content(image_data)
                
                results.append({
                    "content_type": request.content_type,
                    "analysis": analysis
                })
                
            except Exception as e:
                logger.error(f"Error analyzing image in batch: {e}")
                results.append({
                    "content_type": request.content_type,
                    "error": str(e)
                })
        
        return {"results": results}
        
    except Exception as e:
        logger.error(f"Error in batch_analyze: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# Error handlers
@app.exception_handler(404)
async def not_found_handler(request, exc):
    return JSONResponse(
        status_code=404,
        content={"error": "Endpoint not found", "detail": str(exc)}
    )

@app.exception_handler(500)
async def internal_error_handler(request, exc):
    return JSONResponse(
        status_code=500,
        content={"error": "Internal server error", "detail": str(exc)}
    )

if __name__ == "__main__":
    import uvicorn
    
    # Get configuration from environment
    host = os.getenv("AI_SERVICE_HOST", "0.0.0.0")
    port = int(os.getenv("AI_SERVICE_PORT", "8001"))
    workers = int(os.getenv("AI_SERVICE_WORKERS", "1"))
    
    logger.info(f"Starting AI Service on {host}:{port}")
    
    uvicorn.run(
        "api:app",
        host=host,
        port=port,
        workers=workers,
        reload=False,
        log_level="info"
    )

