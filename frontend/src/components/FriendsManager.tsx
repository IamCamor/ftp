import React, { useState, useEffect } from 'react';
import Icon from './Icon';
import { 
  getFriends, 
  getFriendRequests, 
  getSentFriendRequests,
  respondToFriendRequest,
  removeFriend,
  createUserReport
} from '../api';
import type { 
  Friendship, 
  FriendRequest, 
  RespondToFriendRequestRequest,
  CreateUserReportRequest,
  User 
} from '../types';

interface FriendsManagerProps {
  onClose?: () => void;
}

const FriendsManager: React.FC<FriendsManagerProps> = ({ onClose }) => {
  const [activeTab, setActiveTab] = useState<'friends' | 'requests' | 'sent'>('friends');
  const [friends, setFriends] = useState<Friendship[]>([]);
  const [incomingRequests, setIncomingRequests] = useState<FriendRequest[]>([]);
  const [sentRequests, setSentRequests] = useState<FriendRequest[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showReportModal, setShowReportModal] = useState(false);
  const [reportReason, setReportReason] = useState<'spam' | 'harassment' | 'inappropriate_content' | 'fake_profile' | 'other'>('spam');
  const [reportDescription, setReportDescription] = useState('');
  const [reportingUser, setReportingUser] = useState<User | null>(null);

  useEffect(() => {
    loadData();
  }, [activeTab]);

  const loadData = async () => {
    try {
      setLoading(true);
      setError(null);

      switch (activeTab) {
        case 'friends':
          const friendsData = await getFriends();
          setFriends(friendsData);
          break;
        case 'requests':
          const requestsData = await getFriendRequests();
          setIncomingRequests(requestsData);
          break;
        case 'sent':
          const sentData = await getSentFriendRequests();
          setSentRequests(sentData);
          break;
      }
    } catch (err) {
      setError('Не удалось загрузить данные');
      console.error('Error loading friends data:', err);
    } finally {
      setLoading(false);
    }
  };


  const handleRespondToRequest = async (requestId: number, action: 'accept' | 'decline') => {
    try {
      const data: RespondToFriendRequestRequest = {
        request_id: requestId,
        action
      };
      await respondToFriendRequest(data);
      loadData();
    } catch (err) {
      console.error('Error responding to friend request:', err);
      alert('Не удалось обработать заявку');
    }
  };

  const handleRemoveFriend = async (friendId: number) => {
    if (!confirm('Вы уверены, что хотите удалить этого пользователя из друзей?')) {
      return;
    }

    try {
      await removeFriend(friendId);
      loadData();
    } catch (err) {
      console.error('Error removing friend:', err);
      alert('Не удалось удалить друга');
    }
  };


  const handleReportUser = (user: User) => {
    setReportingUser(user);
    setShowReportModal(true);
  };

  const handleSubmitReport = async () => {
    if (!reportingUser) return;

    try {
      const data: CreateUserReportRequest = {
        reported_user_id: reportingUser.id,
        reason: reportReason,
        description: reportDescription
      };
      await createUserReport(data);
      setShowReportModal(false);
      setReportDescription('');
      alert('Жалоба отправлена');
    } catch (err) {
      console.error('Error submitting report:', err);
      alert('Не удалось отправить жалобу');
    }
  };

  const getStatusBadge = (status: string) => {
    const statusConfig = {
      pending: { text: 'Ожидает', class: 'status-pending' },
      accepted: { text: 'Друзья', class: 'status-accepted' },
      declined: { text: 'Отклонено', class: 'status-declined' },
      blocked: { text: 'Заблокирован', class: 'status-blocked' }
    };

    const config = statusConfig[status as keyof typeof statusConfig] || statusConfig.pending;
    return <span className={`status-badge ${config.class}`}>{config.text}</span>;
  };

  if (loading) {
    return (
      <div className="friends-manager">
        <div className="loading">Загрузка...</div>
      </div>
    );
  }

  return (
    <div className="friends-manager">
      <div className="friends-header">
        <h3>Друзья</h3>
        {onClose && (
          <button className="close-btn" onClick={onClose}>
            <Icon name="close" size="md" />
          </button>
        )}
      </div>

      <div className="friends-tabs">
        <button 
          className={`tab ${activeTab === 'friends' ? 'active' : ''}`}
          onClick={() => setActiveTab('friends')}
        >
          Друзья ({friends.length})
        </button>
        <button 
          className={`tab ${activeTab === 'requests' ? 'active' : ''}`}
          onClick={() => setActiveTab('requests')}
        >
          Заявки ({incomingRequests.length})
        </button>
        <button 
          className={`tab ${activeTab === 'sent' ? 'active' : ''}`}
          onClick={() => setActiveTab('sent')}
        >
          Отправленные ({sentRequests.length})
        </button>
      </div>

      <div className="friends-content">
        {error && (
          <div className="error">
            <p>{error}</p>
            <button onClick={loadData} className="btn btn-primary">
              Попробовать снова
            </button>
          </div>
        )}

        {activeTab === 'friends' && (
          <div className="friends-list">
            {friends.length === 0 ? (
              <div className="empty-state">
                <Icon name="people" size="xl" />
                <p>У вас пока нет друзей</p>
              </div>
            ) : (
              friends.map((friendship) => (
                <div key={friendship.id} className="friend-item">
                  <div className="friend-info">
                    <div className="friend-avatar">
                      <img 
                        src={friendship.friend?.photo_url || '/default-avatar.png'} 
                        alt={friendship.friend?.name}
                      />
                    </div>
                    <div className="friend-details">
                      <h4>{friendship.friend?.name}</h4>
                      <p>@{friendship.friend?.username}</p>
                      {getStatusBadge(friendship.status)}
                    </div>
                  </div>
                  <div className="friend-actions">
                    <button 
                      className="btn btn-outline btn-sm"
                      onClick={() => handleRemoveFriend(friendship.friend_id)}
                    >
                      Удалить
                    </button>
                    <button 
                      className="btn btn-outline btn-sm"
                      onClick={() => handleReportUser(friendship.friend!)}
                    >
                      Пожаловаться
                    </button>
                  </div>
                </div>
              ))
            )}
          </div>
        )}

        {activeTab === 'requests' && (
          <div className="requests-list">
            {incomingRequests.length === 0 ? (
              <div className="empty-state">
                <Icon name="person_add" size="xl" />
                <p>Нет входящих заявок</p>
              </div>
            ) : (
              incomingRequests.map((request) => (
                <div key={request.id} className="request-item">
                  <div className="request-info">
                    <div className="request-avatar">
                      <img 
                        src={request.from_user?.photo_url || '/default-avatar.png'} 
                        alt={request.from_user?.name}
                      />
                    </div>
                    <div className="request-details">
                      <h4>{request.from_user?.name}</h4>
                      <p>@{request.from_user?.username}</p>
                      {request.message && <p className="request-message">"{request.message}"</p>}
                    </div>
                  </div>
                  <div className="request-actions">
                    <button 
                      className="btn btn-primary btn-sm"
                      onClick={() => handleRespondToRequest(request.id, 'accept')}
                    >
                      Принять
                    </button>
                    <button 
                      className="btn btn-secondary btn-sm"
                      onClick={() => handleRespondToRequest(request.id, 'decline')}
                    >
                      Отклонить
                    </button>
                    <button 
                      className="btn btn-outline btn-sm"
                      onClick={() => handleReportUser(request.from_user!)}
                    >
                      Пожаловаться
                    </button>
                  </div>
                </div>
              ))
            )}
          </div>
        )}

        {activeTab === 'sent' && (
          <div className="sent-requests-list">
            {sentRequests.length === 0 ? (
              <div className="empty-state">
                <Icon name="send" size="xl" />
                <p>Нет отправленных заявок</p>
              </div>
            ) : (
              sentRequests.map((request) => (
                <div key={request.id} className="request-item">
                  <div className="request-info">
                    <div className="request-avatar">
                      <img 
                        src={request.to_user?.photo_url || '/default-avatar.png'} 
                        alt={request.to_user?.name}
                      />
                    </div>
                    <div className="request-details">
                      <h4>{request.to_user?.name}</h4>
                      <p>@{request.to_user?.username}</p>
                      {getStatusBadge(request.status)}
                    </div>
                  </div>
                </div>
              ))
            )}
          </div>
        )}
      </div>

      {/* Report Modal */}
      {showReportModal && reportingUser && (
        <div className="modal-overlay" onClick={() => setShowReportModal(false)}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h3>Пожаловаться на пользователя</h3>
              <button 
                className="modal-close"
                onClick={() => setShowReportModal(false)}
              >
                <Icon name="close" size="md" />
              </button>
            </div>
            
            <div className="modal-body">
              <p>Пользователь: <strong>{reportingUser.name}</strong></p>
              
              <div className="form-group">
                <label>Причина жалобы:</label>
                <select 
                  value={reportReason}
                  onChange={(e) => setReportReason(e.target.value as any)}
                  className="form-select"
                >
                  <option value="spam">Спам</option>
                  <option value="harassment">Оскорбления</option>
                  <option value="inappropriate_content">Неподходящий контент</option>
                  <option value="fake_profile">Фейковый профиль</option>
                  <option value="other">Другое</option>
                </select>
              </div>
              
              <div className="form-group">
                <label>Описание (необязательно):</label>
                <textarea
                  value={reportDescription}
                  onChange={(e) => setReportDescription(e.target.value)}
                  placeholder="Опишите проблему..."
                  className="form-textarea"
                  rows={3}
                />
              </div>
            </div>
            
            <div className="modal-footer">
              <button 
                className="btn btn-secondary"
                onClick={() => setShowReportModal(false)}
              >
                Отмена
              </button>
              <button 
                className="btn btn-primary"
                onClick={handleSubmitReport}
              >
                Отправить жалобу
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default FriendsManager;
