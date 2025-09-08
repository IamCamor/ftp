import React, { useState, useEffect } from 'react';
import { User } from '../types';
import { toggleFollow } from '../api';
import Avatar from './Avatar';

interface FollowersModalProps {
  title: string;
  users: User[];
  onClose: () => void;
  onLoadMore: () => void;
  hasMore: boolean;
}

const FollowersModal: React.FC<FollowersModalProps> = ({
  title,
  users,
  onClose,
  onLoadMore,
  hasMore
}) => {
  const [searchQuery, setSearchQuery] = useState('');
  const [filteredUsers, setFilteredUsers] = useState<User[]>([]);
  const [loadingMore, setLoadingMore] = useState(false);
  const [following, setFollowing] = useState<Set<number>>(new Set());

  useEffect(() => {
    if (searchQuery.trim()) {
      const filtered = users.filter(user => 
        user.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        user.username?.toLowerCase().includes(searchQuery.toLowerCase())
      );
      setFilteredUsers(filtered);
    } else {
      setFilteredUsers(users);
    }
  }, [searchQuery, users]);

  const handleFollowToggle = async (userId: number) => {
    try {
      const response = await toggleFollow(userId);
      if (response.success) {
        setFollowing(prev => {
          const newSet = new Set(prev);
          if (response.data?.following) {
            newSet.add(userId);
          } else {
            newSet.delete(userId);
          }
          return newSet;
        });
      }
    } catch (error) {
      console.error('Error toggling follow:', error);
    }
  };

  const handleLoadMore = async () => {
    if (loadingMore || !hasMore) return;
    
    setLoadingMore(true);
    try {
      await onLoadMore();
    } finally {
      setLoadingMore(false);
    }
  };

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="followers-modal" onClick={(e) => e.stopPropagation()}>
        <div className="modal-header">
          <h2>{title}</h2>
          <button className="close-button" onClick={onClose}>
            <span className="material-symbols-rounded">close</span>
          </button>
        </div>
        
        <div className="modal-content">
          <div className="search-section">
            <div className="search-input-group">
              <span className="material-symbols-rounded">search</span>
              <input
                type="text"
                className="search-input"
                placeholder="Поиск пользователей..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>
          </div>
          
          <div className="users-list">
            {filteredUsers.map((user) => (
              <div key={user.id} className="user-item">
                <Avatar src={user.photo_url} size={40} />
                <div className="user-info">
                  <div className="user-name">
                    {user.name}
                    {user.is_premium && user.crown_icon_url && (
                      <img src={user.crown_icon_url} alt="Premium" className="crown-icon" />
                    )}
                  </div>
                  {user.username && (
                    <div className="user-username">@{user.username}</div>
                  )}
                  <div className="user-stats">
                    {user.followers_count} подписчиков
                  </div>
                </div>
                <button
                  className={`follow-button ${following.has(user.id) ? 'following' : ''}`}
                  onClick={() => handleFollowToggle(user.id)}
                >
                  {following.has(user.id) ? 'Отписаться' : 'Подписаться'}
                </button>
              </div>
            ))}
            
            {hasMore && (
              <button 
                className="load-more-button" 
                onClick={handleLoadMore}
                disabled={loadingMore}
              >
                {loadingMore ? 'Загрузка...' : 'Загрузить еще'}
              </button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default FollowersModal;