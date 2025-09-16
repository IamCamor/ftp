import React from 'react';
import { useNavigate } from 'react-router-dom';
import type { SearchResult } from '../types';
import Icon from './Icon';
import Avatar from './Avatar';

interface SearchResultCardProps {
  result: SearchResult;
  onResultClick?: (result: SearchResult) => void;
}

const SearchResultCard: React.FC<SearchResultCardProps> = ({ result, onResultClick }) => {
  const navigate = useNavigate();

  const getResultIcon = (type: string) => {
    const icons = {
      catch: 'fishing',
      event: 'event',
      track: 'route',
      user: 'person',
      point: 'place',
      fish_species: 'pets',
      fishing_method: 'build',
      bait: 'bug_report',
      location: 'location_on'
    };
    return icons[type as keyof typeof icons] || 'search';
  };

  const getResultUrl = (result: SearchResult) => {
    switch (result.type) {
      case 'catch':
        return `/catches/${result.id}`;
      case 'event':
        return `/events/${result.id}`;
      case 'track':
        return `/tracks/${result.id}`;
      case 'user':
        return `/users/${result.id}`;
      case 'point':
        return `/points/${result.id}`;
      case 'fish_species':
        return `/reference/fish/${result.id}`;
      case 'fishing_method':
        return `/reference/methods/${result.id}`;
      case 'bait':
        return `/reference/baits/${result.id}`;
      case 'location':
        return `/reference/locations/${result.id}`;
      default:
        return '#';
    }
  };

  const getTypeLabel = (type: string) => {
    const labels = {
      catch: 'Улов',
      event: 'Мероприятие',
      track: 'Трек',
      user: 'Пользователь',
      point: 'Место',
      fish_species: 'Вид рыбы',
      fishing_method: 'Метод ловли',
      bait: 'Наживка',
      location: 'Место ловли'
    };
    return labels[type as keyof typeof labels] || 'Результат';
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  const handleClick = () => {
    if (onResultClick) {
      onResultClick(result);
    } else {
      navigate(getResultUrl(result));
    }
  };

  return (
    <div className="search-result-card" onClick={handleClick}>
      <div className="result-image">
        {result.image ? (
          <img src={result.image} alt={result.title} />
        ) : (
          <div className="result-icon">
            <Icon name={getResultIcon(result.type)} size="md" />
          </div>
        )}
      </div>
      
      <div className="result-content">
        <div className="result-header">
          <h3 className="result-title">{result.title}</h3>
          <span className="result-type">
            <Icon name={getResultIcon(result.type)} size="sm" />
            {getTypeLabel(result.type)}
          </span>
        </div>
        
        {result.description && (
          <p className="result-description">{result.description}</p>
        )}
        
        <div className="result-meta">
          {result.user && (
            <div className="result-user">
              <Avatar 
                src={result.user.photo_url} 
                size="md" 
                name={result.user.name}
                isGuide={result.user.is_guide}
                guideIconUrl={result.user.guide_icon_url}
              />
              <span>{result.user.name}</span>
            </div>
          )}
          
          <div className="result-date">
            <Icon name="schedule" size="sm" />
            <span>{formatDate(result.created_at)}</span>
          </div>
        </div>
      </div>
    </div>
  );
};

export default SearchResultCard;
