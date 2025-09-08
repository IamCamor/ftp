import React from 'react';
import { useNavigate } from 'react-router-dom';
import Avatar from './Avatar';
import Icon from './Icon';
import type { Point } from '../types';
import config from '../config';

interface PointPinCardProps {
  point: Point;
  onClose?: () => void;
}

const PointPinCard: React.FC<PointPinCardProps> = ({ point, onClose }) => {
  const navigate = useNavigate();

  const handleCardClick = () => {
    navigate(config.routes.placeDetail(point.id));
  };

  return (
    <div className="point-pin-card glass">
      <div className="card-header">
        <div className="user-info">
          <Avatar src={point.user.photo_url} size={32} />
          <div className="user-details">
            <span className="user-name">{point.user.name}</span>
            {point.user.username && (
              <span className="user-username">@{point.user.username}</span>
            )}
          </div>
        </div>
        {onClose && (
          <button className="close-button" onClick={onClose} aria-label="Закрыть">
            <Icon name="close" size={20} />
          </button>
        )}
      </div>

      <div className="card-content" onClick={handleCardClick}>
        {point.cover_url && (
          <img src={point.cover_url} alt={point.title} className="point-cover" />
        )}
        
        <div className="point-info">
          <h3 className="point-title">{point.title}</h3>
          {point.description && (
            <p className="point-description">{point.description}</p>
          )}
          
          <div className="point-meta">
            <div className="meta-item">
              <Icon name="location_on" size={16} />
              <span>{point.lat.toFixed(4)}, {point.lng.toFixed(4)}</span>
            </div>
            {point.media_count && point.media_count > 0 && (
              <div className="meta-item">
                <Icon name="photo_library" size={16} />
                <span>{point.media_count} фото</span>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default PointPinCard;

