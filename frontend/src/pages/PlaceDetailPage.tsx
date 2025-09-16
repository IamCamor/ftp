import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import Avatar from '../components/Avatar';
import Icon from '../components/Icon';
import CatchLocationMap from '../components/CatchLocationMap';
import { ReportModal } from '../components/ReportModal';
import { getPlaceDetail } from '../api';
import type { Point, User } from '../types';

interface PlaceReview {
  id: number;
  user: User;
  rating: number;
  comment: string;
  created_at: string;
}

interface Place extends Point {
  // Дополнительные поля для места
  place_type?: 'lake' | 'river' | 'pond' | 'sea' | 'reservoir' | 'other' | 'fishing_spot' | 'resort' | 'slip' | 'marina';
  has_parking?: boolean;
  has_security?: boolean;
  contact_phone?: string;
  contact_email?: string;
  website?: string;
  amenities?: string[];
  reviews?: PlaceReview[];
  rating?: number;
  reviews_count?: number;
}

const PlaceDetailPage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [place, setPlace] = useState<Place | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [currentPhotoIndex, setCurrentPhotoIndex] = useState(0);
  const [showReportModal, setShowReportModal] = useState(false);

  useEffect(() => {
    if (id) {
      loadPlaceDetail();
    }
  }, [id]);

  const loadPlaceDetail = async () => {
    try {
      setLoading(true);
      const response = await getPlaceDetail(Number(id));
      setPlace(response);
    } catch (err) {
      setError('Не удалось загрузить место');
      console.error('Place detail loading error:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleReport = () => {
    setShowReportModal(true);
  };

  const getPhotos = () => {
    if (!place) return [];
    
    const photos = [];
    if (place.cover_url) {
      photos.push(place.cover_url);
    }
    if (place.media && place.media.length > 0) {
      photos.push(...place.media.map(media => media.url));
    }
    
    return photos;
  };

  const nextPhoto = () => {
    const photos = getPhotos();
    setCurrentPhotoIndex((prev) => (prev + 1) % photos.length);
  };

  const prevPhoto = () => {
    const photos = getPhotos();
    setCurrentPhotoIndex((prev) => (prev - 1 + photos.length) % photos.length);
  };

  const getPlaceTypeLabel = (type?: string) => {
    switch (type) {
      case 'fishing_spot': return 'Место для рыбалки';
      case 'resort': return 'База отдыха';
      case 'slip': return 'Слип';
      case 'marina': return 'Марина';
      default: return 'Место';
    }
  };

  const renderStars = (rating: number) => {
    return Array.from({ length: 5 }, (_, i) => (
      <Icon
        key={i}
        name="star"
        filled={i < rating}
        size="sm"
      />
    ));
  };

  if (loading) {
    return (
      <div className="screen">
        <div className="loading">Загрузка места...</div>
      </div>
    );
  }

  if (error || !place) {
    return (
      <div className="screen">
        <div className="error">
          <p>{error || 'Место не найдено'}</p>
          <button onClick={() => navigate(-1)} className="btn btn-primary">
            Назад
          </button>
        </div>
      </div>
    );
  }

  const photos = getPhotos();

  return (
    <div className="screen">
      <div className="place-detail">
        {/* Header */}
        <div className="place-header glass">
          <div className="user-info">
            <div className="user-avatar-clickable">
              <Avatar 
                src={place.user.photo_url} 
                size="xl"
                crownIconUrl={place.user.crown_icon_url}
                isPremium={place.user.is_premium}
                name={place.user.name}
              />
            </div>
            <div className="user-details">
              <div className="user-name-row">
                <span className="user-name">{place.user.name}</span>
              </div>
              {place.user.username && (
                <span className="user-username">@{place.user.username}</span>
              )}
            </div>
          </div>
          <span className="place-date">
            {new Date(place.created_at).toLocaleDateString()}
          </span>
        </div>

        {/* Photo Gallery */}
        {photos.length > 0 && (
          <div className="place-photo-gallery glass">
            <div className="photo-container">
              <img 
                src={photos[currentPhotoIndex]} 
                alt={place.title} 
                className="place-photo-main"
              />
              
              {photos.length > 1 && (
                <>
                  <button 
                    className="photo-nav photo-nav-prev"
                    onClick={prevPhoto}
                  >
                    <Icon name="chevron_left" />
                  </button>
                  <button 
                    className="photo-nav photo-nav-next"
                    onClick={nextPhoto}
                  >
                    <Icon name="chevron_right" />
                  </button>
                  
                  <div className="photo-indicators">
                    {photos.map((_, index) => (
                      <button
                        key={index}
                        className={`photo-indicator ${index === currentPhotoIndex ? 'active' : ''}`}
                        onClick={() => setCurrentPhotoIndex(index)}
                      />
                    ))}
                  </div>
                  
                  <div className="photo-counter">
                    {currentPhotoIndex + 1} / {photos.length}
                  </div>
                </>
              )}
            </div>
          </div>
        )}

        {/* Place Info */}
        <div className="place-info glass">
          <div className="place-title-section">
            <div className="title-row">
              <h1 className="place-title">{place.title}</h1>
              <button 
                className="report-button" 
                onClick={handleReport}
                title="Пожаловаться на место"
              >
                <Icon name="report" size="md" />
              </button>
            </div>
            <div className="place-type-badge">
              {getPlaceTypeLabel(place.place_type)}
            </div>
          </div>

          {place.description && (
            <div className="place-description">
              <p>{place.description}</p>
            </div>
          )}

          {/* Rating */}
          {place.rating && (
            <div className="place-rating">
              <div className="rating-stars">
                {renderStars(Math.round(place.rating))}
              </div>
              <span className="rating-value">{place.rating.toFixed(1)}</span>
              {place.reviews_count && (
                <span className="reviews-count">({place.reviews_count} отзывов)</span>
              )}
            </div>
          )}

          {/* Features */}
          <div className="place-features">
            <h3>Удобства</h3>
            <div className="features-grid">
              {place.has_parking && (
                <div className="feature-item">
                  <Icon name="local_parking" size="md" />
                  <span>Парковка</span>
                </div>
              )}
              {place.has_security && (
                <div className="feature-item">
                  <Icon name="security" size="md" />
                  <span>Охрана</span>
                </div>
              )}
              {place.amenities && place.amenities.map((amenity, index) => (
                <div key={index} className="feature-item">
                  <Icon name="check_circle" size="md" />
                  <span>{amenity}</span>
                </div>
              ))}
            </div>
          </div>

          {/* Coordinates */}
          <div className="place-coordinates">
            <div className="coordinate-item">
              <Icon name="location_on" size="md" />
              <div className="coordinate-content">
                <span className="coordinate-label">Координаты</span>
                <span className="coordinate-value">
                  {Number(place.lat).toFixed(6)}, {Number(place.lng).toFixed(6)}
                </span>
              </div>
            </div>
          </div>
        </div>

        {/* Location Map */}
        {place.lat && place.lng && (
          <div className="place-location glass">
            <div className="location-header">
              <h3>
                <Icon name="location_on" size="md" />
                Расположение
              </h3>
            </div>
            
            <CatchLocationMap 
              catchRecord={{
                id: place.id,
                user: place.user,
                lat: place.lat,
                lng: place.lng,
                species: place.title,
                weight: undefined,
                length: undefined,
                style: undefined,
                lure: undefined,
                tackle: undefined,
                notes: place.description,
                photos: photos,
                videos: [],
                main_photo: photos[0],
                main_video: undefined,
                media_count: photos.length,
                photo_url: photos[0],
                additional_photos: undefined,
                privacy: place.privacy,
                caught_at: place.created_at,
                likes_count: 0,
                comments_count: 0,
                liked_by_me: false,
                point: undefined,
                fish_species: undefined,
                fishing_method: undefined,
                fishing_location: undefined,
                created_at: place.created_at
              }}
              height="300px"
            />
          </div>
        )}

        {/* Contact Information */}
        {(place.contact_phone || place.contact_email || place.website) && (
          <div className="place-contacts glass">
            <h3>Контакты</h3>
            <div className="contacts-list">
              {place.contact_phone && (
                <div className="contact-item">
                  <Icon name="phone" size="md" />
                  <a href={`tel:${place.contact_phone}`}>{place.contact_phone}</a>
                </div>
              )}
              {place.contact_email && (
                <div className="contact-item">
                  <Icon name="email" size="md" />
                  <a href={`mailto:${place.contact_email}`}>{place.contact_email}</a>
                </div>
              )}
              {place.website && (
                <div className="contact-item">
                  <Icon name="language" size="md" />
                  <a href={place.website} target="_blank" rel="noopener noreferrer">
                    {place.website}
                  </a>
                </div>
              )}
            </div>
          </div>
        )}

        {/* Reviews */}
        {place.reviews && place.reviews.length > 0 && (
          <div className="place-reviews glass">
            <h3>Отзывы ({place.reviews.length})</h3>
            <div className="reviews-list">
              {place.reviews.map((review) => (
                <div key={review.id} className="review-item">
                  <div className="review-header">
                    <Avatar 
                      src={review.user.photo_url} 
                      size="lg"
                      name={review.user.name}
                    />
                    <div className="review-user-info">
                      <span className="review-user-name">{review.user.name}</span>
                      <div className="review-rating">
                        {renderStars(review.rating)}
                      </div>
                    </div>
                    <span className="review-date">
                      {new Date(review.created_at).toLocaleDateString()}
                    </span>
                  </div>
                  <p className="review-comment">{review.comment}</p>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Report Modal */}
        <ReportModal
          isOpen={showReportModal}
          onClose={() => setShowReportModal(false)}
          type="place"
          targetId={place.id}
          targetName={`Место: ${place.title}`}
        />
      </div>
    </div>
  );
};

export default PlaceDetailPage;
