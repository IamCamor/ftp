import React, { useState } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import Icon from './Icon';
import config from '../config';

const BottomNav: React.FC = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const [showAddMenu, setShowAddMenu] = useState(false);

  const navItems = [
    { path: '/catch-feed', icon: 'home', label: 'Уловы' },
    { path: config.routes.feed, icon: 'timeline', label: 'Лента' },
    { path: config.routes.map, icon: 'map', label: 'Карта' },
    { path: config.routes.weather, icon: 'wb_sunny', label: 'Погода' },
    { path: '/groups', icon: 'group', label: 'Группы' },
  ];

  const isActive = (path: string) => {
    return location.pathname === path;
  };

  const handleAddClick = () => {
    setShowAddMenu(!showAddMenu);
  };

  const handleAddCatch = () => {
    setShowAddMenu(false);
    navigate('/add-catch');
  };

  const handleAddPoint = () => {
    setShowAddMenu(false);
    navigate('/add-point');
  };

  const handleStartTrack = () => {
    setShowAddMenu(false);
    navigate('/tracks');
  };

  const handleNavClick = (path: string) => {
    setShowAddMenu(false);
    navigate(path);
  };

  return (
    <>
      <nav className="bottom-nav glass">
        {navItems.map((item) => (
          <button
            key={item.path}
            className={`nav-item ${isActive(item.path) ? 'active' : ''}`}
            onClick={() => handleNavClick(item.path)}
            aria-label={item.label}
            data-testid={`nav-${item.path.split('/').pop() || 'home'}`}
          >
            <Icon name={item.icon} filled={isActive(item.path)} size="md" />
            <span className="nav-label">{item.label}</span>
          </button>
        ))}
        
        <button
          className={`nav-item ${showAddMenu ? 'active' : ''}`}
          onClick={handleAddClick}
          aria-label="Добавить"
          data-testid="add-button"
        >
          <Icon name="add" filled={showAddMenu} size="md" />
          <span className="nav-label">Добавить</span>
        </button>
      </nav>

      {showAddMenu && (
        <div className="add-menu-overlay" onClick={() => setShowAddMenu(false)}>
          <div className="add-menu" data-testid="add-menu">
            <button
              className="add-menu-item"
              onClick={handleAddCatch}
              aria-label="Добавить улов"
              data-testid="add-catch"
            >
              <Icon name="pets" size="lg" />
              <span>Улов</span>
            </button>
            <button
              className="add-menu-item"
              onClick={handleAddPoint}
              aria-label="Добавить место"
              data-testid="add-point"
            >
              <Icon name="add_location" size="lg" />
              <span>Место</span>
            </button>
            <button
              className="add-menu-item"
              onClick={handleStartTrack}
              aria-label="Начать трек"
              data-testid="start-track"
            >
              <Icon name="timeline" size="lg" />
              <span>Трек</span>
            </button>
          </div>
        </div>
      )}
    </>
  );
};

export default BottomNav;
