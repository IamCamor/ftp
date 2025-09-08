import React from 'react';

interface OnlineIndicatorProps {
  isOnline: boolean;
  lastSeenAt?: string;
  showText?: boolean;
  size?: 'small' | 'medium' | 'large';
  className?: string;
}

const OnlineIndicator: React.FC<OnlineIndicatorProps> = ({
  isOnline,
  lastSeenAt,
  showText = false,
  size = 'small',
  className = ''
}) => {
  const getTimeAgo = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60));

    if (diffInMinutes < 1) {
      return 'только что';
    } else if (diffInMinutes < 60) {
      return `${diffInMinutes} мин назад`;
    } else if (diffInMinutes < 1440) {
      const hours = Math.floor(diffInMinutes / 60);
      return `${hours} ч назад`;
    } else {
      const days = Math.floor(diffInMinutes / 1440);
      return `${days} дн назад`;
    }
  };

  const getSizeClass = () => {
    switch (size) {
      case 'small':
        return 'online-indicator-small';
      case 'medium':
        return 'online-indicator-medium';
      case 'large':
        return 'online-indicator-large';
      default:
        return 'online-indicator-small';
    }
  };

  if (isOnline) {
    return (
      <div className={`online-indicator online ${getSizeClass()} ${className}`}>
        <div className="online-dot"></div>
        {showText && <span className="online-text">В сети</span>}
      </div>
    );
  }

  if (lastSeenAt) {
    return (
      <div className={`online-indicator offline ${getSizeClass()} ${className}`}>
        <div className="offline-dot"></div>
        {showText && <span className="offline-text">{getTimeAgo(lastSeenAt)}</span>}
      </div>
    );
  }

  return null;
};

export default OnlineIndicator;
