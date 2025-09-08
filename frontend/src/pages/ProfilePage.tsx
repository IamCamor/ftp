import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import Avatar from '../components/Avatar';
import Icon from '../components/Icon';
import { profileMe, logout, getBonuses } from '../api';
import type { User, Bonus } from '../types';
import config from '../config';

const ProfilePage: React.FC = () => {
  const navigate = useNavigate();
  const [user, setUser] = useState<User | null>(null);
  const [bonuses, setBonuses] = useState<Bonus[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    loadProfile();
  }, []);

  const loadProfile = async () => {
    try {
      setLoading(true);
      const [userData, bonusesData] = await Promise.all([
        profileMe(),
        getBonuses()
      ]);
      setUser(userData);
      setBonuses(bonusesData);
    } catch (err) {
      setError('Не удалось загрузить профиль');
      console.error('Profile loading error:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleLogout = async () => {
    try {
      await logout();
      localStorage.removeItem('token');
      navigate(config.routes.auth.login);
    } catch (err) {
      console.error('Logout error:', err);
      // Force logout even if API call fails
      localStorage.removeItem('token');
      navigate(config.routes.auth.login);
    }
  };

  const getActionLabel = (action: string) => {
    switch (action) {
      case 'add_catch':
        return 'Добавление улова';
      case 'add_point':
        return 'Добавление точки';
      case 'like_received':
        return 'Получен лайк';
      case 'comment_received':
        return 'Получен комментарий';
      case 'daily_login':
        return 'Ежедневный вход';
      default:
        return action;
    }
  };

  if (loading) {
    return (
      <div className="screen">
        <div className="loading">Загрузка профиля...</div>
      </div>
    );
  }

  if (error || !user) {
    return (
      <div className="screen">
        <div className="error">
          <p>{error || 'Профиль не найден'}</p>
          <button onClick={loadProfile} className="btn btn-primary">
            Попробовать снова
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="screen">
      <div className="profile-header glass">
        <div className="profile-avatar">
          <Avatar src={user.photo_url} size={80} />
        </div>
        
        <div className="profile-info">
          <h2>{user.name}</h2>
          {user.username && <p>@{user.username}</p>}
          {user.email && <p>{user.email}</p>}
          
          <div className="profile-stats">
            <div className="stat-item">
              <Icon name="stars" size={20} />
              <span>{user.total_bonuses || 0} бонусов</span>
            </div>
            {user.average_rating && (
              <div className="stat-item">
                <Icon name="star" size={20} />
                <span>Рейтинг: {user.average_rating}</span>
              </div>
            )}
          </div>
        </div>
      </div>

      <div className="profile-actions">
        <button className="action-button">
          <Icon name="edit" size={20} />
          <span>Редактировать профиль</span>
        </button>
        
        <button className="action-button">
          <Icon name="settings" size={20} />
          <span>Настройки</span>
        </button>
        
        <button className="action-button" onClick={handleLogout}>
          <Icon name="logout" size={20} />
          <span>Выйти</span>
        </button>
      </div>

      <div className="bonuses-section">
        <h3>История бонусов</h3>
        
        {bonuses.length === 0 ? (
          <div className="empty-state">
            <Icon name="stars" size={48} />
            <p>Пока нет бонусов</p>
          </div>
        ) : (
          <div className="bonuses-list">
            {bonuses.slice(0, 10).map((bonus) => (
              <div key={bonus.id} className="bonus-item">
                <div className="bonus-icon">
                  <Icon name="add_circle" size={20} />
                </div>
                
                <div className="bonus-content">
                  <h4>{getActionLabel(bonus.action)}</h4>
                  <p>{new Date(bonus.created_at).toLocaleDateString()}</p>
                </div>
                
                <div className="bonus-amount">
                  +{bonus.amount}
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default ProfilePage;

