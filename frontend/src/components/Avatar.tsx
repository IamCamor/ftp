import React from 'react';
import config from '../config';

interface AvatarProps {
  src?: string;
  alt?: string;
  size?: number | 'xs' | 'sm' | 'md' | 'lg' | 'xl';
  className?: string;
  crownIconUrl?: string;
  isPremium?: boolean;
  isGuide?: boolean;
  guideIconUrl?: string;
  name?: string; // Имя пользователя для генерации инициалов
}

const Avatar: React.FC<AvatarProps> = ({
  src,
  alt = 'Avatar',
  size = 40,
  className = '',
  crownIconUrl,
  isPremium = false,
  isGuide = false,
  guideIconUrl,
  name
}) => {
  // Унифицированные размеры аватаров
  const sizeMap = {
    xs: 24,
    sm: 32,
    md: 40,
    lg: 48,
    xl: 64
  };

  const avatarSize = typeof size === 'number' ? size : sizeMap[size];
  const crownSize = Math.max(avatarSize * 0.3, 12);
  
  // Генерируем инициалы из имени
  const getInitials = (name: string): string => {
    if (!name) return '?';
    
    const words = name.trim().split(/\s+/);
    if (words.length === 1) {
      return words[0].substring(0, 2).toUpperCase();
    }
    
    const firstInitial = words[0].charAt(0).toUpperCase();
    const lastInitial = words[words.length - 1].charAt(0).toUpperCase();
    return firstInitial + lastInitial;
  };

  // Генерируем цвет на основе имени
  const getAvatarColor = (name: string): string => {
    if (!name) return '#6B7280';
    
    const colors = [
      '#EF4444', '#F97316', '#F59E0B', '#EAB308', '#84CC16',
      '#22C55E', '#10B981', '#14B8A6', '#06B6D4', '#0EA5E9',
      '#3B82F6', '#6366F1', '#8B5CF6', '#A855F7', '#D946EF',
      '#EC4899', '#F43F5E', '#6B7280', '#374151', '#1F2937'
    ];
    
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
      hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    
    return colors[Math.abs(hash) % colors.length];
  };

  const hasValidSrc = src && src !== config.defaultAvatar;
  const initials = name ? getInitials(name) : '?';
  const backgroundColor = name ? getAvatarColor(name) : '#6B7280';
  const fontSize = Math.max(avatarSize * 0.4, 12);
  
  // Ensure the src URL is complete with base URL
  const fullSrc = src && src.startsWith('http') ? src : `${config.siteBase}${src || ''}`;

  return (
    <div className={`avatar-container ${className}`} style={{ position: 'relative', display: 'inline-block' }}>
      {hasValidSrc ? (
        <img
          src={fullSrc}
          alt={alt}
          className="rounded-full object-cover"
          style={{ width: avatarSize, height: avatarSize }}
          onError={(e) => {
            const target = e.target as HTMLImageElement;
            target.style.display = 'none';
            // Показываем инициалы при ошибке загрузки
            const parent = target.parentElement;
            if (parent) {
              const initialsDiv = parent.querySelector('.avatar-initials') as HTMLElement;
              if (initialsDiv) {
                initialsDiv.style.display = 'flex';
              }
            }
          }}
        />
      ) : null}
      
      <div
        className="avatar-initials"
        style={{
          display: hasValidSrc ? 'none' : 'flex',
          width: avatarSize,
          height: avatarSize,
          borderRadius: '50%',
          backgroundColor: backgroundColor,
          color: 'white',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: fontSize,
          fontWeight: '600',
          fontFamily: 'system-ui, -apple-system, sans-serif',
          textTransform: 'uppercase',
          letterSpacing: '0.5px'
        }}
      >
        {initials}
      </div>
      
      {isPremium && crownIconUrl && (
        <img
          src={crownIconUrl}
          alt="Crown"
          className="crown-icon"
          style={{
            position: 'absolute',
            top: -crownSize * 0.3,
            right: -crownSize * 0.3,
            width: crownSize,
            height: crownSize,
            zIndex: 1
          }}
        />
      )}
      
      {isGuide && guideIconUrl && (
        <img
          src={guideIconUrl}
          alt="Guide"
          className="guide-icon"
          style={{
            position: 'absolute',
            bottom: -crownSize * 0.2,
            right: -crownSize * 0.2,
            width: crownSize,
            height: crownSize,
            zIndex: 1
          }}
        />
      )}
    </div>
  );
};

export default Avatar;

