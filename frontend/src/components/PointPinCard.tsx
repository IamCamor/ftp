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

  const handleDetailsClick = (e: React.MouseEvent) => {
    e.stopPropagation();
    if (point.type === 'catch') {
      navigate(config.routes.catchDetail(point.id));
    } else {
      navigate(config.routes.placeDetail(point.id));
    }
  };

  const isCatch = point.type === 'catch';

  return (
    <div className="point-pin-card glass">
      <div className="card-header">
        <div className="user-info">
          <Avatar src={point.user.photo_url} size="lg" name={point.user.name} />
          <div className="user-details">
            <span className="user-name">{point.user.name}</span>
            {point.user.username && (
              <span className="user-username">@{point.user.username}</span>
            )}
          </div>
        </div>
        {onClose && (
          <button className="close-button" onClick={onClose} aria-label="Закрыть">
            <Icon name="close" size="md" />
          </button>
        )}
      </div>

      <div className="card-content">
        {point.cover_url && (
          <img src={point.cover_url} alt={point.title} className="point-cover" />
        )}
        
        <div className="point-info">
          <h3 className="point-title">{point.title}</h3>
          {point.description && (
            <p className="point-description">{point.description}</p>
          )}
          
          {/* Дополнительная информация для уловов */}
          {isCatch && (
            <div className="catch-details">
              {point.species && (
                <div className="catch-species">
                  <Icon name="pets" size="sm" />
                  <span>{point.species}</span>
                </div>
              )}
              {point.weight && (
                <div className="catch-weight">
                  <Icon name="scale" size="sm" />
                  <span>{point.weight} кг</span>
                </div>
              )}
              {point.length && (
                <div className="catch-length">
                  <Icon name="straighten" size="sm" />
                  <span>{point.length} см</span>
                </div>
              )}
            </div>
          )}
          
          <div className="point-meta">
            <div className="meta-item">
              <Icon name="location_on" size="sm" />
              <span>{Number(point.lat).toFixed(4)}, {Number(point.lng).toFixed(4)}</span>
            </div>
            {point.media_count && point.media_count > 0 && (
              <div className="meta-item">
                <Icon name="photo_library" size="sm" />
                <span>{point.media_count} фото</span>
              </div>
            )}
          </div>
        </div>
      </div>

      <div className="card-actions">
        <button 
          className="btn btn-primary btn-sm details-button"
          onClick={handleDetailsClick}
        >
          <Icon name="visibility" size="sm" />
          Подробнее
        </button>
      </div>
    </div>
  );
};

export default PointPinCard;

