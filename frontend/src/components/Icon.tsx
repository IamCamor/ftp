import React from 'react';

interface IconProps {
  name: string;
  filled?: boolean;
  weight?: number;
  grade?: number;
  size?: number;
  className?: string;
}

const Icon: React.FC<IconProps> = ({
  name,
  filled = false,
  weight = 400,
  grade = 0,
  size = 24,
  className = ''
}) => {
  const style = {
    fontVariationSettings: `'FILL' ${filled ? 1 : 0}, 'wght' ${weight}, 'GRAD' ${grade}, 'opsz' ${size}`,
    fontSize: `${size}px`,
  };

  return (
    <span
      className={`material-symbols-rounded ${className}`}
      style={style}
    >
      {name}
    </span>
  );
};

export default Icon;

