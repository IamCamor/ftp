import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import Avatar from '../components/Avatar';
import Icon from '../components/Icon';
import BannerSlot from '../components/BannerSlot';
import { feed, likeCatch } from '../api';
import type { CatchRecord } from '../types';
import config from '../config';

const FeedScreen: React.FC = () => {
  const navigate = useNavigate();
  const [catches, setCatches] = useState<CatchRecord[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    loadFeed();
  }, []);

  const loadFeed = async () => {
    try {
      setLoading(true);
      const data = await feed();
      setCatches(data);
    } catch (err) {
      setError('Не удалось загрузить ленту');
      console.error('Feed loading error:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleLike = async (catchId: number) => {
    try {
      const result = await likeCatch(catchId);
      setCatches((prev: CatchRecord[]) => prev.map((c: CatchRecord) => 
        c.id === catchId 
          ? { ...c, liked_by_me: result.liked, likes_count: result.likes_count }
          : c
      ));
    } catch (err) {
      console.error('Like error:', err);
    }
  };

  const handleCatchClick = (catchId: number) => {
    navigate(config.routes.catchDetail(catchId));
  };

  if (loading) {
    return (
      <div className="screen">
        <div className="loading">Загрузка ленты...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="screen">
        <div className="error">
          <p>{error}</p>
          <button onClick={loadFeed} className="btn btn-primary">
            Попробовать снова
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="screen">
      <BannerSlot slot="feed_top" className="feed-banner" />
      
      <div className="feed">
        {catches.map((catchRecord) => (
          <div key={catchRecord.id} className="catch-card glass">
            <div className="catch-header">
              <div className="user-info">
                <Avatar src={catchRecord.user.photo_url} size={40} />
                <div className="user-details">
                  <span className="user-name">{catchRecord.user.name}</span>
                  {catchRecord.user.username && (
                    <span className="user-username">@{catchRecord.user.username}</span>
                  )}
                </div>
              </div>
              <span className="catch-date">
                {new Date(catchRecord.created_at).toLocaleDateString()}
              </span>
            </div>

            {catchRecord.photo_url && (
              <div 
                className="catch-photo"
                onClick={() => handleCatchClick(catchRecord.id)}
              >
                <img src={catchRecord.photo_url} alt="Улов" />
              </div>
            )}

            <div className="catch-content">
              <div className="catch-info">
                {catchRecord.species && (
                  <div className="info-item">
                    <Icon name="pets" size={16} />
                    <span>{catchRecord.species}</span>
                  </div>
                )}
                {catchRecord.length && (
                  <div className="info-item">
                    <Icon name="straighten" size={16} />
                    <span>{catchRecord.length} см</span>
                  </div>
                )}
                {catchRecord.weight && (
                  <div className="info-item">
                    <Icon name="scale" size={16} />
                    <span>{catchRecord.weight} кг</span>
                  </div>
                )}
                {catchRecord.caught_at && (
                  <div className="info-item">
                    <Icon name="schedule" size={16} />
                    <span>{new Date(catchRecord.caught_at).toLocaleString()}</span>
                  </div>
                )}
              </div>

              {catchRecord.notes && (
                <p className="catch-notes">{catchRecord.notes}</p>
              )}

              <div className="catch-actions">
                <button
                  className={`action-button ${catchRecord.liked_by_me ? 'liked' : ''}`}
                  onClick={() => handleLike(catchRecord.id)}
                >
                  <Icon 
                    name="favorite" 
                    filled={catchRecord.liked_by_me}
                    size={20}
                  />
                  <span>{catchRecord.likes_count}</span>
                </button>

                <button
                  className="action-button"
                  onClick={() => handleCatchClick(catchRecord.id)}
                >
                  <Icon name="comment" size={20} />
                  <span>{catchRecord.comments_count}</span>
                </button>

                <button className="action-button">
                  <Icon name="share" size={20} />
                </button>
              </div>
            </div>
          </div>
        ))}
      </div>

      <BannerSlot slot="feed_bottom" className="feed-banner" />
    </div>
  );
};

export default FeedScreen;
