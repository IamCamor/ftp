import React from 'react';
import Icon from './Icon';

interface FeedFiltersProps {
  activeFilter: 'all' | 'following' | 'nearby';
  onFilterChange: (filter: 'all' | 'following' | 'nearby') => void;
  className?: string;
}

const FeedFilters: React.FC<FeedFiltersProps> = ({
  activeFilter,
  onFilterChange,
  className = ''
}) => {
  const filters = [
    {
      key: 'all' as const,
      label: 'Все',
      icon: 'public',
      description: 'Все уловы'
    },
    {
      key: 'following' as const,
      label: 'Подписки',
      icon: 'people',
      description: 'Уловы подписок'
    },
    {
      key: 'nearby' as const,
      label: 'Рядом',
      icon: 'location_on',
      description: 'Уловы поблизости'
    }
  ];

  return (
    <div className={`feed-filters ${className}`}>
      <div className="filters-container">
        {filters.map((filter) => (
          <button
            key={filter.key}
            className={`filter-button ${activeFilter === filter.key ? 'active' : ''}`}
            onClick={() => onFilterChange(filter.key)}
            title={filter.description}
          >
            <Icon name={filter.icon} />
            <span className="filter-label">{filter.label}</span>
          </button>
        ))}
      </div>
    </div>
  );
};

export default FeedFilters;
