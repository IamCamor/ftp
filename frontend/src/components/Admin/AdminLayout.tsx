import React, { useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import Icon from '../Icon';

interface AdminLayoutProps {
  children: React.ReactNode;
}

const AdminLayout: React.FC<AdminLayoutProps> = ({ children }) => {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const location = useLocation();

  const menuItems = [
    { path: '/admin', icon: 'dashboard', label: 'Панель управления' },
    { path: '/admin/users', icon: 'people', label: 'Пользователи' },
    { path: '/admin/catches', icon: 'fishing', label: 'Уловы' },
    { path: '/admin/points', icon: 'place', label: 'Точки на карте' },
    { path: '/admin/reports', icon: 'report', label: 'Жалобы' },
  ];

  const isActive = (path: string) => {
    if (path === '/admin') {
      return location.pathname === '/admin';
    }
    return location.pathname.startsWith(path);
  };

  return (
    <div className="admin-layout">
      {/* Sidebar */}
      <div className={`admin-sidebar ${sidebarOpen ? 'open' : ''}`}>
        <div className="admin-sidebar-header">
          <h2>FishTrackPro Admin</h2>
          <button 
            className="admin-sidebar-toggle"
            onClick={() => setSidebarOpen(false)}
          >
            <Icon name="close" />
          </button>
        </div>
        
        <nav className="admin-nav">
          {menuItems.map((item) => (
            <Link
              key={item.path}
              to={item.path}
              className={`admin-nav-item ${isActive(item.path) ? 'active' : ''}`}
              onClick={() => setSidebarOpen(false)}
            >
              <Icon name={item.icon} />
              <span>{item.label}</span>
            </Link>
          ))}
        </nav>
      </div>

      {/* Main Content */}
      <div className="admin-main">
        {/* Header */}
        <header className="admin-header">
          <button 
            className="admin-menu-toggle"
            onClick={() => setSidebarOpen(true)}
          >
            <Icon name="menu" />
          </button>
          
          <div className="admin-header-actions">
            <Link to="/" className="admin-back-link">
              <Icon name="arrow_back" />
              Вернуться в приложение
            </Link>
          </div>
        </header>

        {/* Content */}
        <main className="admin-content">
          {children}
        </main>
      </div>

      {/* Overlay */}
      {sidebarOpen && (
        <div 
          className="admin-overlay"
          onClick={() => setSidebarOpen(false)}
        />
      )}
    </div>
  );
};

export default AdminLayout;
