import React from 'react';

interface IconProps {
  name: string;
  filled?: boolean;
  weight?: number;
  grade?: number;
  size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | number;
  className?: string;
  style?: React.CSSProperties;
}

const Icon: React.FC<IconProps> = ({
  name,
  filled = false,
  weight = 400,
  grade = 0,
  size = 'md',
  className = '',
  style = {}
}) => {
  // Унифицированные размеры иконок
  const sizeMap = {
    xs: 16,
    sm: 20,
    md: 24,
    lg: 28,
    xl: 32
  };

  const iconSize = typeof size === 'number' ? size : sizeMap[size];
  
  const iconStyle = {
    fontVariationSettings: `'FILL' ${filled ? 1 : 0}, 'wght' ${weight}, 'GRAD' ${grade}, 'opsz' ${iconSize}`,
    fontSize: `${iconSize}px`,
    width: `${iconSize}px`,
    height: `${iconSize}px`,
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    lineHeight: 1,
    ...style,
  };

  return (
    <span
      className={`material-symbols-rounded icon ${className}`}
      style={iconStyle}
      aria-hidden="true"
    >
      {name}
    </span>
  );
};

export default Icon;

