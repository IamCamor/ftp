import React from 'react';
import { useNavigate } from 'react-router-dom';
import Icon from './Icon';

interface ReferenceCardProps {
  id: number;
  name: string;
  description?: string;
  photoUrl?: string;
  type: 'fish_species' | 'fishing_knots' | 'boats' | 'fishing_methods' | 'fishing_tackle' | 'boat_engines' | 'fishing_locations';
  slug: string;
  viewCount?: number;
  additionalInfo?: Record<string, any>;
  className?: string;
}

const ReferenceCard: React.FC<ReferenceCardProps> = ({
  name,
  description,
  photoUrl,
  type,
  slug,
  viewCount = 0,
  additionalInfo = {},
  className = ''
}) => {
  const navigate = useNavigate();

  const handleClick = () => {
    navigate(`/reference/${type}/${slug}`);
  };

  const getTypeIcon = () => {
    switch (type) {
      case 'fish_species':
        return 'fish';
      case 'fishing_knots':
        return 'knot';
      case 'boats':
        return 'boat';
      case 'fishing_methods':
        return 'fishing';
      case 'fishing_tackle':
        return 'tackle';
      case 'boat_engines':
        return 'engine';
      case 'fishing_locations':
        return 'location';
      default:
        return 'info';
    }
  };

  const getTypeName = () => {
    switch (type) {
      case 'fish_species':
        return 'Вид рыбы';
      case 'fishing_knots':
        return 'Узел';
      case 'boats':
        return 'Лодка';
      case 'fishing_methods':
        return 'Способ ловли';
      case 'fishing_tackle':
        return 'Снасть';
      case 'boat_engines':
        return 'Мотор';
      case 'fishing_locations':
        return 'Место';
      default:
        return 'Справочник';
    }
  };

  const formatViewCount = (count: number) => {
    if (count >= 1000) {
      return `${(count / 1000).toFixed(1)}k`;
    }
    return count.toString();
  };

  return (
    <div 
      className={`reference-card ${className}`}
      onClick={handleClick}
    >
      <div className="reference-image">
        {photoUrl ? (
          <img 
            src={photoUrl} 
            alt={name}
            className="reference-photo"
            onError={(e) => {
              const target = e.target as HTMLImageElement;
              target.style.display = 'none';
              target.nextElementSibling?.classList.remove('hidden');
            }}
          />
        ) : null}
        <div className={`reference-icon ${photoUrl ? 'hidden' : ''}`}>
          <Icon name={getTypeIcon()} />
        </div>
      </div>
      
      <div className="reference-content">
        <div className="reference-header">
          <h3 className="reference-title">{name}</h3>
          <span className="reference-type">{getTypeName()}</span>
        </div>
        
        {description && (
          <p className="reference-description">
            {description.length > 100 
              ? `${description.substring(0, 100)}...` 
              : description}
          </p>
        )}
        
        <div className="reference-footer">
          <div className="reference-stats">
            <Icon name="visibility" />
            <span>{formatViewCount(viewCount)}</span>
          </div>
          
          {additionalInfo && Object.keys(additionalInfo).length > 0 && (
            <div className="reference-additional-info">
              {additionalInfo.category && (
                <span className="info-badge category">
                  {additionalInfo.category}
                </span>
              )}
              {additionalInfo.difficulty && (
                <span className="info-badge difficulty">
                  {additionalInfo.difficulty}
                </span>
              )}
              {additionalInfo.price_range && (
                <span className="info-badge price">
                  {additionalInfo.price_range}
                </span>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default ReferenceCard;
