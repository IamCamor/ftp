import React, { useState, useEffect } from 'react';
import Icon from './Icon';
import { getFriends, addFishingCompanion } from '../api';
import type { Friendship, AddFishingCompanionRequest } from '../types';

interface FriendsSelectorProps {
  catchId?: number;
  trackId?: number;
  onCompanionsAdded?: (companions: any[]) => void;
  onClose?: () => void;
}

const FriendsSelector: React.FC<FriendsSelectorProps> = ({ 
  catchId, 
  trackId, 
  onCompanionsAdded, 
  onClose 
}) => {
  const [friends, setFriends] = useState<Friendship[]>([]);
  const [selectedFriends, setSelectedFriends] = useState<number[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    loadFriends();
  }, []);

  const loadFriends = async () => {
    try {
      setLoading(true);
      const friendsData = await getFriends();
      // Фильтруем только принятых друзей
      const acceptedFriends = friendsData.filter(f => f.status === 'accepted');
      setFriends(acceptedFriends);
    } catch (err) {
      setError('Не удалось загрузить список друзей');
      console.error('Error loading friends:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleFriendToggle = (friendId: number) => {
    setSelectedFriends(prev => 
      prev.includes(friendId) 
        ? prev.filter(id => id !== friendId)
        : [...prev, friendId]
    );
  };

  const handleAddCompanions = async () => {
    if (selectedFriends.length === 0) {
      alert('Выберите друзей для добавления');
      return;
    }

    try {
      setSaving(true);
      const promises = selectedFriends.map(friendId => {
        const data: AddFishingCompanionRequest = {
          companion_id: friendId,
          catch_id: catchId,
          track_id: trackId
        };
        return addFishingCompanion(data);
      });

      const companions = await Promise.all(promises);
      onCompanionsAdded?.(companions);
      onClose?.();
    } catch (err) {
      console.error('Error adding companions:', err);
      alert('Не удалось добавить друзей');
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="friends-selector">
        <div className="loading">Загрузка друзей...</div>
      </div>
    );
  }

  return (
    <div className="friends-selector">
      <div className="friends-selector-header">
        <h3>Выберите друзей</h3>
        {onClose && (
          <button className="close-btn" onClick={onClose}>
            <Icon name="close" size="md" />
          </button>
        )}
      </div>

      {error && (
        <div className="error">
          <p>{error}</p>
          <button onClick={loadFriends} className="btn btn-primary">
            Попробовать снова
          </button>
        </div>
      )}

      <div className="friends-list">
        {friends.length === 0 ? (
          <div className="empty-state">
            <Icon name="people" size="xl" />
            <p>У вас пока нет друзей</p>
            <p>Добавьте друзей, чтобы отмечать их в уловах и треках</p>
          </div>
        ) : (
          friends.map((friendship) => (
            <div 
              key={friendship.id} 
              className={`friend-item ${selectedFriends.includes(friendship.friend_id) ? 'selected' : ''}`}
              onClick={() => handleFriendToggle(friendship.friend_id)}
            >
              <div className="friend-checkbox">
                <input 
                  type="checkbox"
                  checked={selectedFriends.includes(friendship.friend_id)}
                  onChange={() => handleFriendToggle(friendship.friend_id)}
                />
              </div>
              <div className="friend-avatar">
                <img 
                  src={friendship.friend?.photo_url || '/default-avatar.png'} 
                  alt={friendship.friend?.name}
                />
              </div>
              <div className="friend-details">
                <h4>{friendship.friend?.name}</h4>
                <p>@{friendship.friend?.username}</p>
              </div>
            </div>
          ))
        )}
      </div>

      <div className="friends-selector-footer">
        <div className="selected-count">
          Выбрано: {selectedFriends.length} из {friends.length}
        </div>
        <div className="actions">
          <button 
            className="btn btn-secondary"
            onClick={onClose}
          >
            Отмена
          </button>
          <button 
            className="btn btn-primary"
            onClick={handleAddCompanions}
            disabled={saving || selectedFriends.length === 0}
          >
            {saving ? 'Добавление...' : 'Добавить друзей'}
          </button>
        </div>
      </div>
    </div>
  );
};

export default FriendsSelector;

