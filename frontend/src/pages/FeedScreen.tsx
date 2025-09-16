import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import Icon from '../components/Icon';
import BannerSlot from '../components/BannerSlot';
import FeedFilters from '../components/FeedFilters';
import PageLayout from '../components/PageLayout';
import ModernCatchCard from '../components/ModernCatchCard';
import { useInfiniteScroll } from '../hooks/useInfiniteScroll';
import { getFeed, likeCatch } from '../api';
import type { CatchRecord } from '../types';

const FeedScreen: React.FC = () => {
  const navigate = useNavigate();
  const [catches, setCatches] = useState<CatchRecord[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [activeFilter, setActiveFilter] = useState<'all' | 'following' | 'nearby'>('all');
  const [currentPage, setCurrentPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [userLocation, setUserLocation] = useState<{ latitude: number; longitude: number } | null>(null);
  const [loadingMore, setLoadingMore] = useState(false);

  useEffect(() => {
    loadFeed();
    // getUserLocation(); // Removed automatic location request to comply with browser policy
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
      if (append) {
        setLoadingMore(true);
      } else {
        setLoading(true);
      }
      
      setError(null);

      // Check authentication for following feed
      if (activeFilter === 'following' && !localStorage.getItem('token')) {
        setError('Для просмотра ленты подписок необходимо войти в систему');
        return;
      }
      
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
      const newCatches = response.data.data || [];
      
      if (append) {
        setCatches(prev => [...prev, ...newCatches]);
      } else {
        setCatches(newCatches);
      }

      setCurrentPage(page);
      setHasMore(page < (response.data.last_page || 1));
    } catch (err: any) {
      if (err.message?.includes('Authentication required')) {
        setError('Для просмотра ленты подписок необходимо войти в систему');
      } else {
        setError('Не удалось загрузить ленту');
      }
      console.error('Feed loading error:', err);
    } finally {
      setLoading(false);
      setLoadingMore(false);
    }
  };



  const handleLike = async (id: number) => {
    try {
      await likeCatch(id);
    } catch (error) {
      console.error('Failed to like catch:', error);
    }
  };

  const handleComment = (id: number) => {
    // Find the catch record to check its type
    const catchRecord = catches.find(c => c.id === id);
    if (catchRecord?.type === 'track') {
      navigate(`/tracks/${id}`);
    } else {
      navigate(`/catch/${id}`);
    }
  };

  const handleShare = (id: number) => {
    // TODO: Implement share functionality
    console.log('Share catch:', id);
  };


  const handleFilterChange = (filter: 'all' | 'following' | 'nearby') => {
    // Check if user is authenticated for following feed
    if (filter === 'following' && !localStorage.getItem('token')) {
      setError('Для просмотра ленты подписок необходимо войти в систему');
      setActiveFilter('all'); // Switch back to 'all' filter
      return;
    }
    
    setActiveFilter(filter);
    setCurrentPage(1);
    setCatches([]);
    setError(null);
  };

  const loadMore = () => {
    if (!loadingMore && hasMore) {
      loadFeed(currentPage + 1, true);
    }
  };

  // Хук для бесконечной прокрутки
  const { sentinelRef } = useInfiniteScroll({
    hasMore,
    loading: loadingMore,
    onLoadMore: loadMore,
    rootMargin: '200px'
  });

  return (
    <PageLayout
      title="Лента уловов"
      description="Последние уловы рыбаков, фотографии и истории успешной рыбалки"
      keywords={['уловы', 'рыбалка', 'фото', 'лента', 'рыбаки']}
      className="screen"
      loading={loading}
      error={error}
      onRetry={() => loadFeed()}
    >
      <BannerSlot slot="feed_top" className="feed-banner" />
      
      <div className="feed-controls">
        <FeedFilters 
          activeFilter={activeFilter}
          onFilterChange={handleFilterChange}
          className="feed-filters"
        />
        {activeFilter === 'nearby' && !userLocation && (
          <button 
            onClick={getUserLocation}
            className="btn btn-outline btn-sm location-btn"
            title="Разрешить доступ к геолокации для показа ближайших уловов"
          >
            <Icon name="location_on" size="sm" />
            Показать рядом
          </button>
        )}
      </div>
      
      <div className="feed-container">
        <div className="instagram-feed">
          {catches.map((catchRecord) => (
            <ModernCatchCard
              key={catchRecord.id}
              catchRecord={catchRecord}
              onLike={handleLike}
              onComment={handleComment}
              onShare={handleShare}
            />
          ))}

          {/* Индикатор загрузки для бесконечной прокрутки */}
          {loadingMore && (
            <div className="infinite-loading">
              <div className="loading-spinner">
                <Icon name="refresh" size="md" />
              </div>
              <p>Загрузка новых уловов...</p>
            </div>
          )}

          {/* Элемент-наблюдатель для бесконечной прокрутки */}
          {hasMore && (
            <div 
              ref={sentinelRef}
              style={{ 
                height: '1px', 
                width: '100%',
                position: 'absolute',
                bottom: '-200px'
              }}
              aria-hidden="true"
            />
          )}

          {/* Сообщение о том, что больше нет данных */}
          {!hasMore && catches.length > 0 && (
            <div className="no-more-data">
              <Icon name="check_circle" size="md" />
              <p>Вы просмотрели все доступные уловы</p>
            </div>
          )}
        </div>
      </div>

      <BannerSlot slot="feed_bottom" className="feed-banner" />
    </PageLayout>
  );
};

export default FeedScreen;
