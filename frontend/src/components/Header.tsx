import React from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import Icon from './Icon';
import config from '../config';

const Header: React.FC = () => {
  const navigate = useNavigate();
  const location = useLocation();

  const getTitle = () => {
    switch (location.pathname) {
      case '/feed':
        return 'Лента';
      case '/map':
        return 'Карта';
      case '/weather':
        return 'Погода';
      case '/alerts':
        return 'Уведомления';
      case '/profile':
        return 'Профиль';
      case '/auth/login':
        return 'Вход';
      case '/auth/register':
        return 'Регистрация';
      default:
        return 'FishTrackPro';
    }
  };

  const showBackButton = () => {
    return !['/feed', '/map', '/weather', '/alerts', '/profile'].includes(location.pathname);
  };

  return (
    <header className="glass header">
      <div className="header-content">
        {showBackButton() && (
          <button
            className="back-button"
            onClick={() => navigate(-1)}
            aria-label="Назад"
          >
            <Icon name="arrow_back" />
          </button>
        )}
        
        <div className="header-title">
          <img src={config.logoUrl} alt="FishTrackPro" className="logo" />
          <h1>{getTitle()}</h1>
        </div>

        <div className="header-actions">
          <button
            className="action-button"
            onClick={() => navigate('/alerts')}
            aria-label="Уведомления"
          >
            <Icon name="notifications" />
          </button>
        </div>
      </div>
    </header>
  );
};

export default Header;

