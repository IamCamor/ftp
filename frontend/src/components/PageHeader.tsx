import React from 'react';
import { useNavigate } from 'react-router-dom';
import Icon from './Icon';

interface PageHeaderProps {
  title: string;
  showBack?: boolean;
  onBack?: () => void;
  className?: string;
}

const PageHeader: React.FC<PageHeaderProps> = ({
  title,
  showBack = false,
  onBack,
  className = ''
}) => {
  const navigate = useNavigate();

  const handleBack = () => {
    if (onBack) {
      onBack();
    } else {
      navigate(-1);
    }
  };

  return (
    <header className={`page-header ${className}`}>
      <div className="header-content">
        {showBack && (
          <button className="back-button" onClick={handleBack}>
            <Icon name="arrow_back" size={24} />
          </button>
        )}
        <h1 className="page-title">{title}</h1>
      </div>
    </header>
  );
};

export default PageHeader;
