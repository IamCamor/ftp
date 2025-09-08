import React from 'react';
import config from '../config';

interface AvatarProps {
  src?: string;
  alt?: string;
  size?: number;
  className?: string;
  crownIconUrl?: string;
  isPremium?: boolean;
}

const Avatar: React.FC<AvatarProps> = ({
  src,
  alt = 'Avatar',
  size = 40,
  className = '',
  crownIconUrl,
  isPremium = false
}) => {
  const avatarSrc = src || config.defaultAvatar;
  const crownSize = Math.max(size * 0.3, 12);
  
  return (
    <div className={`avatar-container ${className}`} style={{ position: 'relative', display: 'inline-block' }}>
      <img
        src={avatarSrc}
        alt={alt}
        className="rounded-full object-cover"
        style={{ width: size, height: size }}
        onError={(e) => {
          const target = e.target as HTMLImageElement;
          target.src = config.defaultAvatar;
        }}
      />
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
    </div>
  );
};

export default Avatar;

