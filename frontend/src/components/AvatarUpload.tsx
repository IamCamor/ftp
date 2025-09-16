import React, { useState, useRef } from 'react';
import Icon from './Icon';
import { uploadAvatar, deleteAvatar } from '../api';
import type { User } from '../types';
import config from '../config';

interface AvatarUploadProps {
  user: User;
  onAvatarUpdate: (photoUrl: string | null) => void;
  size?: number | 'xs' | 'sm' | 'md' | 'lg' | 'xl';
  className?: string;
}

const AvatarUpload: React.FC<AvatarUploadProps> = ({
  user,
  onAvatarUpdate,
  size = 80,
  className = ''
}) => {
  const [isUploading, setIsUploading] = useState(false);
  
  // Унифицированные размеры для AvatarUpload
  const sizeMap = {
    xs: 40,
    sm: 60,
    md: 80,
    lg: 100,
    xl: 120
  };

  const avatarSize = typeof size === 'number' ? size : sizeMap[size];
  const [error, setError] = useState<string | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const videoRef = useRef<HTMLVideoElement>(null);
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const [showCamera, setShowCamera] = useState(false);
  const [stream, setStream] = useState<MediaStream | null>(null);

  const handleFileSelect = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) return;

    // Validate file type
    if (!file.type.startsWith('image/')) {
      setError('Пожалуйста, выберите изображение');
      return;
    }

    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
      setError('Размер файла не должен превышать 5MB');
      return;
    }

    await uploadFile(file);
  };

  const handleCameraClick = async () => {
    try {
      const mediaStream = await navigator.mediaDevices.getUserMedia({
        video: { 
          facingMode: 'user',
          width: { ideal: 400 },
          height: { ideal: 400 }
        }
      });
      
      setStream(mediaStream);
      setShowCamera(true);
      setError(null);
      
      if (videoRef.current) {
        videoRef.current.srcObject = mediaStream;
      }
    } catch (err) {
      setError('Не удалось получить доступ к камере');
      console.error('Camera access error:', err);
    }
  };

  const capturePhoto = () => {
    if (!videoRef.current || !canvasRef.current) return;

    const video = videoRef.current;
    const canvas = canvasRef.current;
    const context = canvas.getContext('2d');

    if (!context) return;

    // Set canvas size to match video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    // Draw video frame to canvas
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    // Convert canvas to blob
    canvas.toBlob(async (blob) => {
      if (!blob) return;

      // Create file from blob
      const file = new File([blob], 'camera-photo.jpg', { type: 'image/jpeg' });
      await uploadFile(file);
      
      // Stop camera and close modal
      stopCamera();
    }, 'image/jpeg', 0.8);
  };

  const stopCamera = () => {
    if (stream) {
      stream.getTracks().forEach(track => track.stop());
      setStream(null);
    }
    setShowCamera(false);
  };

  const uploadFile = async (file: File) => {
    try {
      setIsUploading(true);
      setError(null);

      const response = await uploadAvatar(file);
      // Ensure the photo URL is complete with base URL
      const fullPhotoUrl = response.photo_url.startsWith('http') 
        ? response.photo_url 
        : `${config.siteBase}${response.photo_url}`;
      onAvatarUpdate(fullPhotoUrl);
    } catch (err) {
      setError('Ошибка при загрузке аватарки');
      console.error('Avatar upload error:', err);
    } finally {
      setIsUploading(false);
    }
  };

  const handleDeleteAvatar = async () => {
    if (!user.photo_url) return;

    if (!confirm('Вы уверены, что хотите удалить аватарку?')) return;

    try {
      setIsUploading(true);
      setError(null);

      await deleteAvatar();
      onAvatarUpdate(null);
    } catch (err) {
      setError('Ошибка при удалении аватарки');
      console.error('Avatar delete error:', err);
    } finally {
      setIsUploading(false);
    }
  };

  const openFileDialog = () => {
    fileInputRef.current?.click();
  };

  return (
    <div className={`avatar-upload ${className}`}>
      <div className="avatar-container" style={{ position: 'relative' }}>
        <div 
          className="avatar-preview"
          style={{
            width: avatarSize,
            height: avatarSize,
            borderRadius: '50%',
            overflow: 'hidden',
            position: 'relative',
            cursor: 'pointer',
            border: '2px solid #e5e7eb',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            backgroundColor: '#f3f4f6'
          }}
          onClick={openFileDialog}
        >
          {user.photo_url ? (
            <img
              src={user.photo_url.startsWith('http') ? user.photo_url : `${config.siteBase}${user.photo_url}`}
              alt="Avatar"
              style={{
                width: '100%',
                height: '100%',
                objectFit: 'cover'
              }}
              onError={(e) => {
                console.error('Avatar image load error:', e);
                // Fallback to initials if image fails to load
                const target = e.target as HTMLImageElement;
                target.style.display = 'none';
              }}
            />
          ) : (
            <div style={{
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
              justifyContent: 'center',
              color: '#6b7280',
              fontSize: avatarSize * 0.3
            }}>
              <Icon name="person" size={avatarSize * 0.4} />
              <span style={{ fontSize: '12px', marginTop: '4px' }}>Фото</span>
            </div>
          )}
          
          {isUploading && (
            <div style={{
              position: 'absolute',
              top: 0,
              left: 0,
              right: 0,
              bottom: 0,
              backgroundColor: 'rgba(0, 0, 0, 0.5)',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              borderRadius: '50%'
            }}>
              <div className="spinner" style={{
                width: '24px',
                height: '24px',
                border: '2px solid #ffffff',
                borderTop: '2px solid transparent',
                borderRadius: '50%',
                animation: 'spin 1s linear infinite'
              }} />
            </div>
          )}
        </div>

        {/* Upload buttons */}
        <div className="avatar-actions" style={{
          position: 'absolute',
          bottom: '-8px',
          right: '-8px',
          display: 'flex',
          gap: '4px'
        }}>
          <button
            type="button"
            onClick={openFileDialog}
            disabled={isUploading}
            style={{
              width: '32px',
              height: '32px',
              borderRadius: '50%',
              border: 'none',
              backgroundColor: '#3b82f6',
              color: 'white',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              cursor: 'pointer',
              boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)'
            }}
            title="Загрузить фото"
          >
            <Icon name="photo_camera" size="sm" />
          </button>

          <button
            type="button"
            onClick={handleCameraClick}
            disabled={isUploading}
            style={{
              width: '32px',
              height: '32px',
              borderRadius: '50%',
              border: 'none',
              backgroundColor: '#10b981',
              color: 'white',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              cursor: 'pointer',
              boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)'
            }}
            title="Сделать снимок"
          >
            <Icon name="camera_alt" size="sm" />
          </button>

          {user.photo_url && (
            <button
              type="button"
              onClick={handleDeleteAvatar}
              disabled={isUploading}
              style={{
                width: '32px',
                height: '32px',
                borderRadius: '50%',
                border: 'none',
                backgroundColor: '#ef4444',
                color: 'white',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                cursor: 'pointer',
                boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)'
              }}
              title="Удалить фото"
            >
              <Icon name="delete" size="sm" />
            </button>
          )}
        </div>
      </div>

      {/* Hidden file input */}
      <input
        ref={fileInputRef}
        type="file"
        accept="image/*"
        onChange={handleFileSelect}
        style={{ display: 'none' }}
      />

      {/* Error message */}
      {error && (
        <div style={{
          color: '#ef4444',
          fontSize: '14px',
          marginTop: '8px',
          textAlign: 'center'
        }}>
          {error}
        </div>
      )}

      {/* Camera Modal */}
      {showCamera && (
        <div style={{
          position: 'fixed',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          justifyContent: 'center',
          zIndex: 1000
        }}>
          <div style={{
            backgroundColor: 'white',
            borderRadius: '12px',
            padding: '20px',
            maxWidth: '400px',
            width: '90%',
            textAlign: 'center'
          }}>
            <h3 style={{ margin: '0 0 16px 0', color: '#1f2937' }}>
              Сделать снимок
            </h3>
            
            <video
              ref={videoRef}
              autoPlay
              playsInline
              style={{
                width: '100%',
                maxWidth: '300px',
                height: '300px',
                objectFit: 'cover',
                borderRadius: '8px',
                backgroundColor: '#000'
              }}
            />
            
            <canvas
              ref={canvasRef}
              style={{ display: 'none' }}
            />
            
            <div style={{
              display: 'flex',
              gap: '12px',
              justifyContent: 'center',
              marginTop: '16px'
            }}>
              <button
                onClick={capturePhoto}
                style={{
                  padding: '12px 24px',
                  backgroundColor: '#3b82f6',
                  color: 'white',
                  border: 'none',
                  borderRadius: '8px',
                  cursor: 'pointer',
                  fontSize: '16px',
                  fontWeight: '500'
                }}
              >
                <Icon name="camera_alt" size="md" />
                Сделать снимок
              </button>
              
              <button
                onClick={stopCamera}
                style={{
                  padding: '12px 24px',
                  backgroundColor: '#6b7280',
                  color: 'white',
                  border: 'none',
                  borderRadius: '8px',
                  cursor: 'pointer',
                  fontSize: '16px',
                  fontWeight: '500'
                }}
              >
                Отмена
              </button>
            </div>
          </div>
        </div>
      )}

      <style>{`
        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
      `}</style>
    </div>
  );
};

export default AvatarUpload;
