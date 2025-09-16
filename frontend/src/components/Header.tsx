import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import Icon from './Icon';
import Avatar from './Avatar';
import NotificationBadge from './NotificationBadge';
import { useNotifications } from '../hooks/useNotifications';
import { isAuthed, profileMe } from '../api';
import type { User } from '../types';
import config from '../config';

const Header: React.FC = () => {
  const navigate = useNavigate();
  const [showSearchModal, setShowSearchModal] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [user, setUser] = useState<User | null>(null);
  const { unreadCount } = useNotifications();

  useEffect(() => {
    if (isAuthed()) {
      loadUser();
    }
  }, []);

  const loadUser = async () => {
    try {
      const userData = await profileMe();
      setUser(userData);
    } catch (error) {
      console.error('Failed to load user:', error);
    }
  };

  const handleSearch = () => {
    if (searchQuery.trim()) {
      navigate(`/search?q=${encodeURIComponent(searchQuery.trim())}`);
      setShowSearchModal(false);
      setSearchQuery('');
    }
  };

  const handleKeyPress = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter') {
      handleSearch();
    }
  };


  return (
    <header className="glass header">
      <div className="header-content">
        <div className="header-title">
          <img src={config.logoUrl} alt="FishTrackPro" className="logo" />
        </div>

        <div className="header-actions">
          <button
            className="action-button"
            onClick={() => setShowSearchModal(true)}
            aria-label="Поиск по рыбакам, уловам"
            title="Поиск по рыбакам, уловам"
          >
            <Icon name="search" size="md" />
          </button>
          <button
            className="action-button"
            onClick={() => navigate('/events')}
            aria-label="События"
            title="События"
          >
            <Icon name="event" size="md" />
          </button>
          <button
            className="action-button notification-button"
            onClick={() => navigate('/alerts')}
            aria-label="Уведомления"
          >
            <Icon name="notifications" size="md" />
            <NotificationBadge count={unreadCount} />
          </button>
          {isAuthed() && user ? (
            <button
              className="profile-avatar-button"
              onClick={() => navigate('/profile')}
              aria-label="Профиль"
              title="Профиль"
            >
              <Avatar 
                src={user.photo_url} 
                size="lg"
                crownIconUrl={user.crown_icon_url}
                isPremium={user.is_premium}
                name={user.name}
              />
            </button>
          ) : (
            <button
              className="action-button"
              onClick={() => navigate('/profile')}
              aria-label="Профиль"
              title="Профиль"
            >
              <Icon name="person" size="md" />
            </button>
          )}
        </div>
      </div>

      {/* Search Modal */}
      {showSearchModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" onClick={() => setShowSearchModal(false)}>
          <div className="glass-card p-6 mx-4 max-w-lg w-full" onClick={(e) => e.stopPropagation()}>
            <div className="flex items-center justify-between mb-6">
              <h3 className="md3-title-large">Поиск по справочникам</h3>
              <button
                className="md3-button md3-button-text p-2"
                onClick={() => setShowSearchModal(false)}
                aria-label="Закрыть"
              >
                <Icon name="close" size={20} />
              </button>
            </div>
            <div className="space-y-4">
              <div className="flex gap-3">
                <input
                  type="text"
                  placeholder="Поиск по рыбакам, уловам, описанию..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  onKeyPress={handleKeyPress}
                  className="flex-1 px-4 py-3 border border-gray-300 rounded-2xl bg-white/50 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  autoFocus
                />
                <button 
                  onClick={handleSearch}
                  className="md3-button md3-button-filled px-6"
                >
                  <Icon name="search" size={20} />
                </button>
              </div>
              <div className="p-4 bg-gray-50 rounded-2xl">
                <p className="md3-body-medium text-gray-600">
                  Например: "щука", "спиннинг", "узлы", "лодка"
                </p>
              </div>
            </div>
          </div>
        </div>
      )}
    </header>
  );
};

export default Header;

