import React, { useState, useEffect } from 'react';
import Icon from './Icon';
import { 
  getFishingCompanions, 
  respondToFishingCompanion 
} from '../api';
import type { FishingCompanion, RespondToFishingCompanionRequest } from '../types';

interface FriendsNotificationsProps {
  onClose?: () => void;
}

const FriendsNotifications: React.FC<FriendsNotificationsProps> = ({ onClose }) => {
  const [companions, setCompanions] = useState<FishingCompanion[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    loadCompanions();
  }, []);

  const loadCompanions = async () => {
    try {
      setLoading(true);
      const companionsData = await getFishingCompanions();
      // Фильтруем только входящие запросы
      const pendingCompanions = companionsData.filter(c => c.status === 'pending');
      setCompanions(pendingCompanions);
    } catch (err) {
      setError('Не удалось загрузить уведомления');
      console.error('Error loading companions:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleRespondToCompanion = async (companionId: number, action: 'accept' | 'decline') => {
    try {
      const data: RespondToFishingCompanionRequest = {
        companion_id: companionId,
        action
      };
      await respondToFishingCompanion(data);
      loadCompanions();
    } catch (err) {
      console.error('Error responding to companion request:', err);
      alert('Не удалось обработать запрос');
    }
  };

  const getActivityType = (companion: FishingCompanion) => {
    if (companion.catch_id) {
      return 'улове';
    } else if (companion.track_id) {
      return 'треке';
    }
    return 'активности';
  };

  if (loading) {
    return (
      <div className="friends-notifications">
        <div className="loading">Загрузка уведомлений...</div>
      </div>
    );
  }

  return (
    <div className="friends-notifications">
      <div className="notifications-header">
        <h3>Уведомления о дружбе</h3>
        {onClose && (
          <button className="close-btn" onClick={onClose}>
            <Icon name="close" size="md" />
          </button>
        )}
      </div>

      {error && (
        <div className="error">
          <p>{error}</p>
          <button onClick={loadCompanions} className="btn btn-primary">
            Попробовать снова
          </button>
        </div>
      )}

      <div className="notifications-list">
        {companions.length === 0 ? (
          <div className="empty-state">
            <Icon name="notifications_none" size="xl" />
            <p>Нет новых уведомлений</p>
          </div>
        ) : (
          companions.map((companion) => (
            <div key={companion.id} className="notification-item">
              <div className="notification-avatar">
                <img 
                  src={companion.user?.photo_url || '/default-avatar.png'} 
                  alt={companion.user?.name}
                />
              </div>
              <div className="notification-content">
                <div className="notification-text">
                  <strong>{companion.user?.name}</strong> отметил вас в своем {getActivityType(companion)}
                </div>
                <div className="notification-time">
                  {new Date(companion.created_at).toLocaleString()}
                </div>
              </div>
              <div className="notification-actions">
                <button 
                  className="btn btn-primary btn-sm"
                  onClick={() => handleRespondToCompanion(companion.id, 'accept')}
                >
                  <Icon name="check" size="sm" />
                  Подтвердить
                </button>
                <button 
                  className="btn btn-secondary btn-sm"
                  onClick={() => handleRespondToCompanion(companion.id, 'decline')}
                >
                  <Icon name="close" size="sm" />
                  Отклонить
                </button>
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  );
};

export default FriendsNotifications;

