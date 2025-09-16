import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import Avatar from '../components/Avatar';
import Icon from '../components/Icon';
import UserStats from '../components/UserStats';
import FollowStats from '../components/FollowStats';
import { ReportModal } from '../components/ReportModal';
import { getUser, followUser, unfollowUser, checkFollowing, isAuthed } from '../api';
import type { User } from '../types';

const UserProfilePage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [following, setFollowing] = useState<boolean>(false);
  const [followLoading, setFollowLoading] = useState(false);
  const [followError, setFollowError] = useState<string | null>(null);
  const [showReportModal, setShowReportModal] = useState(false);

  useEffect(() => {
    if (id) {
      loadUserProfile();
    }
  }, [id]);

  const loadUserProfile = async () => {
    try {
      setLoading(true);
      const [userData, followingData] = await Promise.all([
        getUser(Number(id)),
        isAuthed() ? checkFollowing(Number(id)) : Promise.resolve({ following: false })
      ]);
      
      setUser(userData);
      setFollowing(followingData.following);
    } catch (err) {
      setError('Не удалось загрузить профиль пользователя');
      console.error('User profile loading error:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleFollowToggle = async () => {
    if (!user || !isAuthed()) {
      navigate('/auth/login');
      return;
    }

    // Проверяем настройки приватности пользователя
    if (!user.privacy_settings?.allow_friend_requests) {
      setFollowError('Этот пользователь запретил добавлять его в друзья');
      return;
    }

    try {
      setFollowLoading(true);
      setFollowError(null);
      
      const result = following 
        ? await unfollowUser(user.id)
        : await followUser(user.id);
      
      setFollowing(result.data.following);
      
      // Обновляем счетчик подписчиков
      setUser(prev => prev ? {
        ...prev,
        followers_count: result.data.followers_count
      } : null);
      
      // Показываем уведомление об успехе
      const action = following ? 'удален из друзей' : 'добавлен в друзья';
      console.log(`Пользователь ${action} успешно`);
      
    } catch (err: any) {
      console.error('Follow toggle error:', err);
      setFollowError(err.message || 'Не удалось выполнить действие. Попробуйте еще раз.');
    } finally {
      setFollowLoading(false);
    }
  };

  const handleViewCatches = () => {
    navigate(`/users/${user?.id}/catches`);
  };

  const handleReport = () => {
    setShowReportModal(true);
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
          <p>{error || 'Пользователь не найден'}</p>
          <button onClick={() => navigate(-1)} className="btn btn-primary">
            Назад
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="screen">
      <div className="user-profile">
        {/* Header */}
        <div className="profile-header glass">
          <div className="profile-avatar">
            <Avatar 
              src={user.photo_url} 
              size="xl"
              crownIconUrl={user.crown_icon_url}
              isPremium={user.is_premium}
              isGuide={user.is_guide}
              guideIconUrl={user.guide_icon_url || undefined}
              name={user.name}
            />
          </div>
          
          <div className="profile-info">
            <div className="profile-title">
              <h2>{user.name}</h2>
              {user.username && <p>@{user.username}</p>}
              {user.bio && <p className="user-bio">{user.bio}</p>}
              {user.location && (
                <div className="user-location">
                  <Icon name="location_on" size="sm" />
                  <span>{user.location}</span>
                </div>
              )}
            </div>
            
            <div className="profile-actions">
              {isAuthed() && (
                <div className="follow-section">
                  <div className="action-buttons">
                    <button
                      className={`btn ${following ? 'btn-secondary' : 'btn-primary'}`}
                      onClick={handleFollowToggle}
                      disabled={followLoading}
                    >
                      <Icon name={following ? 'person_remove' : 'person_add'} size="sm" />
                      {followLoading ? '...' : (following ? 'Удалить из друзей' : 'Добавить в друзья')}
                    </button>
                    
                    <button
                      className="report-button"
                      onClick={handleReport}
                      title="Пожаловаться на пользователя"
                    >
                      <Icon name="report" size="md" />
                    </button>
                  </div>
                  
                  {followError && (
                    <div className="follow-error">
                      <Icon name="error" size="xs" />
                      <span>{followError}</span>
                    </div>
                  )}
                </div>
              )}
              
              <button
                className="btn btn-outline"
                onClick={handleViewCatches}
              >
                <Icon name="fishing" size="sm" />
                Уловы
              </button>
            </div>
          </div>
        </div>

        {/* Follow Stats */}
        <FollowStats
          followersCount={user.followers_count || 0}
          followingCount={user.following_count || 0}
          likesCount={user.total_likes_received || 0}
          onFollowersClick={() => navigate(`/users/${user.id}/followers`)}
          onFollowingClick={() => navigate(`/users/${user.id}/following`)}
          onLikesClick={() => navigate(`/users/${user.id}/likes`)}
        />

        {/* User Statistics */}
        <UserStats userId={user.id} />

        {/* Guide Information */}
        {user.is_guide && (
          <div className="guide-info glass">
            <h3>Информация о гиде</h3>
            <div className="guide-details">
              {user.guide_info && (
                <div className="guide-description">
                  <p>{user.guide_info}</p>
                </div>
              )}
              
              <div className="guide-links">
                {user.guide_website && (
                  <div className="guide-link">
                    <Icon name="language" size="md" />
                    <a href={user.guide_website} target="_blank" rel="noopener noreferrer">
                      {user.guide_website}
                    </a>
                  </div>
                )}
                
                {user.guide_social_links && (
                  <div className="social-links">
                    {user.guide_social_links.instagram && (
                      <a href={user.guide_social_links.instagram} target="_blank" rel="noopener noreferrer" className="social-link">
                        <Icon name="instagram" size="md" />
                        Instagram
                      </a>
                    )}
                    {user.guide_social_links.vk && (
                      <a href={user.guide_social_links.vk} target="_blank" rel="noopener noreferrer" className="social-link">
                        <Icon name="vk" size="md" />
                        VK
                      </a>
                    )}
                    {user.guide_social_links.telegram && (
                      <a href={user.guide_social_links.telegram} target="_blank" rel="noopener noreferrer" className="social-link">
                        <Icon name="telegram" size="md" />
                        Telegram
                      </a>
                    )}
                    {user.guide_social_links.youtube && (
                      <a href={user.guide_social_links.youtube} target="_blank" rel="noopener noreferrer" className="social-link">
                        <Icon name="youtube" size="md" />
                        YouTube
                      </a>
                    )}
                  </div>
                )}
              </div>
              
              {user.guide_rating && (
                <div className="guide-rating">
                  <Icon name="star" size="md" />
                  <span>Рейтинг гида: {user.guide_rating}</span>
                  {user.guide_reviews_count && (
                    <span className="reviews-count">({user.guide_reviews_count} отзывов)</span>
                  )}
                </div>
              )}
            </div>
          </div>
        )}

        {/* Additional Info */}
        <div className="profile-details glass">
          <h3>Информация</h3>
          <div className="details-grid">
            {user.website && (
              <div className="detail-item">
                <Icon name="language" size="md" />
                <a href={user.website} target="_blank" rel="noopener noreferrer">
                  {user.website}
                </a>
              </div>
            )}
            
            <div className="detail-item">
              <Icon name="calendar_today" size="md" />
              <span>На сайте с {new Date(user.created_at).toLocaleDateString()}</span>
            </div>
            
            {user.average_rating && (
              <div className="detail-item">
                <Icon name="star" size="md" />
                <span>Рейтинг: {user.average_rating}</span>
              </div>
            )}
          </div>
        </div>

        {/* Report Modal */}
        <ReportModal
          isOpen={showReportModal}
          onClose={() => setShowReportModal(false)}
          type="user"
          targetId={user.id}
          targetName={`Пользователь: ${user.name}`}
        />
      </div>
    </div>
  );
};

export default UserProfilePage;
