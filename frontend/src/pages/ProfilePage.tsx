import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import Avatar from '../components/Avatar';
import Icon from '../components/Icon';
import FollowStats from '../components/FollowStats';
import FollowersModal from '../components/FollowersModal';
import { profileMe, logout, getBonuses, getUserFollowers, getUserFollowing } from '../api';
import type { User, Bonus, FollowersResponse } from '../types';
import config from '../config';

const ProfilePage: React.FC = () => {
  const navigate = useNavigate();
  const [user, setUser] = useState<User | null>(null);
  const [bonuses, setBonuses] = useState<Bonus[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showFollowersModal, setShowFollowersModal] = useState(false);
  const [showFollowingModal, setShowFollowingModal] = useState(false);
  const [followers, setFollowers] = useState<FollowersResponse | null>(null);
  const [following, setFollowing] = useState<FollowersResponse | null>(null);

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

  const handleShowFollowers = async () => {
    if (!user) return;
    
    try {
      const data = await getUserFollowers(user.id);
      setFollowers(data);
      setShowFollowersModal(true);
    } catch (err) {
      console.error('Error loading followers:', err);
    }
  };

  const handleShowFollowing = async () => {
    if (!user) return;
    
    try {
      const data = await getUserFollowing(user.id);
      setFollowing(data);
      setShowFollowingModal(true);
    } catch (err) {
      console.error('Error loading following:', err);
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
          
          <FollowStats
            followersCount={user.followers_count || 0}
            followingCount={user.following_count || 0}
            likesCount={user.total_likes_received || 0}
            onFollowersClick={handleShowFollowers}
            onFollowingClick={handleShowFollowing}
            onLikesClick={() => {}} // TODO: Implement likes modal
          />
          
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

      {/* Followers Modal */}
      {showFollowersModal && followers && (
        <FollowersModal
          title="Подписчики"
          users={followers.data}
          onClose={() => setShowFollowersModal(false)}
          onLoadMore={() => {}} // TODO: Implement pagination
          hasMore={followers.current_page < followers.last_page}
        />
      )}

      {/* Following Modal */}
      {showFollowingModal && following && (
        <FollowersModal
          title="Подписки"
          users={following.data}
          onClose={() => setShowFollowingModal(false)}
          onLoadMore={() => {}} // TODO: Implement pagination
          hasMore={following.current_page < following.last_page}
        />
      )}
    </div>
  );
};

export default ProfilePage;

