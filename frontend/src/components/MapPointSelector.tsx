import React, { useState, useEffect } from 'react';
import { MapContainer, TileLayer, Marker, useMapEvents } from 'react-leaflet';
import L from 'leaflet';
import Icon from './Icon';

interface MapPointSelectorProps {
  onPointSelect: (lat: number, lng: number) => void;
  initialPosition?: { lat: number; lng: number };
  onClose: () => void;
}

// Компонент для обработки кликов на карте
const MapClickHandler: React.FC<{ onPointSelect: (lat: number, lng: number) => void }> = ({ onPointSelect }) => {
  useMapEvents({
    click: (e) => {
      const { lat, lng } = e.latlng;
      onPointSelect(lat, lng);
    }
  });
  return null;
};

const MapPointSelector: React.FC<MapPointSelectorProps> = ({ onPointSelect, initialPosition, onClose }) => {
  const [selectedPoint, setSelectedPoint] = useState<{ lat: number; lng: number } | null>(
    initialPosition || null
  );
  const [currentLocation, setCurrentLocation] = useState<{ lat: number; lng: number } | null>(null);

  // Получаем текущее местоположение
  useEffect(() => {
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
          let errorMessage = 'Не удалось получить текущее местоположение';
          
          switch (error.code) {
            case error.PERMISSION_DENIED:
              errorMessage = 'Доступ к геолокации запрещен. Разрешите доступ в настройках браузера.';
              break;
            case error.POSITION_UNAVAILABLE:
              errorMessage = 'Информация о местоположении недоступна.';
              break;
            case error.TIMEOUT:
              errorMessage = 'Время ожидания получения местоположения истекло.';
              break;
            default:
              errorMessage = 'Произошла неизвестная ошибка при получении местоположения.';
              break;
          }
          
          console.warn('Geolocation error:', errorMessage);
        }
      );
    }
  }, []);

  const handlePointSelect = (lat: number, lng: number) => {
    setSelectedPoint({ lat, lng });
  };

  const handleConfirm = () => {
    if (selectedPoint) {
      onPointSelect(selectedPoint.lat, selectedPoint.lng);
      onClose();
    }
  };

  const handleUseCurrentLocation = () => {
    if (currentLocation) {
      setSelectedPoint(currentLocation);
    }
  };

  // Создаем кастомную иконку для маркера
  const createCustomIcon = () => {
    return L.divIcon({
      className: 'custom-marker',
      html: `
        <div style="
          background: var(--accent-primary);
          width: 24px;
          height: 24px;
          border-radius: 50%;
          border: 3px solid white;
          box-shadow: 0 2px 8px rgba(0,0,0,0.3);
          display: flex;
          align-items: center;
          justify-content: center;
        ">
          <span style="color: white; font-size: 12px; font-weight: bold;">📍</span>
        </div>
      `,
      iconSize: [24, 24],
      iconAnchor: [12, 12]
    });
  };

  const defaultCenter = currentLocation || { lat: 55.7558, lng: 37.6176 };

  return (
    <div className="map-point-selector">
      <div className="map-selector-header">
        <h3>Выберите место улова</h3>
        <button className="close-button" onClick={onClose}>
          <Icon name="close" size="md" />
        </button>
      </div>

      <div className="map-selector-actions">
        <button
          className="btn btn-secondary"
          onClick={handleUseCurrentLocation}
          disabled={!currentLocation}
        >
          <Icon name="my_location" size="md" />
          Использовать текущее местоположение
        </button>
        {selectedPoint && (
          <div className="selected-coordinates">
            <Icon name="place" size="sm" />
            <span>
              {selectedPoint.lat.toFixed(6)}, {selectedPoint.lng.toFixed(6)}
            </span>
          </div>
        )}
      </div>

      <div className="map-container">
        <MapContainer
          center={defaultCenter}
          zoom={13}
          style={{ height: '400px', width: '100%' }}
        >
          <TileLayer
            url="https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}"
            attribution='&copy; <a href="https://www.esri.com/">Esri</a> — Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
          />
          
          <MapClickHandler onPointSelect={handlePointSelect} />
          
          {selectedPoint && (
            <Marker
              position={[selectedPoint.lat, selectedPoint.lng]}
              icon={createCustomIcon()}
            />
          )}
        </MapContainer>
      </div>

      <div className="map-selector-footer">
        <button className="btn btn-secondary" onClick={onClose}>
          Отмена
        </button>
        <button
          className="btn btn-primary"
          onClick={handleConfirm}
          disabled={!selectedPoint}
        >
          <Icon name="check" size="md" />
          Выбрать место
        </button>
      </div>
    </div>
  );
};

export default MapPointSelector;

