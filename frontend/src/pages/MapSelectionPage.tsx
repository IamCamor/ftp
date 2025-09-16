import React, { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { MapContainer, TileLayer, Marker, useMapEvents } from 'react-leaflet';
import L from 'leaflet';
import Icon from '../components/Icon';
import { reverseGeocode } from '../api';

// Компонент для обработки кликов на карте
const MapClickHandler: React.FC<{ 
  onPointSelect: (lat: number, lng: number) => void;
  onLocationName: (name: string) => void;
}> = ({ onPointSelect, onLocationName }) => {
  useMapEvents({
    click: async (e) => {
      const { lat, lng } = e.latlng;
      onPointSelect(lat, lng);
      
      // Получаем название места по координатам
      try {
        const result = await reverseGeocode(lat, lng);
        onLocationName(result.data.name);
      } catch (err) {
        console.error('Failed to get location name:', err);
        onLocationName(`Место ${lat.toFixed(4)}, ${lng.toFixed(4)}`);
      }
    }
  });
  return null;
};

const MapSelectionPage: React.FC = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const [selectedPoint, setSelectedPoint] = useState<{ lat: number; lng: number } | null>(null);
  const [locationName, setLocationName] = useState<string>('');
  const [loading, setLoading] = useState(false);
  const [currentLocation, setCurrentLocation] = useState<{ lat: number; lng: number } | null>(null);

  // Получаем координаты из state (если переходим с другой страницы)
  const initialPosition = location.state?.coordinates || { lat: 55.7558, lng: 37.6176 };

  useEffect(() => {
    // Получаем текущее местоположение
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          setCurrentLocation({
            lat: position.coords.latitude,
            lng: position.coords.longitude
          });
        },
        (error) => {
          console.error('Error getting location:', error);
        }
      );
    }
  }, []);

  const handlePointSelect = (lat: number, lng: number) => {
    setSelectedPoint({ lat, lng });
  };

  const handleLocationName = (name: string) => {
    setLocationName(name);
  };

  const handleConfirm = () => {
    if (selectedPoint) {
      // Передаем координаты и название обратно
      navigate('/add-point', {
        state: {
          coordinates: selectedPoint,
          locationName: locationName
        }
      });
    }
  };

  const handleGetCurrentLocation = () => {
    if (navigator.geolocation) {
      setLoading(true);
      navigator.geolocation.getCurrentPosition(
        async (position) => {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;
          
          setSelectedPoint({ lat, lng });
          
          // Получаем название места
          try {
            const result = await reverseGeocode(lat, lng);
            setLocationName(result.data.name);
          } catch (err) {
            console.error('Failed to get location name:', err);
            setLocationName(`Текущее местоположение`);
          }
          
          setLoading(false);
        },
        (error) => {
          console.error('Error getting location:', error);
          setLoading(false);
          alert('Не удалось получить текущее местоположение');
        }
      );
    }
  };

  const handleBack = () => {
    navigate(-1);
  };

  return (
    <div className="screen">
      <div className="map-selection-header">
        <button className="back-button" onClick={handleBack}>
          <Icon name="arrow_back" size="md" />
        </button>
        <h1>Выберите место на карте</h1>
        <button 
          className="btn btn-primary btn-sm"
          onClick={handleGetCurrentLocation}
          disabled={loading}
        >
          <Icon name="my_location" size="sm" />
          {loading ? 'Определяем...' : 'Мое местоположение'}
        </button>
      </div>

      <div className="map-selection-content">
        <div className="map-container">
          <MapContainer
            center={[initialPosition.lat, initialPosition.lng]}
            zoom={13}
            style={{ height: '100%', width: '100%' }}
          >
            <TileLayer
              url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
              attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            />
            
            <MapClickHandler 
              onPointSelect={handlePointSelect}
              onLocationName={handleLocationName}
            />
            
            {selectedPoint && (
              <Marker
                position={[selectedPoint.lat, selectedPoint.lng]}
                icon={L.divIcon({
                  className: 'custom-marker',
                  html: '<div class="marker-pin"></div>',
                  iconSize: [20, 20],
                  iconAnchor: [10, 20]
                })}
              />
            )}
            
            {currentLocation && (
              <Marker
                position={[currentLocation.lat, currentLocation.lng]}
                icon={L.divIcon({
                  className: 'current-location-marker',
                  html: '<div class="current-location-pin"></div>',
                  iconSize: [16, 16],
                  iconAnchor: [8, 16]
                })}
              />
            )}
          </MapContainer>
        </div>

        <div className="map-selection-info">
          {selectedPoint ? (
            <div className="selected-location">
              <div className="location-details">
                <Icon name="place" size="md" />
                <div>
                  <h3>{locationName || 'Выбранное место'}</h3>
                  <p>
                    {selectedPoint.lat.toFixed(6)}, {selectedPoint.lng.toFixed(6)}
                  </p>
                </div>
              </div>
              <button 
                className="btn btn-primary"
                onClick={handleConfirm}
              >
                <Icon name="check" size="sm" />
                Выбрать это место
              </button>
            </div>
          ) : (
            <div className="selection-hint">
              <Icon name="touch_app" size="lg" />
              <p>Коснитесь карты, чтобы выбрать место</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default MapSelectionPage;
