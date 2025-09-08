import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import Avatar from '../components/Avatar';
import Icon from '../components/Icon';
import BannerSlot from '../components/BannerSlot';
import FeedFilters from '../components/FeedFilters';
import OnlineIndicator from '../components/OnlineIndicator';
import { getFeed, likeCatch } from '../api';
import type { CatchRecord } from '../types';
import config from '../config';

const FeedScreen: React.FC = () => {
  const navigate = useNavigate();
  const [catches, setCatches] = useState<CatchRecord[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [activeFilter, setActiveFilter] = useState<'all' | 'following' | 'nearby'>('all');
  const [currentPage, setCurrentPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [userLocation, setUserLocation] = useState<{ latitude: number; longitude: number } | null>(null);

  useEffect(() => {
    loadFeed();
    getUserLocation();
  }, [activeFilter]);

  const getUserLocation = () => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          setUserLocation({
            latitude: position.coords.latitude,
            longitude: position.coords.longitude
          });
        },
        (error) => {
          console.error('Error getting location:', error);
        }
      );
    }
  };

  const loadFeed = async (page: number = 1, append: boolean = false) => {
    try {
      setLoading(true);
      
      const params: any = {
        type: activeFilter,
        page,
        limit: 20
      };

      if (activeFilter === 'nearby' && userLocation) {
        params.latitude = userLocation.latitude;
        params.longitude = userLocation.longitude;
        params.radius = 50;
      }

      const response = await getFeed(params);
      const newCatches = response.data.data;
      
      if (append) {
        setCatches(prev => [...prev, ...newCatches]);
      } else {
        setCatches(newCatches);
      }

      setCurrentPage(page);
      setHasMore(page < response.data.last_page);
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

  const handleFilterChange = (filter: 'all' | 'following' | 'nearby') => {
    setActiveFilter(filter);
    setCurrentPage(1);
    setCatches([]);
  };

  const loadMore = () => {
    if (!loading && hasMore) {
      loadFeed(currentPage + 1, true);
    }
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
          <button onClick={() => loadFeed()} className="btn btn-primary">
            Попробовать снова
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="screen">
      <BannerSlot slot="feed_top" className="feed-banner" />
      
      <FeedFilters 
        activeFilter={activeFilter}
        onFilterChange={handleFilterChange}
        className="feed-filters"
      />
      
      <div className="feed">
        {catches.map((catchRecord) => (
          <div key={catchRecord.id} className="catch-card glass">
            <div className="catch-header">
              <div className="user-info">
                <Avatar 
                  src={catchRecord.user.photo_url} 
                  size={40}
                  crownIconUrl={catchRecord.user.crown_icon_url}
                  isPremium={catchRecord.user.is_premium}
                />
                <div className="user-details">
                  <div className="user-name-row">
                    <span className="user-name">{catchRecord.user.name}</span>
                    <OnlineIndicator 
                      isOnline={catchRecord.user.is_online || false}
                      lastSeenAt={catchRecord.user.last_seen_at}
                      size="small"
                    />
                  </div>
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

        {hasMore && (
          <div className="load-more-section">
            <button 
              className="load-more-button"
              onClick={loadMore}
              disabled={loading}
            >
              {loading ? 'Загрузка...' : 'Загрузить еще'}
            </button>
          </div>
        )}
      </div>

      <BannerSlot slot="feed_bottom" className="feed-banner" />
    </div>
  );
};

export default FeedScreen;
