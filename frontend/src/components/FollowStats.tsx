import React from 'react';

interface FollowStatsProps {
  followersCount: number;
  followingCount: number;
  likesCount: number;
  onFollowersClick: () => void;
  onFollowingClick: () => void;
  onLikesClick: () => void;
  className?: string;
}

const FollowStats: React.FC<FollowStatsProps> = ({
  followersCount,
  followingCount,
  likesCount,
  onFollowersClick,
  onFollowingClick,
  onLikesClick,
  className = ''
}) => {
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
        <div className="stat-item" onClick={onFollowersClick}>
          <span className="stat-number">{formatCount(followersCount)}</span>
          <span className="stat-label">подписчиков</span>
        </div>
        
        <div className="stat-item" onClick={onFollowingClick}>
          <span className="stat-number">{formatCount(followingCount)}</span>
          <span className="stat-label">подписок</span>
        </div>
        
        <div className="stat-item" onClick={onLikesClick}>
          <span className="stat-number">{formatCount(likesCount)}</span>
          <span className="stat-label">лайков</span>
        </div>
      </div>
    </div>
  );
};

export default FollowStats;
