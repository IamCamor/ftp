import React from 'react';
import Icon from './Icon';

interface PhotoCollageProps {
  photos: string[];
  maxPhotos?: number;
  className?: string;
  onPhotoClick?: (index: number) => void;
}

const PhotoCollage: React.FC<PhotoCollageProps> = ({
  photos,
  maxPhotos = 4,
  className = '',
  onPhotoClick
}) => {
  
  if (!photos || photos.length === 0) {
    return null;
  }

  const displayPhotos = photos.slice(0, maxPhotos);
  const remainingCount = photos.length - maxPhotos;

  const getCollageLayout = (count: number) => {
    switch (count) {
      case 1:
        return 'single';
      case 2:
        return 'double';
      case 3:
        return 'triple';
      case 4:
      default:
        return 'quad';
    }
  };

  const layout = getCollageLayout(displayPhotos.length);

  const handlePhotoClick = (index: number) => {
    if (onPhotoClick) {
      onPhotoClick(index);
    }
  };

  const renderSinglePhoto = () => (
    <div className="photo-collage-single">
      <img 
        src={displayPhotos[0]} 
        alt="Фото 1"
        onClick={() => handlePhotoClick(0)}
      />
      {remainingCount > 0 && (
        <div className="photo-overlay">
          <Icon name="add" size="md" />
          <span>+{remainingCount}</span>
        </div>
      )}
    </div>
  );

  const renderDoublePhotos = () => (
    <div className="photo-collage-double">
      <div className="photo-item">
        <img 
          src={displayPhotos[0]} 
          alt="Фото 1"
          onClick={() => handlePhotoClick(0)}
        />
      </div>
      <div className="photo-item">
        <img 
          src={displayPhotos[1]} 
          alt="Фото 2"
          onClick={() => handlePhotoClick(1)}
        />
        {remainingCount > 0 && (
          <div className="photo-overlay">
            <Icon name="add" size="md" />
            <span>+{remainingCount}</span>
          </div>
        )}
      </div>
    </div>
  );

  const renderTriplePhotos = () => (
    <div className="photo-collage-triple">
      <div className="photo-item photo-item-large">
        <img 
          src={displayPhotos[0]} 
          alt="Фото 1"
          onClick={() => handlePhotoClick(0)}
        />
      </div>
      <div className="photo-item">
        <img 
          src={displayPhotos[1]} 
          alt="Фото 2"
          onClick={() => handlePhotoClick(1)}
        />
      </div>
      <div className="photo-item">
        <img 
          src={displayPhotos[2]} 
          alt="Фото 3"
          onClick={() => handlePhotoClick(2)}
        />
        {remainingCount > 0 && (
          <div className="photo-overlay">
            <Icon name="add" size="sm" />
            <span>+{remainingCount}</span>
          </div>
        )}
      </div>
    </div>
  );

  const renderQuadPhotos = () => (
    <div className="photo-collage-quad">
      <div className="photo-item">
        <img 
          src={displayPhotos[0]} 
          alt="Фото 1"
          onClick={() => handlePhotoClick(0)}
        />
      </div>
      <div className="photo-item">
        <img 
          src={displayPhotos[1]} 
          alt="Фото 2"
          onClick={() => handlePhotoClick(1)}
        />
      </div>
      <div className="photo-item">
        <img 
          src={displayPhotos[2]} 
          alt="Фото 3"
          onClick={() => handlePhotoClick(2)}
        />
      </div>
      <div className="photo-item">
        <img 
          src={displayPhotos[3]} 
          alt="Фото 4"
          onClick={() => handlePhotoClick(3)}
        />
        {remainingCount > 0 && (
          <div className="photo-overlay">
            <Icon name="add" size="sm" />
            <span>+{remainingCount}</span>
          </div>
        )}
      </div>
    </div>
  );

  const renderCollage = () => {
    switch (layout) {
      case 'single':
        return renderSinglePhoto();
      case 'double':
        return renderDoublePhotos();
      case 'triple':
        return renderTriplePhotos();
      case 'quad':
      default:
        return renderQuadPhotos();
    }
  };

  return (
    <div className={`photo-collage ${className}`}>
      {renderCollage()}
    </div>
  );
};

export default PhotoCollage;
