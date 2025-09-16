import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Avatar from './Avatar';
import Icon from './Icon';
import PhotoCollage from './PhotoCollage';
import { ReportModal } from './ReportModal';
import type { CatchRecord } from '../types';
import logger from '../utils/logger';

interface InstagramCatchCardProps {
  catchRecord: CatchRecord;
  onLike?: (id: number) => void;
  onComment?: (id: number) => void;
  onShare?: (id: number) => void;
}

const InstagramCatchCard: React.FC<InstagramCatchCardProps> = ({
  catchRecord,
  onLike,
  onComment,
  onShare
}) => {
  const navigate = useNavigate();
  const [isLiked, setIsLiked] = useState(catchRecord.liked_by_me);
  const [likesCount, setLikesCount] = useState(catchRecord.likes_count);
  const [showReportModal, setShowReportModal] = useState(false);

  // Логируем JSON данные в консоль
  logger.json('Catch Record', catchRecord);

  const handleLike = (e: React.MouseEvent) => {
    e.stopPropagation();
    setIsLiked(!isLiked);
    setLikesCount(prev => isLiked ? prev - 1 : prev + 1);
    if (onLike) {
      onLike(catchRecord.id);
    }
  };

  const handleComment = (e: React.MouseEvent) => {
    e.stopPropagation();
    if (onComment) {
      onComment(catchRecord.id);
    } else {
      // For tracks, navigate to track detail page
      if (catchRecord.type === 'track') {
        navigate(`/tracks/${catchRecord.id}`);
      } else {
        navigate(`/catch/${catchRecord.id}`);
      }
    }
  };

  const handleShare = (e: React.MouseEvent) => {
    e.stopPropagation();
    if (onShare) {
      onShare(catchRecord.id);
    }
  };

  const handleReport = (e: React.MouseEvent) => {
    e.stopPropagation();
    setShowReportModal(true);
  };

  const handleReportUser = (e: React.MouseEvent) => {
    e.stopPropagation();
    setShowReportModal(true);
  };

  const handleCardClick = () => {
    // Check if this is a track or individual catch
    if (catchRecord.type === 'track') {
      navigate(`/tracks/${catchRecord.id}`);
    } else {
      navigate(`/catch/${catchRecord.id}`);
    }
  };

  const handleUserClick = (e: React.MouseEvent) => {
    e.stopPropagation();
    navigate(`/users/${catchRecord.user.id}`);
  };


  // Собираем все фотографии для коллажа
  const allPhotos = [];
  if (catchRecord.photo_url) {
    allPhotos.push(catchRecord.photo_url);
  }
  if (catchRecord.additional_photos) {
    try {
      const additionalPhotos = JSON.parse(catchRecord.additional_photos);
      if (Array.isArray(additionalPhotos)) {
        allPhotos.push(...additionalPhotos);
      }
    } catch (e) {
      console.warn('Failed to parse additional_photos:', e);
    }
  }

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInHours = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60));
    
    if (diffInHours < 1) {
      return 'только что';
    } else if (diffInHours < 24) {
      return `${diffInHours}ч`;
    } else if (diffInHours < 48) {
      return 'вчера';
    } else {
      return date.toLocaleDateString('ru-RU', { 
        day: 'numeric', 
        month: 'short' 
      });
    }
  };

  return (
    <div className="instagram-catch-card" onClick={handleCardClick}>
      {/* Header */}
      <div className="catch-header">
        <div className="user-info" onClick={handleUserClick}>
          <Avatar 
            src={catchRecord.user.photo_url} 
            size="lg"
            crownIconUrl={catchRecord.user.crown_icon_url}
            isPremium={catchRecord.user.is_premium}
            name={catchRecord.user.name}
          />
          <div className="user-details">
            <span className="username">{catchRecord.user.username || catchRecord.user.name}</span>
            <span className="location">
              {catchRecord.point?.name || `${catchRecord.lat?.toFixed(4)}, ${catchRecord.lng?.toFixed(4)}`}
            </span>
          </div>
        </div>
        <div className="header-actions">
          <button 
            className="report-button" 
            onClick={handleReportUser}
            title="Пожаловаться на пользователя"
          >
            <Icon name="report" size="md" />
          </button>
          <button className="more-button">
            <Icon name="more_horiz" size="md" />
          </button>
        </div>
      </div>

      {/* Photos */}
      {allPhotos.length > 0 && (
        <div className="catch-photos">
          <PhotoCollage 
            photos={allPhotos}
            maxPhotos={4}
            onPhotoClick={() => handleCardClick()}
          />
        </div>
      )}

      {/* Actions */}
      <div className="catch-actions">
        <div className="actions-left">
          <button 
            className={`action-button ${isLiked ? 'liked' : ''}`}
            onClick={handleLike}
          >
            <Icon name={isLiked ? "favorite" : "favorite_border"} size="md" />
          </button>
          <button className="action-button" onClick={handleComment}>
            <Icon name="chat_bubble_outline" size="md" />
          </button>
          <button className="action-button" onClick={handleShare}>
            <Icon name="send" size="md" />
          </button>
        </div>
        <div className="actions-right">
          <button className="action-button" onClick={handleReport} title="Пожаловаться на улов">
            <Icon name="report" size="md" />
          </button>
          <button className="action-button">
            <Icon name="bookmark_border" size="md" />
          </button>
        </div>
      </div>

      {/* Likes */}
      {likesCount > 0 && (
        <div className="catch-likes">
          <span className="likes-count">{likesCount} отметок "Нравится"</span>
        </div>
      )}

      {/* Caption */}
      <div className="catch-caption">
        <span className="username">{catchRecord.user.username || catchRecord.user.name}</span>
        <span className="caption-text">
          {catchRecord.species && (
            <span className="species-tag">#{catchRecord.species.replace(/\s+/g, '')}</span>
          )}
          {catchRecord.notes && ` ${catchRecord.notes}`}
        </span>
      </div>

      {/* Comments */}
      {catchRecord.comments_count > 0 && (
        <div className="catch-comments-preview">
          <button className="view-comments">
            Посмотреть все {catchRecord.comments_count} комментариев
          </button>
        </div>
      )}

      {/* Time */}
      <div className="catch-time">
        <span>{formatDate(catchRecord.created_at)}</span>
      </div>

      {/* Catch Parameters */}
      <div className="catch-parameters">
        {catchRecord.species && (
          <div className="parameter-item">
            <Icon name="pets" size="sm" />
            <span>{catchRecord.species}</span>
          </div>
        )}
        {catchRecord.weight && (
          <div className="parameter-item">
            <Icon name="scale" size="sm" />
            <span>{catchRecord.weight} кг</span>
          </div>
        )}
        {catchRecord.length && (
          <div className="parameter-item">
            <Icon name="straighten" size="sm" />
            <span>{catchRecord.length} см</span>
          </div>
        )}
        {catchRecord.style && (
          <div className="parameter-item">
            <Icon name="fishing" size="sm" />
            <span>{catchRecord.style}</span>
          </div>
        )}
        {catchRecord.lure && (
          <div className="parameter-item">
            <Icon name="lure" size="sm" />
            <span>{catchRecord.lure}</span>
          </div>
        )}
        {catchRecord.tackle && (
          <div className="parameter-item">
            <Icon name="tackle" size="sm" />
            <span>{catchRecord.tackle}</span>
          </div>
        )}
      </div>

      {/* Report Modal */}
      <ReportModal
        isOpen={showReportModal}
        onClose={() => setShowReportModal(false)}
        type="catch"
        targetId={catchRecord.id}
        targetName={`Улов: ${catchRecord.species || 'Рыба'} от ${catchRecord.user.name}`}
      />
    </div>
  );
};

export default InstagramCatchCard;



