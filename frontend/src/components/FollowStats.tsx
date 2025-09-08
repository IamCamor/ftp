import React, { useState } from 'react';
import { User } from '../types';
import { getUserFollowers, getUserFollowing } from '../api';
import FollowersModal from './FollowersModal';

interface FollowStatsProps {
  user: User;
  onFollowToggle?: (userId: number) => void;
  isFollowing?: boolean;
  showFollowButton?: boolean;
  className?: string;
}

const FollowStats: React.FC<FollowStatsProps> = ({
  user,
  onFollowToggle,
  isFollowing = false,
  showFollowButton = false,
  className = ''
}) => {
  const [showFollowersModal, setShowFollowersModal] = useState(false);
  const [showFollowingModal, setShowFollowingModal] = useState(false);
  const [modalType, setModalType] = useState<'followers' | 'following'>('followers');

  const handleFollowersClick = () => {
    setModalType('followers');
    setShowFollowersModal(true);
  };

  const handleFollowingClick = () => {
    setModalType('following');
    setShowFollowingModal(true);
  };

  const handleFollowClick = () => {
    if (onFollowToggle) {
      onFollowToggle(user.id);
    }
  };

  const formatCount = (count: number): string => {
    if (count >= 1000000) {
      return `${(count / 1000000).toFixed(1)}M`;
    } else if (count >= 1000) {
      return `${(count / 1000).toFixed(1)}K`;
    }
    return count.toString();
  };

  return (
    <div className={`follow-stats ${className}`}>
      <div className="stats-row">
        <div className="stat-item" onClick={handleFollowersClick}>
          <span className="stat-number">{formatCount(user.followers_count || 0)}</span>
          <span className="stat-label">подписчиков</span>
        </div>
        
        <div className="stat-item" onClick={handleFollowingClick}>
          <span className="stat-number">{formatCount(user.following_count || 0)}</span>
          <span className="stat-label">подписок</span>
        </div>
        
        <div className="stat-item">
          <span className="stat-number">{formatCount(user.total_likes_received || 0)}</span>
          <span className="stat-label">лайков</span>
        </div>
      </div>

      {showFollowButton && (
        <button 
          className={`follow-button ${isFollowing ? 'following' : 'not-following'}`}
          onClick={handleFollowClick}
        >
          {isFollowing ? 'Отписаться' : 'Подписаться'}
        </button>
      )}

      {showFollowersModal && (
        <FollowersModal
          user={user}
          type={modalType}
          onClose={() => setShowFollowersModal(false)}
        />
      )}

      {showFollowingModal && (
        <FollowersModal
          user={user}
          type={modalType}
          onClose={() => setShowFollowingModal(false)}
        />
      )}
    </div>
  );
};

export default FollowStats;
