import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import AvatarUpload from '../components/AvatarUpload';
import Icon from '../components/Icon';
import FollowStats from '../components/FollowStats';
import FollowersModal from '../components/FollowersModal';
import ScreenLayout from '../components/ScreenLayout';
import { profileMe, logout, getBonuses, getUserFollowers, getUserFollowing, updateProfile, getUserInviteCode, getUserPromoCode, isAuthed, feed } from '../api';
import { checkTokenValidity } from '../utils/auth';
import type { User, Bonus, FollowersResponse, InviteCode, PromoCode, CatchRecord } from '../types';
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
  const [isEditing, setIsEditing] = useState(false);
  const [inviteCode, setInviteCode] = useState<InviteCode | null>(null);
  const [promoCode, setPromoCode] = useState<PromoCode | null>(null);
  const [loadingInvite, setLoadingInvite] = useState(false);
  const [loadingPromo, setLoadingPromo] = useState(false);
  const [userCatches, setUserCatches] = useState<CatchRecord[]>([]);
  const [loadingCatches, setLoadingCatches] = useState(false);
  const [editForm, setEditForm] = useState({
    name: '',
    username: '',
    bio: '',
    location: '',
    website: '',
    guide_info: '',
    guide_website: '',
    guide_social_links: {
      instagram: '',
      vk: '',
      telegram: '',
      youtube: ''
    }
  });

  useEffect(() => {
    loadProfile();
    loadInviteCode();
    loadPromoCode();
  }, []);

  useEffect(() => {
    if (user) {
      loadUserCatches();
    }
  }, [user]);

  const loadProfile = async () => {
    try {
      setLoading(true);
      
      // Check if user is authenticated and token is valid
      const token = localStorage.getItem('token');
      console.log('ProfilePage: Token check:', token ? 'Token exists' : 'No token');
      if (!token) {
        console.log('User not authenticated, skipping profile data load');
        setLoading(false);
        return;
      }

      // Check token validity
      const isValidToken = await checkTokenValidity();
      if (!isValidToken) {
        console.log('Token is invalid, redirecting to login');
        navigate('/auth/login');
        return;
      }

      const [userData, bonusesData] = await Promise.all([
        profileMe(),
        getBonuses()
      ]);
      setUser(userData);
      setBonuses(bonusesData);
      
      // Заполняем форму редактирования
      setEditForm({
        name: userData.name || '',
        username: userData.username || '',
        bio: userData.bio || '',
        location: userData.location || '',
        website: userData.website || '',
        guide_info: userData.guide_info || '',
        guide_website: userData.guide_website || '',
        guide_social_links: {
          instagram: userData.guide_social_links?.instagram || '',
          vk: userData.guide_social_links?.vk || '',
          telegram: userData.guide_social_links?.telegram || '',
          youtube: userData.guide_social_links?.youtube || ''
        }
      });
    } catch (err) {
      setError('Не удалось загрузить профиль');
      console.error('Profile loading error:', err);
    } finally {
      setLoading(false);
    }
  };

  const loadInviteCode = async () => {
    if (!isAuthed()) {
      console.log('User not authenticated, skipping invite code load');
      return;
    }
    
    try {
      setLoadingInvite(true);
      const response = await getUserInviteCode();
      setInviteCode(response.data);
    } catch (error) {
      console.error('Error loading invite code:', error);
    } finally {
      setLoadingInvite(false);
    }
  };

  const loadPromoCode = async () => {
    if (!isAuthed()) {
      console.log('User not authenticated, skipping promo code load');
      return;
    }
    
    try {
      setLoadingPromo(true);
      const response = await getUserPromoCode();
      setPromoCode(response.data);
    } catch (error) {
      console.error('Error loading promo code:', error);
      // Don't show error to user if not authenticated
      if (error instanceof Error && error.message.includes('Authentication required')) {
        console.log('User not authenticated for promo code');
      }
    } finally {
      setLoadingPromo(false);
    }
  };

  const loadUserCatches = async () => {
    if (!isAuthed()) {
      console.log('User not authenticated, skipping catches load');
      return;
    }
    
    try {
      setLoadingCatches(true);
      const response = await feed(20, 0);
      console.log('Feed response:', response);
      
      // Обработка разных форматов ответа API
      let feedData: any[] = [];
      
      if (Array.isArray(response)) {
        // Если ответ - массив
        feedData = response;
      } else if (response && response.data) {
        if (Array.isArray(response.data)) {
          // Если response.data - массив
          feedData = response.data;
        } else if (response.data.data && Array.isArray(response.data.data)) {
          // Если response.data.data - массив
          feedData = response.data.data;
        }
      }
      
      console.log('Processed feed data:', feedData);
      
      // Извлекаем уловы из треков текущего пользователя
      const userCatches: any[] = [];
      
      feedData.forEach(trackItem => {
        if (trackItem && trackItem.type === 'track' && trackItem.user?.id === user?.id && trackItem.catches) {
          trackItem.catches.forEach((catchItem: any) => {
            userCatches.push({
              ...catchItem,
              track_title: trackItem.title,
              track_id: trackItem.id
            });
          });
        }
      });
      
      console.log('User catches:', userCatches);
      setUserCatches(userCatches);
    } catch (error) {
      console.error('Error loading user catches:', error);
    } finally {
      setLoadingCatches(false);
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

  const copyToClipboard = async (text: string, type: string) => {
    try {
      await navigator.clipboard.writeText(text);
      alert(`${type} скопирован в буфер обмена!`);
    } catch (error) {
      console.error('Error copying to clipboard:', error);
      // Fallback for older browsers
      const textArea = document.createElement('textarea');
      textArea.value = text;
      document.body.appendChild(textArea);
      textArea.select();
      document.execCommand('copy');
      document.body.removeChild(textArea);
      alert(`${type} скопирован в буфер обмена!`);
    }
  };

  const copyInviteLink = () => {
    if (inviteCode) {
      const inviteLink = `${window.location.origin}/register?invite=${inviteCode.code}`;
      copyToClipboard(inviteLink, 'Ссылка для приглашения');
    }
  };

  const copyInviteCode = () => {
    if (inviteCode) {
      copyToClipboard(inviteCode.code, 'Инвайт-код');
    }
  };

  const copyPromoCode = () => {
    if (promoCode) {
      copyToClipboard(promoCode.code, 'Промокод');
    }
  };

  const handleEditToggle = () => {
    setIsEditing(!isEditing);
    if (!isEditing) {
      // Заполняем форму текущими данными при включении редактирования
      setEditForm({
        name: user?.name || '',
        username: user?.username || '',
        bio: user?.bio || '',
        location: user?.location || '',
        website: user?.website || '',
        guide_info: user?.guide_info || '',
        guide_website: user?.guide_website || '',
        guide_social_links: {
          instagram: user?.guide_social_links?.instagram || '',
          vk: user?.guide_social_links?.vk || '',
          telegram: user?.guide_social_links?.telegram || '',
          youtube: user?.guide_social_links?.youtube || ''
        }
      });
    }
  };

  const handleSaveProfile = async () => {
    if (!user) return;
    
    try {
      setLoading(true);
      const updatedUser = await updateProfile(editForm);
      setUser(updatedUser);
      setIsEditing(false);
      console.log('Profile updated successfully:', updatedUser);
    } catch (err) {
      console.error('Profile update error:', err);
      setError('Ошибка при обновлении профиля');
    } finally {
      setLoading(false);
    }
  };

  const handleCancelEdit = () => {
    setIsEditing(false);
    // Восстанавливаем исходные данные
    setEditForm({
      name: user?.name || '',
      username: user?.username || '',
      bio: user?.bio || '',
      location: user?.location || '',
      website: user?.website || '',
      guide_info: user?.guide_info || '',
      guide_website: user?.guide_website || '',
      guide_social_links: {
        instagram: user?.guide_social_links?.instagram || '',
        vk: user?.guide_social_links?.vk || '',
        telegram: user?.guide_social_links?.telegram || '',
        youtube: user?.guide_social_links?.youtube || ''
      }
    });
  };

  const handleAvatarUpdate = (photoUrl: string | null) => {
    if (user) {
      setUser({
        ...user,
        photo_url: photoUrl || undefined
      });
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

  return (
    <ScreenLayout
      title={user?.name ? `Профиль ${user.name}` : 'Мой профиль'}
      description={user?.bio || 'Профиль пользователя FishTrackPro'}
      keywords={['профиль', 'пользователь', 'рыбак', 'статистика']}
      loading={loading}
      error={error || (!user ? 'Профиль не найден' : null)}
      onRetry={loadProfile}
    >
      {user && (
        <>
        <div className="profile-header glass">
          <div className="profile-avatar">
            <AvatarUpload 
              user={user} 
              onAvatarUpdate={handleAvatarUpdate}
              size="xl"
            />
          </div>
            
            <div className="profile-info">
              <div className="profile-header">
                <div className="profile-title">
                  <h2>{user.name}</h2>
                  {user.username && <p>@{user.username}</p>}
                  {user.email && <p>{user.email}</p>}
                </div>
            <button 
              className="edit-profile-btn"
              onClick={handleEditToggle}
              title={isEditing ? 'Отменить редактирование' : 'Редактировать профиль'}
            >
              <Icon name={isEditing ? 'close' : 'edit'} size="md" />
            </button>
          </div>
          
          <FollowStats
            followersCount={user.followers_count || 0}
            followingCount={user.following_count || 0}
            likesCount={user.total_likes_received || 0}
            onFollowersClick={handleShowFollowers}
            onFollowingClick={handleShowFollowing}
            onLikesClick={() => {}} // TODO: Implement likes modal
          />
          
          {/* Форма редактирования профиля */}
          {isEditing && (
            <div className="edit-profile-form">
              <h3>Редактировать профиль</h3>
              <div className="form-group">
                <label>Имя</label>
                <input
                  type="text"
                  value={editForm.name}
                  onChange={(e) => setEditForm(prev => ({ ...prev, name: e.target.value }))}
                  placeholder="Введите ваше имя"
                />
              </div>
              
              <div className="form-group">
                <label>Имя пользователя</label>
                <input
                  type="text"
                  value={editForm.username}
                  onChange={(e) => setEditForm(prev => ({ ...prev, username: e.target.value }))}
                  placeholder="Введите имя пользователя"
                />
              </div>
              
              <div className="form-group">
                <label>О себе</label>
                <textarea
                  value={editForm.bio}
                  onChange={(e) => setEditForm(prev => ({ ...prev, bio: e.target.value }))}
                  placeholder="Расскажите о себе"
                  rows={3}
                />
              </div>
              
              <div className="form-group">
                <label>Местоположение</label>
                <input
                  type="text"
                  value={editForm.location}
                  onChange={(e) => setEditForm(prev => ({ ...prev, location: e.target.value }))}
                  placeholder="Где вы находитесь"
                />
              </div>
              
              <div className="form-group">
                <label>Веб-сайт</label>
                <input
                  type="url"
                  value={editForm.website}
                  onChange={(e) => setEditForm(prev => ({ ...prev, website: e.target.value }))}
                  placeholder="https://example.com"
                />
              </div>
              
              {/* Поля для гида */}
              {user.is_guide && (
                <>
                  <div className="guide-section">
                    <h4>Информация о гиде</h4>
                    
                    <div className="form-group">
                      <label>Описание услуг гида</label>
                      <textarea
                        value={editForm.guide_info}
                        onChange={(e) => setEditForm(prev => ({ ...prev, guide_info: e.target.value }))}
                        placeholder="Расскажите о ваших услугах как гида"
                        rows={4}
                      />
                    </div>
                    
                    <div className="form-group">
                      <label>Сайт гида</label>
                      <input
                        type="url"
                        value={editForm.guide_website}
                        onChange={(e) => setEditForm(prev => ({ ...prev, guide_website: e.target.value }))}
                        placeholder="https://your-guide-site.com"
                      />
                    </div>
                    
                    <div className="form-group">
                      <label>Социальные сети</label>
                      <div className="social-inputs">
                        <div className="social-input">
                          <Icon name="instagram" size="md" />
                          <input
                            type="url"
                            value={editForm.guide_social_links.instagram}
                            onChange={(e) => setEditForm(prev => ({ 
                              ...prev, 
                              guide_social_links: { 
                                ...prev.guide_social_links, 
                                instagram: e.target.value 
                              } 
                            }))}
                            placeholder="https://instagram.com/username"
                          />
                        </div>
                        
                        <div className="social-input">
                          <Icon name="vk" size="md" />
                          <input
                            type="url"
                            value={editForm.guide_social_links.vk}
                            onChange={(e) => setEditForm(prev => ({ 
                              ...prev, 
                              guide_social_links: { 
                                ...prev.guide_social_links, 
                                vk: e.target.value 
                              } 
                            }))}
                            placeholder="https://vk.com/username"
                          />
                        </div>
                        
                        <div className="social-input">
                          <Icon name="telegram" size="md" />
                          <input
                            type="url"
                            value={editForm.guide_social_links.telegram}
                            onChange={(e) => setEditForm(prev => ({ 
                              ...prev, 
                              guide_social_links: { 
                                ...prev.guide_social_links, 
                                telegram: e.target.value 
                              } 
                            }))}
                            placeholder="https://t.me/username"
                          />
                        </div>
                        
                        <div className="social-input">
                          <Icon name="youtube" size="md" />
                          <input
                            type="url"
                            value={editForm.guide_social_links.youtube}
                            onChange={(e) => setEditForm(prev => ({ 
                              ...prev, 
                              guide_social_links: { 
                                ...prev.guide_social_links, 
                                youtube: e.target.value 
                              } 
                            }))}
                            placeholder="https://youtube.com/@username"
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                </>
              )}
              
              <div className="form-actions">
                <button 
                  className="btn btn-secondary"
                  onClick={handleCancelEdit}
                >
                  Отмена
                </button>
                <button 
                  className="btn btn-primary"
                  onClick={handleSaveProfile}
                >
                  Сохранить
                </button>
              </div>
            </div>
          )}
          
          <div className="profile-stats">
            <div className="stat-item">
              <Icon name="stars" size="md" />
              <span>{user.total_bonuses || 0} бонусов</span>
            </div>
            {user.average_rating && (
              <div className="stat-item">
                <Icon name="star" size="md" />
                <span>Рейтинг: {user.average_rating}</span>
              </div>
            )}
          </div>
        </div>
      </div>

      <div className="profile-actions">
        <button className="action-button">
          <Icon name="edit" size="md" />
          <span>Редактировать профиль</span>
        </button>
        
        <button className="action-button" onClick={() => navigate('/settings')}>
          <Icon name="settings" size="md" />
          <span>Настройки</span>
        </button>
        
        <button className="action-button" onClick={handleLogout}>
          <Icon name="logout" size="md" />
          <span>Выйти</span>
        </button>
      </div>

      <div className="profile-navigation">
        <button 
          className="nav-button"
          onClick={() => navigate('/reference')}
        >
          <Icon name="menu_book" size="md" />
          <span>Справочники</span>
        </button>
        
        <button 
          className="nav-button"
          onClick={() => navigate('/subscription')}
        >
          <Icon name="star" size="md" />
          <span>PRo</span>
        </button>
        
        <button 
          className="nav-button"
          onClick={() => navigate('/live')}
        >
          <Icon name="videocam" size="md" />
          <span>Live</span>
        </button>
      </div>

      {/* Subscription Section */}
      <div className="subscription-section">
        <h3>Подписка</h3>
        <div className="subscription-actions">
          <button 
            className="action-button primary"
            onClick={() => navigate('/subscription')}
          >
            <Icon name="star" size="md" />
            <span>Выбрать подписку</span>
          </button>
        </div>
      </div>

      {/* Invite Code Section */}
      <div className="invite-section">
        <h3>Пригласить друзей</h3>
        <div className="invite-content">
          {loadingInvite ? (
            <div className="loading-state">
              <Icon name="hourglass_empty" size="md" />
              <span>Загрузка инвайт-кода...</span>
            </div>
          ) : inviteCode ? (
            <div className="invite-code-card">
              <div className="code-info">
                <div className="code-header">
                  <h4>Ваш инвайт-код</h4>
                  <span className="code-value">{inviteCode.code}</span>
                </div>
                <div className="code-details">
                  <div className="detail-item">
                    <span>Скидка для друга:</span>
                    <span className="discount">{inviteCode.discount_percentage}%</span>
                  </div>
                  <div className="detail-item">
                    <span>Ваш бонус:</span>
                    <span className="bonus">+{inviteCode.bonus_amount} бонусов</span>
                  </div>
                  <div className="detail-item">
                    <span>Использований:</span>
                    <span>{inviteCode.used_count}{inviteCode.max_uses ? ` / ${inviteCode.max_uses}` : ' / ∞'}</span>
                  </div>
                </div>
              </div>
              <div className="code-actions">
                <button 
                  className="btn btn-secondary"
                  onClick={copyInviteCode}
                >
                  <Icon name="content_copy" size="sm" />
                  Копировать код
                </button>
                <button 
                  className="btn btn-primary"
                  onClick={copyInviteLink}
                >
                  <Icon name="share" size="sm" />
                  Копировать ссылку
                </button>
              </div>
            </div>
          ) : (
            <div className="error-state">
              <Icon name="error" size="md" />
              <span>Не удалось загрузить инвайт-код</span>
              <button 
                className="btn btn-secondary"
                onClick={loadInviteCode}
              >
                Попробовать снова
              </button>
            </div>
          )}
        </div>
      </div>

      {/* Personal Promo Code Section */}
      <div className="promo-section">
        <h3>Персональный промокод</h3>
        <div className="promo-content">
          {loadingPromo ? (
            <div className="loading-state">
              <Icon name="hourglass_empty" size="md" />
              <span>Загрузка промокода...</span>
            </div>
          ) : promoCode ? (
            <div className="promo-code-card">
              <div className="code-info">
                <div className="code-header">
                  <h4>Ваш промокод</h4>
                  <span className="code-value">{promoCode.code}</span>
                </div>
                <div className="code-details">
                  <div className="detail-item">
                    <span>Тип скидки:</span>
                    <span>{promoCode.type === 'percentage' ? 'Процент' : 'Фиксированная сумма'}</span>
                  </div>
                  <div className="detail-item">
                    <span>Размер скидки:</span>
                    <span className="discount">
                      {promoCode.type === 'percentage' ? `${promoCode.value}%` : `${promoCode.value} ₽`}
                    </span>
                  </div>
                  <div className="detail-item">
                    <span>Использований:</span>
                    <span>{promoCode.used_count}{promoCode.max_uses ? ` / ${promoCode.max_uses}` : ' / ∞'}</span>
                  </div>
                  <div className="detail-item">
                    <span>Статус:</span>
                    <span className={`status ${promoCode.is_active ? 'active' : 'inactive'}`}>
                      {promoCode.is_active ? 'Активен' : 'Неактивен'}
                    </span>
                  </div>
                </div>
              </div>
              <div className="code-actions">
                <button 
                  className="btn btn-primary"
                  onClick={copyPromoCode}
                >
                  <Icon name="content_copy" size="sm" />
                  Копировать промокод
                </button>
              </div>
            </div>
          ) : (
            <div className="error-state">
              <Icon name="error" size="md" />
              <span>Не удалось загрузить промокод</span>
              <button 
                className="btn btn-secondary"
                onClick={loadPromoCode}
              >
                Попробовать снова
              </button>
            </div>
          )}
        </div>
      </div>

      <div className="bonuses-section">
        <h3>История бонусов</h3>
        
        {bonuses.length === 0 ? (
          <div className="empty-state">
            <Icon name="stars" size="xl" />
            <p>Пока нет бонусов</p>
          </div>
        ) : (
          <div className="bonuses-list">
            {bonuses.slice(0, 10).map((bonus) => (
              <div key={bonus.id} className="bonus-item">
                <div className="bonus-icon">
                  <Icon name="add_circle" size="md" />
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

      {/* User Catches Section */}
      <div className="user-catches-section">
        <div className="section-header">
          <h3>Мои уловы</h3>
          <button 
            className="btn btn-secondary"
            onClick={() => navigate('/map')}
          >
            <Icon name="map" size="sm" />
            <span>На карте</span>
          </button>
        </div>
        
        {loadingCatches ? (
          <div className="loading-state">
            <Icon name="hourglass_empty" size="md" />
            <span>Загрузка уловов...</span>
          </div>
        ) : userCatches.length === 0 ? (
          <div className="empty-state">
            <Icon name="fishing" size="xl" />
            <p>Пока нет уловов</p>
            <button 
              className="btn btn-primary"
              onClick={() => navigate('/add-catch')}
            >
              <Icon name="add" size="sm" />
              <span>Добавить улов</span>
            </button>
          </div>
        ) : (
          <div className="catches-grid">
            {userCatches.slice(0, 6).map((catchItem) => (
              <div key={catchItem.id} className="catch-card">
                {catchItem.photo_url && (
                  <div className="catch-image">
                    <img 
                      src={catchItem.photo_url} 
                      alt={catchItem.species || 'Улов'}
                      loading="lazy"
                    />
                  </div>
                )}
                
                <div className="catch-info">
                  <h4>{catchItem.species || 'Неизвестная рыба'}</h4>
                  <div className="catch-stats">
                    {catchItem.weight && (
                      <span className="stat">
                        <Icon name="scale" size="xs" />
                        {catchItem.weight} кг
                      </span>
                    )}
                    {catchItem.length && (
                      <span className="stat">
                        <Icon name="straighten" size="xs" />
                        {catchItem.length} см
                      </span>
                    )}
                  </div>
                  <p className="catch-date">
                    {catchItem.caught_at ? new Date(catchItem.caught_at).toLocaleDateString() : 'Дата неизвестна'}
                  </p>
                </div>
                
                <div className="catch-actions">
                  <button 
                    className="action-btn"
                    onClick={() => navigate(`/catch/${catchItem.id}`)}
                  >
                    <Icon name="visibility" size="sm" />
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
        
        {userCatches.length > 6 && (
          <div className="section-footer">
            <button 
              className="btn btn-secondary"
              onClick={() => navigate('/feed')}
            >
              <span>Показать все уловы</span>
              <Icon name="arrow_forward" size="sm" />
            </button>
          </div>
        )}
      </div>

      {/* Map Section */}
      <div className="map-section">
        <div className="section-header">
          <h3>Места уловов</h3>
          <button 
            className="btn btn-primary"
            onClick={() => navigate('/map')}
          >
            <Icon name="map" size="sm" />
            <span>Открыть карту</span>
          </button>
        </div>
        
        <div className="map-preview">
          <div className="map-placeholder">
            <Icon name="map" size="xl" />
            <p>Посмотрите все ваши места уловов на интерактивной карте</p>
            <button 
              className="btn btn-primary"
              onClick={() => navigate('/map')}
            >
              <Icon name="map" size="sm" />
              <span>Открыть карту</span>
            </button>
          </div>
        </div>
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
        </>
      )}
    </ScreenLayout>
  );
};

export default ProfilePage;

