import React from 'react';
import { useTheme } from '../hooks/useTheme';
import Icon from './Icon';

interface ThemeToggleProps {
  className?: string;
  showLabel?: boolean;
  size?: number | 'xs' | 'sm' | 'md' | 'lg' | 'xl';
}

const ThemeToggle: React.FC<ThemeToggleProps> = ({ 
  className = '', 
  showLabel = false,
  size = 24 
}) => {
  const { toggleTheme, isDark } = useTheme();
  
  // Унифицированные размеры для ThemeToggle
  const sizeMap = {
    xs: 16,
    sm: 20,
    md: 24,
    lg: 28,
    xl: 32
  };

  const iconSize = typeof size === 'number' ? size : sizeMap[size];

  return (
    <button
      className={`theme-toggle ${className}`}
      onClick={toggleTheme}
      aria-label={`Переключить на ${isDark ? 'светлую' : 'тёмную'} тему`}
      title={`Переключить на ${isDark ? 'светлую' : 'тёмную'} тему`}
    >
      <Icon 
        name={isDark ? 'light_mode' : 'dark_mode'} 
        size={iconSize}
        filled={true}
      />
      {showLabel && (
        <span className="theme-toggle-label">
          {isDark ? 'Светлая' : 'Тёмная'}
        </span>
      )}
    </button>
  );
};

export default ThemeToggle;
