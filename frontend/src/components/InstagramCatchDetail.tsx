import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Avatar from './Avatar';
import Icon from './Icon';
import PhotoCollage from './PhotoCollage';
import CatchLocationMap from './CatchLocationMap';
import type { CatchRecord, CatchComment } from '../types';
import logger from '../utils/logger';

interface InstagramCatchDetailProps {
  catchRecord: CatchRecord;
  comments: CatchComment[];
  onLike?: (id: number) => void;
  onAddComment?: (id: number, comment: string) => void;
  onEdit?: (id: number) => void;
  onDelete?: (id: number) => void;
  onReport?: (id: number) => void;
}

const InstagramCatchDetail: React.FC<InstagramCatchDetailProps> = ({
  catchRecord,
  comments,
  onLike,
  onAddComment,
  onEdit,
  onDelete,
  onReport
}) => {
  const navigate = useNavigate();
  const [isLiked, setIsLiked] = useState(catchRecord.liked_by_me);
  const [likesCount, setLikesCount] = useState(catchRecord.likes_count);
  const [newComment, setNewComment] = useState('');
  const [showComments, setShowComments] = useState(false);

  // Логируем JSON данные в консоль
  logger.json('Catch Detail', catchRecord);
  logger.json('Comments', comments);

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
    setShowComments(!showComments);
  };

  const handleAddComment = (e: React.FormEvent) => {
    e.preventDefault();
    if (newComment.trim() && onAddComment) {
      onAddComment(catchRecord.id, newComment.trim());
      setNewComment('');
    }
  };

  const handleUserClick = (e: React.MouseEvent) => {
    e.stopPropagation();
    navigate(`/users/${catchRecord.user.id}`);
  };

  const handleEdit = (e: React.MouseEvent) => {
    e.stopPropagation();
    if (onEdit) {
      onEdit(catchRecord.id);
    }
  };

  const handleDelete = (e: React.MouseEvent) => {
    e.stopPropagation();
    if (onDelete) {
      onDelete(catchRecord.id);
    }
  };

  const handleReport = (e: React.MouseEvent) => {
    e.stopPropagation();
    if (onReport) {
      onReport(catchRecord.id);
    }
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
    return date.toLocaleDateString('ru-RU', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const isOwner = catchRecord.user.id === parseInt(localStorage.getItem('userId') || '0');

  return (
    <div className="instagram-catch-detail">
      {/* Header */}
      <div className="catch-detail-header">
        <button className="back-button" onClick={() => navigate(-1)}>
          <Icon name="arrow_back" size="md" />
        </button>
        <h1>Улов</h1>
        <div className="header-actions">
          {isOwner ? (
            <>
              <button className="action-button" onClick={handleEdit}>
                <Icon name="edit" size="md" />
              </button>
              <button className="action-button" onClick={handleDelete}>
                <Icon name="delete" size="md" />
              </button>
            </>
          ) : (
            <button className="action-button" onClick={handleReport}>
              <Icon name="report" size="md" />
            </button>
          )}
        </div>
      </div>

      {/* Main Content */}
      <div className="catch-detail-content">
        {/* User Info */}
        <div className="catch-user-info">
          <div className="user-info" onClick={handleUserClick}>
            <Avatar 
              src={catchRecord.user.photo_url} 
              size="xl"
              crownIconUrl={catchRecord.user.crown_icon_url}
              isPremium={catchRecord.user.is_premium}
              name={catchRecord.user.name}
            />
            <div className="user-details">
              <span className="username">{catchRecord.user.username || catchRecord.user.name}</span>
              <span className="catch-date">{formatDate(catchRecord.created_at)}</span>
            </div>
          </div>
        </div>

        {/* Photos */}
        {allPhotos.length > 0 && (
          <div className="catch-photos-detail">
            <PhotoCollage 
              photos={allPhotos}
              maxPhotos={4}
            />
          </div>
        )}

        {/* Actions */}
        <div className="catch-actions-detail">
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
            <button className="action-button">
              <Icon name="send" size="md" />
            </button>
          </div>
          <button className="action-button">
            <Icon name="bookmark_border" size="md" />
          </button>
        </div>

        {/* Likes */}
        {likesCount > 0 && (
          <div className="catch-likes-detail">
            <span className="likes-count">{likesCount} отметок "Нравится"</span>
          </div>
        )}

        {/* Caption */}
        <div className="catch-caption-detail">
          <span className="username">{catchRecord.user.username || catchRecord.user.name}</span>
          <span className="caption-text">
            {catchRecord.species && (
              <span className="species-tag">#{catchRecord.species.replace(/\s+/g, '')}</span>
            )}
            {catchRecord.notes && ` ${catchRecord.notes}`}
          </span>
        </div>

        {/* Catch Parameters */}
        <div className="catch-parameters-detail">
          <h3>Параметры улова</h3>
          <div className="parameters-grid">
            {catchRecord.species && (
              <div className="parameter-card">
                <Icon name="pets" size="md" />
                <div className="parameter-info">
                  <span className="parameter-label">Вид рыбы</span>
                  <span className="parameter-value">{catchRecord.species}</span>
                </div>
              </div>
            )}
            {catchRecord.weight && (
              <div className="parameter-card">
                <Icon name="scale" size="md" />
                <div className="parameter-info">
                  <span className="parameter-label">Вес</span>
                  <span className="parameter-value">{catchRecord.weight} кг</span>
                </div>
              </div>
            )}
            {catchRecord.length && (
              <div className="parameter-card">
                <Icon name="straighten" size="md" />
                <div className="parameter-info">
                  <span className="parameter-label">Длина</span>
                  <span className="parameter-value">{catchRecord.length} см</span>
                </div>
              </div>
            )}
            {catchRecord.style && (
              <div className="parameter-card">
                <Icon name="fishing" size="md" />
                <div className="parameter-info">
                  <span className="parameter-label">Стиль ловли</span>
                  <span className="parameter-value">{catchRecord.style}</span>
                </div>
              </div>
            )}
            {catchRecord.lure && (
              <div className="parameter-card">
                <Icon name="lure" size="md" />
                <div className="parameter-info">
                  <span className="parameter-label">Приманка</span>
                  <span className="parameter-value">{catchRecord.lure}</span>
                </div>
              </div>
            )}
            {catchRecord.tackle && (
              <div className="parameter-card">
                <Icon name="tackle" size="md" />
                <div className="parameter-info">
                  <span className="parameter-label">Снасть</span>
                  <span className="parameter-value">{catchRecord.tackle}</span>
                </div>
              </div>
            )}
            {catchRecord.caught_at && (
              <div className="parameter-card">
                <Icon name="schedule" size="md" />
                <div className="parameter-info">
                  <span className="parameter-label">Время улова</span>
                  <span className="parameter-value">{formatDate(catchRecord.caught_at)}</span>
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Location */}
        {catchRecord.lat && catchRecord.lng && (
          <div className="catch-location-detail">
            <h3>Место улова</h3>
            <CatchLocationMap 
              catchRecord={catchRecord}
              height="200px"
            />
          </div>
        )}

        {/* Comments */}
        {showComments && (
          <div className="catch-comments-detail">
            <h3>Комментарии ({comments.length})</h3>
            <div className="comments-list">
              {comments.map((comment) => (
                <div key={comment.id} className="comment-item">
                  <Avatar 
                    src={comment.user.photo_url} 
                    size="lg"
                    name={comment.user.name}
                  />
                  <div className="comment-content">
                    <div className="comment-header">
                      <span className="comment-username">{comment.user.username || comment.user.name}</span>
                      <span className="comment-date">{formatDate(comment.created_at)}</span>
                    </div>
                    <p className="comment-text">{comment.body}</p>
                  </div>
                </div>
              ))}
            </div>
            
            {/* Add Comment Form */}
            <form className="add-comment-form" onSubmit={handleAddComment}>
              <input
                type="text"
                placeholder="Добавить комментарий..."
                value={newComment}
                onChange={(e) => setNewComment(e.target.value)}
                className="comment-input"
              />
              <button 
                type="submit" 
                className="comment-submit"
                disabled={!newComment.trim()}
              >
                <Icon name="send" size="md" />
              </button>
            </form>
          </div>
        )}
      </div>
    </div>
  );
};

export default InstagramCatchDetail;
