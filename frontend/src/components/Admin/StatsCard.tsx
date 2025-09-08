import React from 'react';
import Icon from '../Icon';

interface StatsCardProps {
  title: string;
  value: number;
  icon: string;
  color?: string;
  trend?: {
    value: number;
    isPositive: boolean;
  };
}

const StatsCard: React.FC<StatsCardProps> = ({ 
  title, 
  value, 
  icon, 
  color = 'primary',
  trend 
}) => {
  return (
    <div className={`stats-card stats-card--${color}`}>
      <div className="stats-card__icon">
        <Icon name={icon} />
      </div>
      <div className="stats-card__content">
        <h3 className="stats-card__title">{title}</h3>
        <div className="stats-card__value">{value.toLocaleString()}</div>
        {trend && (
          <div className={`stats-card__trend ${trend.isPositive ? 'positive' : 'negative'}`}>
            <Icon name={trend.isPositive ? 'trending_up' : 'trending_down'} />
            <span>{Math.abs(trend.value)}%</span>
          </div>
        )}
      </div>
    </div>
  );
};

export default StatsCard;
