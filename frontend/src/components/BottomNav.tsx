import React from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import Icon from './Icon';
import config from '../config';

const BottomNav: React.FC = () => {
  const navigate = useNavigate();
  const location = useLocation();

  const navItems = [
    { path: config.routes.feed, icon: 'home', label: 'Лента' },
    { path: config.routes.map, icon: 'map', label: 'Карта' },
    { path: '/groups', icon: 'group', label: 'Группы' },
    { path: '/events', icon: 'event', label: 'События' },
    { path: '/live', icon: 'videocam', label: 'Live' },
    { path: '/subscription', icon: 'star', label: 'Pro' },
    { path: config.routes.profile, icon: 'person', label: 'Профиль' },
  ];

  const isActive = (path: string) => {
    return location.pathname === path;
  };

  return (
    <nav className="bottom-nav glass">
      {navItems.map((item) => (
        <button
          key={item.path}
          className={`nav-item ${isActive(item.path) ? 'active' : ''}`}
          onClick={() => navigate(item.path)}
          aria-label={item.label}
        >
          <Icon name={item.icon} filled={isActive(item.path)} />
          <span className="nav-label">{item.label}</span>
        </button>
      ))}
    </nav>
  );
};

export default BottomNav;
