import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { formatDistanceToNow } from 'date-fns';
import { ru } from 'date-fns/locale';
import { Heart, MessageCircle, Share, MoreHorizontal, MapPin, Fish, Scale, Ruler } from 'lucide-react';
import Avatar from './Avatar';
import PhotoCollage from './PhotoCollage';
import { ReportModal } from './ReportModal';
import type { CatchRecord } from '../types';
import logger from '../utils/logger';

interface ModernCatchCardProps {
  catchRecord: CatchRecord;
  onLike?: (id: number) => void;
  onComment?: (id: number) => void;
  onShare?: (id: number) => void;
}

const ModernCatchCard: React.FC<ModernCatchCardProps> = ({
  catchRecord,
  onLike,
  onComment,
  onShare
}) => {
  const navigate = useNavigate();
  const [isLiked, setIsLiked] = useState(catchRecord.liked_by_me);
  const [likesCount, setLikesCount] = useState(catchRecord.likes_count);
  const [showReportModal, setShowReportModal] = useState(false);

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
      navigate(`/catch/${catchRecord.id}`);
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

  const handleCardClick = () => {
    navigate(`/catch/${catchRecord.id}`);
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
      console.error('Error parsing additional photos:', e);
    }
  }

  const formatWeight = (weight: number | undefined) => {
    if (!weight) return null;
    return weight >= 1000 ? `${(weight / 1000).toFixed(1)} кг` : `${weight} г`;
  };

  const formatLength = (length: number | undefined) => {
    if (!length) return null;
    return `${length} см`;
  };

  return (
    <>
      <div className="p-4" onClick={handleCardClick}>
        {/* Header */}
        <div className="flex items-center justify-between mb-4">
          <div className="flex items-center space-x-3" onClick={handleUserClick}>
            <Avatar 
              src={catchRecord.user.photo_url} 
              name={catchRecord.user.name}
              size="lg"
            />
            <div>
              <div className="md3-title-medium">{catchRecord.user.name}</div>
              <div className="md3-body-small text-gray-500">@{catchRecord.user.username}</div>
            </div>
          </div>
          <div className="flex items-center space-x-2">
            <div className="md3-body-small text-gray-500">
              {formatDistanceToNow(new Date(catchRecord.created_at), { 
                addSuffix: true, 
                locale: ru 
              })}
            </div>
            <button 
              className="md3-button md3-button-text p-2"
              onClick={handleReport}
              title="Еще"
            >
              <MoreHorizontal size={20} />
            </button>
          </div>
        </div>

        {/* Photo Section */}
        {allPhotos.length > 0 && (
          <div className="mb-4 rounded-2xl overflow-hidden">
            <PhotoCollage photos={allPhotos} />
          </div>
        )}

        {/* Content Section */}
        <div className="space-y-4">
          {/* Catch Details */}
          <div className="flex flex-wrap gap-2">
            {catchRecord.fish_species && (
              <div className="md3-chip">
                <Fish size={16} className="mr-1" />
                <span>{catchRecord.fish_species.name}</span>
              </div>
            )}
            {formatWeight(catchRecord.weight) && (
              <div className="md3-chip">
                <Scale size={16} className="mr-1" />
                <span>{formatWeight(catchRecord.weight)}</span>
              </div>
            )}
            {formatLength(catchRecord.length) && (
              <div className="md3-chip">
                <Ruler size={16} className="mr-1" />
                <span>{formatLength(catchRecord.length)}</span>
              </div>
            )}
            {catchRecord.point && (
              <div className="md3-chip">
                <MapPin size={16} className="mr-1" />
                <span>{catchRecord.point.name}</span>
              </div>
            )}
          </div>

          {/* Description */}
          {catchRecord.notes && (
            <div className="md3-body-large">
              <span className="font-medium">{catchRecord.user.name}</span>
              <span className="ml-2">{catchRecord.notes}</span>
            </div>
          )}

          {/* Comments Preview */}
          {catchRecord.comments_count > 0 && (
            <button 
              className="md3-button md3-button-text text-left"
              onClick={handleComment}
            >
              Показать все комментарии ({catchRecord.comments_count})
            </button>
          )}
        </div>

        {/* Actions */}
        <div className="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
          <div className="flex items-center space-x-4">
            <button 
              className={`md3-button md3-button-text flex items-center space-x-2 ${isLiked ? 'text-red-500' : ''}`}
              onClick={handleLike}
            >
              <Heart size={20} fill={isLiked ? 'currentColor' : 'none'} />
              <span className="md3-label-medium">{likesCount}</span>
            </button>
            <button 
              className="md3-button md3-button-text flex items-center space-x-2"
              onClick={handleComment}
            >
              <MessageCircle size={20} />
              <span className="md3-label-medium">{catchRecord.comments_count}</span>
            </button>
            <button 
              className="md3-button md3-button-text"
              onClick={handleShare}
            >
              <Share size={20} />
            </button>
          </div>
        </div>
      </div>

      {showReportModal && (
        <ReportModal
          isOpen={showReportModal}
          onClose={() => setShowReportModal(false)}
          type="catch"
          targetId={catchRecord.id}
          targetName={`Улов #${catchRecord.id}`}
        />
      )}
    </>
  );
};

export default ModernCatchCard;
