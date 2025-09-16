#!/usr/bin/env node

/**
 * Test script for FishTrackPro AI Service
 * Tests the AI service endpoints and functionality
 */

const fs = require('fs');
const path = require('path');
const FormData = require('form-data');
const axios = require('axios');

// Configuration
const AI_SERVICE_URL = 'http://localhost:8001';
const BACKEND_URL = 'http://localhost:8000/api';

// Test image (create a simple test image)
const testImagePath = path.join(__dirname, 'test-image.png');

async function createTestImage() {
    // Create a simple test image using canvas or use an existing image
    // For now, we'll create a simple colored rectangle
    const { createCanvas } = require('canvas');
    
    try {
        const canvas = createCanvas(200, 200);
        const ctx = canvas.getContext('2d');
        
        // Draw a simple blue rectangle (representing water)
        ctx.fillStyle = '#0066cc';
        ctx.fillRect(0, 0, 200, 200);
        
        // Add some text
        ctx.fillStyle = 'white';
        ctx.font = '20px Arial';
        ctx.fillText('Test Image', 50, 100);
        
        const buffer = canvas.toBuffer('image/png');
        fs.writeFileSync(testImagePath, buffer);
        
        console.log('✅ Test image created');
        return true;
    } catch (error) {
        console.log('⚠️  Canvas not available, using placeholder image');
        // Create a simple placeholder file
        fs.writeFileSync(testImagePath, 'placeholder');
        return true;
    }
}

async function testAIServiceHealth() {
    console.log('\n🔍 Testing AI Service Health...');
    
    try {
        const response = await axios.get(`${AI_SERVICE_URL}/health`);
        console.log('✅ AI Service is healthy:', response.data);
        return true;
    } catch (error) {
        console.log('❌ AI Service health check failed:', error.message);
        return false;
    }
}

async function testImageAnalysis() {
    console.log('\n🔍 Testing Image Analysis...');
    
    try {
        // Read test image and convert to base64
        const imageData = fs.readFileSync(testImagePath);
        const base64Image = imageData.toString('base64');
        
        const response = await axios.post(`${AI_SERVICE_URL}/analyze`, {
            image_data: base64Image,
            content_type: 'catch'
        });
        
        console.log('✅ Image analysis successful:', response.data);
        return true;
    } catch (error) {
        console.log('❌ Image analysis failed:', error.message);
        return false;
    }
}

async function testContentModeration() {
    console.log('\n🔍 Testing Content Moderation...');
    
    try {
        // Read test image and convert to base64
        const imageData = fs.readFileSync(testImagePath);
        const base64Image = imageData.toString('base64');
        
        const response = await axios.post(`${AI_SERVICE_URL}/moderate`, {
            image_data: base64Image,
            content_type: 'catch'
        });
        
        console.log('✅ Content moderation successful:', response.data);
        return true;
    } catch (error) {
        console.log('❌ Content moderation failed:', error.message);
        return false;
    }
}

async function testAltTextGeneration() {
    console.log('\n🔍 Testing Alt Text Generation...');
    
    try {
        // Read test image and convert to base64
        const imageData = fs.readFileSync(testImagePath);
        const base64Image = imageData.toString('base64');
        
        const response = await axios.post(`${AI_SERVICE_URL}/alt-text`, {
            image_data: base64Image,
            context: 'Fishing catch'
        });
        
        console.log('✅ Alt text generation successful:', response.data);
        return true;
    } catch (error) {
        console.log('❌ Alt text generation failed:', error.message);
        return false;
    }
}

async function testSpeciesDetection() {
    console.log('\n🔍 Testing Species Detection...');
    
    try {
        // Read test image and convert to base64
        const imageData = fs.readFileSync(testImagePath);
        const base64Image = imageData.toString('base64');
        
        const response = await axios.post(`${AI_SERVICE_URL}/detect-species`, {
            image_data: base64Image
        });
        
        console.log('✅ Species detection successful:', response.data);
        return true;
    } catch (error) {
        console.log('❌ Species detection failed:', error.message);
        return false;
    }
}

async function testBackendIntegration() {
    console.log('\n🔍 Testing Backend Integration...');
    
    try {
        // Test backend AI health endpoint
        const healthResponse = await axios.get(`${BACKEND_URL}/ai/health`);
        console.log('✅ Backend AI health check:', healthResponse.data);
        
        // Test backend AI config endpoint
        const configResponse = await axios.get(`${BACKEND_URL}/ai/config`);
        console.log('✅ Backend AI config:', configResponse.data);
        
        return true;
    } catch (error) {
        console.log('❌ Backend integration test failed:', error.message);
        return false;
    }
}

async function testFileUpload() {
    console.log('\n🔍 Testing File Upload...');
    
    try {
        const form = new FormData();
        form.append('image', fs.createReadStream(testImagePath));
        form.append('content_type', 'catch');
        
        const response = await axios.post(`${AI_SERVICE_URL}/upload-analyze`, form, {
            headers: form.getHeaders()
        });
        
        console.log('✅ File upload test successful:', response.data);
        return true;
    } catch (error) {
        console.log('❌ File upload test failed:', error.message);
        return false;
    }
}

async function runAllTests() {
    console.log('🚀 Starting FishTrackPro AI Service Tests\n');
    
    // Create test image
    await createTestImage();
    
    const tests = [
        { name: 'AI Service Health', fn: testAIServiceHealth },
        { name: 'Image Analysis', fn: testImageAnalysis },
        { name: 'Content Moderation', fn: testContentModeration },
        { name: 'Alt Text Generation', fn: testAltTextGeneration },
        { name: 'Species Detection', fn: testSpeciesDetection },
        { name: 'File Upload', fn: testFileUpload },
        { name: 'Backend Integration', fn: testBackendIntegration }
    ];
    
    let passed = 0;
    let total = tests.length;
    
    for (const test of tests) {
        try {
            const result = await test.fn();
            if (result) {
                passed++;
            }
        } catch (error) {
            console.log(`❌ ${test.name} failed with error:`, error.message);
        }
    }
    
    console.log(`\n📊 Test Results: ${passed}/${total} tests passed`);
    
    if (passed === total) {
        console.log('🎉 All tests passed! AI Service is working correctly.');
    } else {
        console.log('⚠️  Some tests failed. Check the logs above for details.');
    }
    
    // Clean up test image
    if (fs.existsSync(testImagePath)) {
        fs.unlinkSync(testImagePath);
        console.log('🧹 Test image cleaned up');
    }
}

// Run tests if this script is executed directly
if (require.main === module) {
    runAllTests().catch(console.error);
}

module.exports = {
    runAllTests,
    testAIServiceHealth,
    testImageAnalysis,
    testContentModeration,
    testAltTextGeneration,
    testSpeciesDetection,
    testBackendIntegration,
    testFileUpload
};

