import React, { useState, useEffect, useRef } from 'react';
import Icon from './Icon';
import config from '../config';

interface WeatherMapSelectorProps {
  onCoordinatesSelect: (lat: number, lng: number, locationName: string) => void;
  onClose: () => void;
  initialCoordinates?: { lat: number; lng: number };
}

const WeatherMapSelector: React.FC<WeatherMapSelectorProps> = ({
  onCoordinatesSelect,
  onClose,
  initialCoordinates = config.map.defaultCenter
}) => {
  const [selectedCoordinates, setSelectedCoordinates] = useState(initialCoordinates);
  const [locationName, setLocationName] = useState<string>('');
  const [loading, setLoading] = useState(false);
  const [mapLoaded, setMapLoaded] = useState(false);
  const mapRef = useRef<HTMLDivElement>(null);
  const mapInstanceRef = useRef<any>(null);
  const markerRef = useRef<any>(null);

  useEffect(() => {
    loadMap();
    getLocationName(initialCoordinates.lat, initialCoordinates.lng);

    // Cleanup function
    return () => {
      if (mapInstanceRef.current) {
        mapInstanceRef.current.remove();
        mapInstanceRef.current = null;
        markerRef.current = null;
      }
    };
  }, []);

  const loadMap = async () => {
    if (typeof window === 'undefined' || !config.map.enabled) {
      setMapLoaded(true);
      return;
    }

    try {
      // Динамически импортируем Leaflet
      const L = await import('leaflet');
      
      // Импортируем CSS для Leaflet
      await import('leaflet/dist/leaflet.css');

      if (mapRef.current && !mapInstanceRef.current) {
        // Создаем карту с настройками из конфига
        const map = L.map(mapRef.current, {
          center: [initialCoordinates.lat, initialCoordinates.lng],
          zoom: config.map.defaultZoom,
          zoomControl: config.map.features.zoomControls,
          maxZoom: config.map.maxZoom,
          minZoom: config.map.minZoom
        });

        // Добавляем слой карты в зависимости от провайдера
        if (config.map.provider === 'osm') {
          L.tileLayer(config.map.tiles.osm, {
            attribution: config.map.tiles.attribution,
            maxZoom: config.map.maxZoom
          }).addTo(map);
        }

        // Создаем маркер
        const marker = L.marker([initialCoordinates.lat, initialCoordinates.lng], {
          draggable: config.map.features.dragMarker
        }).addTo(map);

        // Обработчик клика по карте (если включен)
        if (config.map.features.clickToSelect) {
          map.on('click', (e: any) => {
            const { lat, lng } = e.latlng;
            setSelectedCoordinates({ lat, lng });
            marker.setLatLng([lat, lng]);
            getLocationName(lat, lng);
          });
        }

        // Обработчик перетаскивания маркера (если включен)
        if (config.map.features.dragMarker) {
          marker.on('dragend', (e: any) => {
            const { lat, lng } = e.target.getLatLng();
            setSelectedCoordinates({ lat, lng });
            getLocationName(lat, lng);
          });
        }

        mapInstanceRef.current = map;
        markerRef.current = marker;
        setMapLoaded(true);
      }
    } catch (error) {
      console.error('Failed to load Leaflet map:', error);
      setMapLoaded(true); // Fallback to simple map
    }
  };

  const getLocationName = async (lat: number, lng: number) => {
    setLoading(true);
    try {
      // Пока API geocoding не реализован, используем координаты
      setLocationName(`Место ${lat.toFixed(4)}, ${lng.toFixed(4)}`);
    } catch (error) {
      console.error('Failed to get location name:', error);
      setLocationName(`Место ${lat.toFixed(4)}, ${lng.toFixed(4)}`);
    } finally {
      setLoading(false);
    }
  };

  const handleGetCurrentLocation = () => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;
          setSelectedCoordinates({ lat, lng });
          
          // Обновляем карту и маркер
          if (mapInstanceRef.current && markerRef.current) {
            mapInstanceRef.current.setView([lat, lng], 15);
            markerRef.current.setLatLng([lat, lng]);
          }
          
          getLocationName(lat, lng);
        },
        (error) => {
          console.error('Error getting location:', error);
        }
      );
    }
  };

  const handleConfirm = () => {
    onCoordinatesSelect(selectedCoordinates.lat, selectedCoordinates.lng, locationName);
  };

  return (
    <div className="weather-map-selector">
      <div className="map-selector-header">
        <h3>Выберите место на карте</h3>
        <button className="action-button" onClick={onClose}>
          <Icon name="close" size="sm" />
        </button>
      </div>

      <div className="map-selector-content">
        <div className="map-container">
          {mapLoaded ? (
            <div ref={mapRef} className="weather-map" />
          ) : (
            <div className="map-loading">
              <Icon name="map" size="xl" />
              <p>Загрузка карты...</p>
            </div>
          )}
        </div>

        <div className="coordinates-panel">
          <div className="coordinates-display">
            <div className="coords-header">
              <Icon name="place" size="md" />
              <h4>Выбранные координаты</h4>
            </div>
            
            <div className="coords-info">
              <div className="coords-values">
                <span className="coord-label">Широта:</span>
                <span className="coord-value">{selectedCoordinates.lat.toFixed(6)}</span>
              </div>
              <div className="coords-values">
                <span className="coord-label">Долгота:</span>
                <span className="coord-value">{selectedCoordinates.lng.toFixed(6)}</span>
              </div>
            </div>

            <div className="location-name">
              <Icon name="location_on" size="sm" />
              <span className="location-text">
                {loading ? 'Определяем название...' : locationName}
              </span>
            </div>
          </div>

          {config.map.features.currentLocation && (
            <div className="map-actions">
              <button
                type="button"
                className="btn btn-secondary"
                onClick={handleGetCurrentLocation}
              >
                <Icon name="my_location" size="sm" />
                Мое местоположение
              </button>
            </div>
          )}

          <div className="map-selector-actions">
            <button
              type="button"
              className="btn btn-secondary"
              onClick={onClose}
            >
              Отмена
            </button>
            <button
              type="button"
              className="btn btn-primary"
              onClick={handleConfirm}
            >
              <Icon name="check" size="sm" />
              Выбрать это место
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default WeatherMapSelector;
