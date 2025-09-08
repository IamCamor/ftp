import React, { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { MapContainer, TileLayer, Marker, Popup } from 'react-leaflet';
import L from '../utils/leafletLoader';
import PointPinCard from '../components/PointPinCard';
import Icon from '../components/Icon';
import { points, saveWeatherFav, isAuthed } from '../api';
import type { Point } from '../types';
import config from '../config';
import 'leaflet/dist/leaflet.css';

const MapScreen: React.FC = () => {
  const navigate = useNavigate();
  const [mapPoints, setMapPoints] = useState<Point[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedPoint, setSelectedPoint] = useState<Point | null>(null);
  const [showWeatherModal, setShowWeatherModal] = useState(false);
  const [clickedCoords, setClickedCoords] = useState<{ lat: number; lng: number } | null>(null);
  const mapRef = useRef<L.Map>(null);

  useEffect(() => {
    loadPoints();
  }, []);

  const loadPoints = async () => {
    try {
      setLoading(true);
      const data = await points({ limit: config.map.maxPoints });
      setMapPoints(data);
    } catch (error) {
      console.error('Failed to load points:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleMapClick = (e: L.LeafletMouseEvent) => {
    const { lat, lng } = e.latlng;
    setClickedCoords({ lat, lng });
    setShowWeatherModal(true);
  };

  const handleSaveWeatherFav = async () => {
    if (!clickedCoords) return;

    try {
      const label = prompt('Введите название места:');
      if (label) {
        await saveWeatherFav({
          lat: clickedCoords.lat,
          lng: clickedCoords.lng,
          label
        });
        alert('Место сохранено в избранные!');
      }
    } catch (error) {
      console.error('Failed to save weather fav:', error);
      alert('Не удалось сохранить место');
    } finally {
      setShowWeatherModal(false);
      setClickedCoords(null);
    }
  };

  const handleAuthRequired = () => {
    navigate(config.routes.auth.login);
  };

  const createCustomIcon = (point: Point) => {
    return L.divIcon({
      className: 'custom-marker',
      html: `<div class="marker-content">
        <div class="marker-icon">🎣</div>
        <div class="marker-title">${point.title}</div>
      </div>`,
      iconSize: [60, 40],
      iconAnchor: [30, 40],
    });
  };

  if (loading) {
    return (
      <div className="screen">
        <div className="loading">Загрузка карты...</div>
      </div>
    );
  }

  return (
    <div className="screen map-screen">
      <MapContainer
        center={config.map.defaultCenter}
        zoom={config.map.defaultZoom}
        className="map-container"
        ref={mapRef}
      >
        <TileLayer
          url={config.map.tiles.url}
          attribution={config.map.tiles.attribution}
        />
        
        {mapPoints.map((point) => (
          <Marker
            key={point.id}
            position={[point.lat, point.lng]}
            icon={createCustomIcon(point)}
            eventHandlers={{
              click: () => setSelectedPoint(point),
            }}
          >
            <Popup>
              <PointPinCard point={point} onClose={() => setSelectedPoint(null)} />
            </Popup>
          </Marker>
        ))}
      </MapContainer>

      {selectedPoint && (
        <div className="selected-point-overlay">
          <PointPinCard 
            point={selectedPoint} 
            onClose={() => setSelectedPoint(null)} 
          />
        </div>
      )}

      <div className="map-controls">
        <button
          className="map-button"
          onClick={() => navigate(config.routes.addCatch)}
          title="Добавить улов"
        >
          <Icon name="add" size={24} />
        </button>
        
        <button
          className="map-button"
          onClick={() => navigate(config.routes.addPlace)}
          title="Добавить точку"
        >
          <Icon name="place" size={24} />
        </button>
      </div>

      {showWeatherModal && (
        <div className="modal-overlay">
          <div className="weather-modal glass">
            <h3>Сохранить место для погоды</h3>
            <p>Хотите сохранить это место для отслеживания погоды?</p>
            <div className="modal-actions">
              <button 
                className="btn btn-secondary"
                onClick={() => setShowWeatherModal(false)}
              >
                Отмена
              </button>
              {isAuthed() ? (
                <button 
                  className="btn btn-primary"
                  onClick={handleSaveWeatherFav}
                >
                  Сохранить
                </button>
              ) : (
                <button 
                  className="btn btn-primary"
                  onClick={handleAuthRequired}
                >
                  Войти для сохранения
                </button>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default MapScreen;

