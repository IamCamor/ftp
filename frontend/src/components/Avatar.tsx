import React from 'react';
import config from '../config';

interface AvatarProps {
  src?: string;
  alt?: string;
  size?: number;
  className?: string;
}

const Avatar: React.FC<AvatarProps> = ({
  src,
  alt = 'Avatar',
  size = 40,
  className = ''
}) => {
  const avatarSrc = src || config.defaultAvatar;
  
  return (
    <img
      src={avatarSrc}
      alt={alt}
      className={`rounded-full object-cover ${className}`}
      style={{ width: size, height: size }}
      onError={(e) => {
        const target = e.target as HTMLImageElement;
        target.src = config.defaultAvatar;
      }}
    />
  );
};

export default Avatar;

