import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import Avatar from '../components/Avatar';
import Icon from '../components/Icon';
import { liveSessions, joinLiveSession, leaveLiveSession } from '../api';
import type { LiveSession } from '../types';

const LiveFishingPage: React.FC = () => {
  const navigate = useNavigate();
  const [sessions, setSessions] = useState<LiveSession[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [joinedSessions, setJoinedSessions] = useState<Set<number>>(new Set());

  useEffect(() => {
    loadLiveSessions();
  }, []);

  const loadLiveSessions = async () => {
    try {
      setLoading(true);
      const data = await liveSessions();
      setSessions(data);
    } catch (err) {
      setError('Не удалось загрузить трансляции');
      console.error('Live sessions loading error:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleJoinSession = async (sessionId: number) => {
    try {
      await joinLiveSession(sessionId);
      setJoinedSessions(prev => new Set(prev).add(sessionId));
      setSessions(prev => prev.map(s => 
        s.id === sessionId ? { ...s, viewers_count: s.viewers_count + 1 } : s
      ));
    } catch (err) {
      console.error('Failed to join session:', err);
    }
  };

  const handleLeaveSession = async (sessionId: number) => {
    try {
      await leaveLiveSession(sessionId);
      setJoinedSessions(prev => {
        const newSet = new Set(prev);
        newSet.delete(sessionId);
        return newSet;
      });
      setSessions(prev => prev.map(s => 
        s.id === sessionId ? { ...s, viewers_count: Math.max(0, s.viewers_count - 1) } : s
      ));
    } catch (err) {
      console.error('Failed to leave session:', err);
    }
  };

  const handleStartStream = () => {
    navigate('/live/start');
  };

  const formatDuration = (startedAt: string) => {
    const start = new Date(startedAt);
    const now = new Date();
    const diff = Math.floor((now.getTime() - start.getTime()) / 1000);
    
    const hours = Math.floor(diff / 3600);
    const minutes = Math.floor((diff % 3600) / 60);
    
    if (hours > 0) {
      return `${hours}ч ${minutes}м`;
    }
    return `${minutes}м`;
  };

  if (loading) {
    return (
      <div className="screen">
        <div className="loading">Загрузка трансляций...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="screen">
        <div className="error">
          <p>{error}</p>
          <button onClick={loadLiveSessions} className="btn btn-primary">
            Попробовать снова
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="screen">
      <div className="live-header">
        <h2>Онлайн рыбалка</h2>
        <button className="btn btn-primary" onClick={handleStartStream}>
          <Icon name="videocam" size={20} />
          Начать трансляцию
        </button>
      </div>

      {sessions.length === 0 ? (
        <div className="empty-state">
          <Icon name="videocam_off" size={64} />
          <h3>Нет активных трансляций</h3>
          <p>Начните свою трансляцию или дождитесь других рыбаков</p>
        </div>
      ) : (
        <div className="live-sessions-list">
          {sessions.map((session) => (
            <div key={session.id} className="live-session-card glass">
              <div className="session-header">
                <div className="session-info">
                  <h3>{session.title}</h3>
                  <div className="session-meta">
                    <Avatar src={session.user?.photo_url} size={32} />
                    <div>
                      <span className="streamer-name">{session.user?.name}</span>
                      <div className="session-stats">
                        <Icon name="visibility" size={16} />
                        <span>{session.viewers_count} зрителей</span>
                        {session.started_at && (
                          <>
                            <Icon name="schedule" size={16} />
                            <span>{formatDuration(session.started_at)}</span>
                          </>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
                
                <div className="live-indicator">
                  <div className="live-dot"></div>
                  <span>LIVE</span>
                </div>
              </div>

              {session.description && (
                <p className="session-description">{session.description}</p>
              )}

              <div className="session-location">
                <Icon name="location_on" size={16} />
                <span>{session.lat.toFixed(4)}, {session.lng.toFixed(4)}</span>
              </div>

              <div className="session-actions">
                {joinedSessions.has(session.id) ? (
                  <button 
                    className="btn btn-secondary"
                    onClick={() => handleLeaveSession(session.id)}
                  >
                    <Icon name="stop" size={16} />
                    Покинуть
                  </button>
                ) : (
                  <button 
                    className="btn btn-primary"
                    onClick={() => handleJoinSession(session.id)}
                  >
                    <Icon name="play_arrow" size={16} />
                    Смотреть
                  </button>
                )}
                
                <button 
                  className="btn btn-secondary"
                  onClick={() => navigate(`/live/${session.id}`)}
                >
                  <Icon name="open_in_new" size={16} />
                  Открыть
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default LiveFishingPage;

