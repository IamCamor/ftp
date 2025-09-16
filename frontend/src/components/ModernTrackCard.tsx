import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { formatDistanceToNow } from 'date-fns';
import { ru } from 'date-fns/locale';
import { MapPin, Clock, Play, Pause, Square, MoreHorizontal, Navigation, Activity } from 'lucide-react';
import Avatar from './Avatar';
import Icon from './Icon';
import { ReportModal } from './ReportModal';
import type { Track } from '../types';

interface ModernTrackCardProps {
  track: Track;
}

const ModernTrackCard: React.FC<ModernTrackCardProps> = ({
  track
}) => {
  const navigate = useNavigate();
  const [showReportModal, setShowReportModal] = useState(false);

  const handleCardClick = () => {
    navigate(`/tracks/${track.id}`);
  };

  const handleUserClick = (e: React.MouseEvent) => {
    e.stopPropagation();
    navigate(`/users/${track.user.id}`);
  };

  const handleReport = (e: React.MouseEvent) => {
    e.stopPropagation();
    setShowReportModal(true);
  };

  const formatDuration = (minutes: number | undefined) => {
    if (!minutes) return 'В процессе';
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return hours > 0 ? `${hours}ч ${mins}м` : `${mins}м`;
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active': return 'status-active';
      case 'paused': return 'status-paused';
      case 'completed': return 'status-completed';
      default: return 'status-default';
    }
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'active': return 'Активен';
      case 'paused': return 'Приостановлен';
      case 'completed': return 'Завершен';
      default: return 'Неизвестно';
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'active': return <Play size="sm" />;
      case 'paused': return <Pause size="sm" />;
      case 'completed': return <Square size="sm" />;
      default: return <Activity size="sm" />;
    }
  };

  const formatDistance = (km: number | undefined) => {
    if (!km) return '0 км';
    if (km < 1) {
      return `${(km * 1000).toFixed(0)} м`;
    }
    return `${km.toFixed(1)} км`;
  };

  return (
    <>
      <div className="p-4" onClick={handleCardClick}>
        {/* Header */}
        <div className="flex items-center justify-between mb-4">
          <div className="flex items-center space-x-3" onClick={handleUserClick}>
            <Avatar 
              src={track.user.avatar_url} 
              name={track.user.name}
              size="lg"
            />
            <div>
              <div className="md3-title-medium">{track.user.name}</div>
              <div className="md3-body-small text-gray-500">
                {formatDistanceToNow(new Date(track.created_at), { 
                  addSuffix: true, 
                  locale: ru 
                })}
              </div>
            </div>
          </div>
          <button 
            className="md3-button md3-button-text p-2"
            onClick={handleReport}
            title="Еще"
          >
            <MoreHorizontal size={20} />
          </button>
        </div>

        {/* Track Info */}
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="md3-title-large">{track.name || track.title || 'Без названия'}</h3>
            <div className={`md3-chip ${getStatusColor(track.status)}`}>
              {getStatusIcon(track.status)}
              <span className="ml-1">{getStatusText(track.status)}</span>
            </div>
          </div>

          {track.description && (
            <div className="md3-body-large text-gray-600">
              {track.description}
            </div>
          )}

          {/* Track Stats */}
          <div className="flex flex-wrap gap-2">
            <div className="md3-chip">
              <Clock size={16} className="mr-1" />
              <span>{formatDuration(track.duration_minutes)}</span>
            </div>
            <div className="md3-chip">
              <Navigation size={16} className="mr-1" />
              <span>{formatDistance(track.total_distance)}</span>
            </div>
            {track.place && (
              <div className="md3-chip">
                <MapPin size={16} className="mr-1" />
                <span>{track.place.name}</span>
              </div>
            )}
          </div>
        </div>

        {/* Track Preview */}
        {track.track_points && track.track_points.length > 0 && (
          <div className="mt-4 p-4 bg-gray-50 rounded-2xl">
            <div className="flex items-center justify-between">
              <div className="flex items-center space-x-2">
                <Icon name="map" size={20} />
                <span className="md3-body-medium">Трек на карте</span>
              </div>
              <div className="md3-label-medium text-gray-500">
                {track.track_points.length} точек
              </div>
            </div>
          </div>
        )}

        {/* Actions */}
        <div className="mt-4 pt-4 border-t border-gray-100">
          <button 
            className="md3-button md3-button-filled w-full"
            onClick={handleCardClick}
          >
            <Icon name="visibility" size={20} className="mr-2" />
            <span>Просмотреть трек</span>
          </button>
        </div>
      </div>

      {showReportModal && (
        <ReportModal
          isOpen={showReportModal}
          onClose={() => setShowReportModal(false)}
          type="catch"
          targetId={track.id}
          targetName={`Трек #${track.id}`}
        />
      )}
    </>
  );
};

export default ModernTrackCard;
