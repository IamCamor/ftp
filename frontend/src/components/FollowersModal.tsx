import React, { useState, useEffect } from 'react';
import { User } from '../types';
import { getUserFollowers, getUserFollowing, toggleFollow } from '../api';
import Icon from './Icon';
import Avatar from './Avatar';

interface FollowersModalProps {
  user: User;
  type: 'followers' | 'following';
  onClose: () => void;
}

const FollowersModal: React.FC<FollowersModalProps> = ({
  user,
  type,
  onClose
}) => {
  const [users, setUsers] = useState<User[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [following, setFollowing] = useState<Set<number>>(new Set());

  useEffect(() => {
    loadUsers();
  }, [type, searchQuery]);

  const loadUsers = async (page: number = 1, append: boolean = false) => {
    setLoading(true);
    try {
      const params = {
        page,
        limit: 20,
        ...(searchQuery && { search: searchQuery })
      };

      const response = type === 'followers' 
        ? await getUserFollowers(user.id, params)
        : await getUserFollowing(user.id, params);

      const newUsers = response.data.data;
      
      if (append) {
        setUsers(prev => [...prev, ...newUsers]);
      } else {
        setUsers(newUsers);
      }

      setCurrentPage(page);
      setHasMore(page < response.data.last_page);
    } catch (error) {
      console.error('Error loading users:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (query: string) => {
    setSearchQuery(query);
    setCurrentPage(1);
    setUsers([]);
  };

  const handleFollowToggle = async (userId: number) => {
    try {
      await toggleFollow(userId);
      
      setFollowing(prev => {
        const newSet = new Set(prev);
        if (newSet.has(userId)) {
          newSet.delete(userId);
        } else {
          newSet.add(userId);
        }
        return newSet;
      });
    } catch (error) {
      console.error('Error toggling follow:', error);
    }
  };

  const loadMore = () => {
    if (!loading && hasMore) {
      loadUsers(currentPage + 1, true);
    }
  };

  const getTitle = () => {
    return type === 'followers' ? 'Подписчики' : 'Подписки';
  };

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="followers-modal" onClick={(e) => e.stopPropagation()}>
        <div className="modal-header">
          <h2>{getTitle()}</h2>
          <button className="close-button" onClick={onClose}>
            <Icon name="close" />
          </button>
        </div>

        <div className="modal-content">
          <div className="search-section">
            <div className="search-input-group">
              <Icon name="search" />
              <input
                type="text"
                placeholder="Поиск..."
                value={searchQuery}
                onChange={(e) => handleSearch(e.target.value)}
                className="search-input"
              />
            </div>
          </div>

          <div className="users-list">
            {loading && users.length === 0 ? (
              <div className="loading">Загрузка...</div>
            ) : users.length > 0 ? (
              <>
                {users.map((userItem) => (
                  <div key={userItem.id} className="user-item">
                    <Avatar
                      src={userItem.photo_url}
                      name={userItem.name}
                      size="medium"
                      crownIconUrl={userItem.crown_icon_url}
                      isPremium={userItem.is_premium}
                    />
                    
                    <div className="user-info">
                      <div className="user-name">
                        {userItem.name}
                        {userItem.is_online && (
                          <span className="online-indicator" title="В сети"></span>
                        )}
                      </div>
                      {userItem.username && (
                        <div className="user-username">@{userItem.username}</div>
                      )}
                      <div className="user-stats">
                        {userItem.followers_count} подписчиков
                      </div>
                    </div>

                    <button
                      className={`follow-button ${following.has(userItem.id) ? 'following' : 'not-following'}`}
                      onClick={() => handleFollowToggle(userItem.id)}
                    >
                      {following.has(userItem.id) ? 'Отписаться' : 'Подписаться'}
                    </button>
                  </div>
                ))}

                {hasMore && (
                  <button 
                    className="load-more-button"
                    onClick={loadMore}
                    disabled={loading}
                  >
                    {loading ? 'Загрузка...' : 'Загрузить еще'}
                  </button>
                )}
              </>
            ) : (
              <div className="empty-state">
                <Icon name="people" />
                <h3>Никого не найдено</h3>
                <p>Попробуйте изменить поисковый запрос</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default FollowersModal;
