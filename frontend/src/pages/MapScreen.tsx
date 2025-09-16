import React, { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { MapContainer, TileLayer, Marker } from 'react-leaflet';
import MarkerClusterGroup from 'react-leaflet-cluster';
import L from '../utils/leafletLoader';
import PointPinCard from '../components/PointPinCard';
import MapFilters from '../components/MapFilters';
import WeatherPointModal from '../components/WeatherPointModal';
import Icon from '../components/Icon';
import { points, saveWeatherFav, isAuthed } from '../api';
import type { Point } from '../types';
import config from '../config';
import 'leaflet/dist/leaflet.css';

const MapScreen: React.FC = () => {
  const navigate = useNavigate();
  const [mapPoints, setMapPoints] = useState<Point[]>([]);
  const [selectedPoint, setSelectedPoint] = useState<Point | null>(null);
  const [showWeatherModal, setShowWeatherModal] = useState(false);
  const [clickedCoords, setClickedCoords] = useState<{ lat: number; lng: number } | null>(null);
  const [mapType, setMapType] = useState<'satellite' | 'street'>('satellite');
  const [showFilters, setShowFilters] = useState(false);
  const [selectedSpecies, setSelectedSpecies] = useState<string[]>([]);
  const [selectedPointTypes, setSelectedPointTypes] = useState<string[]>([]);
  const mapRef = useRef<L.Map>(null);

  useEffect(() => {
    loadPoints();
  }, []);

  useEffect(() => {
    if (mapRef.current) {
      const map = mapRef.current;
      map.on('click', handleMapClick);
      
      return () => {
        map.off('click', handleMapClick);
      };
    }
  }, []);

  const loadPoints = async () => {
    try {
      const data = await points({ 
        limit: config.map.maxPoints,
        includeCatches: true 
      });
      setMapPoints(data);
    } catch (error) {
      console.error('Failed to load points:', error);
    }
  };

  const handleMapClick = (e: L.LeafletMouseEvent) => {
    const { lat, lng } = e.latlng;
    setClickedCoords({ lat, lng });
  };

  const handleZoomIn = () => {
    if (mapRef.current) {
      mapRef.current.zoomIn();
    }
  };

  const handleZoomOut = () => {
    if (mapRef.current) {
      mapRef.current.zoomOut();
    }
  };

  const toggleMapType = () => {
    setMapType(prev => prev === 'satellite' ? 'street' : 'satellite');
  };

  const toggleFilters = () => {
    setShowFilters(prev => !prev);
  };

  const clearFilters = () => {
    setSelectedSpecies([]);
    setSelectedPointTypes([]);
  };

  // Получаем уникальные виды рыб и типы мест из данных
  const fishSpecies = Array.from(new Set(
    mapPoints
      .filter(point => point.species) // Только точки с указанным видом рыбы
      .map(point => point.species!)
      .filter(Boolean)
  ));
  
  const pointTypes = ['Место для рыбалки', 'Место улова', 'Погодная точка'];

  // Фильтруем точки на основе выбранных фильтров
  const filteredPoints = mapPoints.filter(point => {
    // Фильтр по видам рыб
    const speciesMatch = selectedSpecies.length === 0 || 
      (point.species && selectedSpecies.includes(point.species));
    
    // Фильтр по типу места (определяем по наличию определенных полей)
    let pointType = 'Место для рыбалки'; // По умолчанию
    if (point.species) {
      pointType = 'Место улова';
    }
    if (point.weather_data) {
      pointType = 'Погодная точка';
    }
    
    const typeMatch = selectedPointTypes.length === 0 || selectedPointTypes.includes(pointType);
    
    return speciesMatch && typeMatch;
  });

  const handleSaveWeatherFav = async (label: string) => {
    if (!clickedCoords) return;

    try {
      await saveWeatherFav({
        lat: clickedCoords.lat,
        lng: clickedCoords.lng,
        label
      });
      alert('Место сохранено в избранные!');
      setShowWeatherModal(false);
      setClickedCoords(null);
    } catch (error) {
      console.error('Failed to save weather fav:', error);
      alert('Не удалось сохранить место');
    }
  };

  const handleSaveWeatherClick = () => {
    setShowWeatherModal(true);
  };

  const handleAuthRequired = () => {
    navigate(config.routes.auth.login);
  };

  const createCustomIcon = (point: Point) => {
    // Определяем тип иконки на основе данных точки
    let iconType = 'point';
    if (point.type === 'catch' || point.species) {
      iconType = 'catch';
    }
    
    const iconConfig = config.map.icons[iconType as keyof typeof config.map.icons];
    
    return L.icon({
      iconUrl: iconConfig.url,
      iconSize: iconConfig.size as [number, number],
      iconAnchor: iconConfig.anchor as [number, number],
      popupAnchor: iconConfig.popupAnchor as [number, number],
      className: `custom-marker custom-marker-${iconType}`
    });
  };

  return (
    <div className="screen map-screen">
      <MapContainer
        center={config.map.defaultCenter}
        zoom={config.map.defaultZoom}
        className="map-container"
        ref={mapRef}
      >
        <TileLayer
          url={mapType === 'satellite' 
            ? 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'
            : 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
          }
          attribution={mapType === 'satellite'
            ? '&copy; <a href="https://www.esri.com/">Esri</a> — Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
            : '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
          }
        />
        
        <MarkerClusterGroup
          chunkedLoading
          iconCreateFunction={(cluster: any) => {
            const count = cluster.getChildCount();
            return L.divIcon({
              html: `<div class="cluster-marker">
                <div class="cluster-count">${count}</div>
              </div>`,
              className: 'custom-cluster',
              iconSize: [40, 40],
              iconAnchor: [20, 20]
            });
          }}
        >
          {filteredPoints.map((point) => (
            <Marker
              key={point.id}
              position={[point.lat, point.lng]}
              icon={createCustomIcon(point)}
              eventHandlers={{
                click: () => setSelectedPoint(point),
              }}
            />
          ))}
        </MarkerClusterGroup>
      </MapContainer>

      {selectedPoint && (
        <div className="selected-point-overlay">
          <PointPinCard 
            point={selectedPoint} 
            onClose={() => setSelectedPoint(null)} 
          />
        </div>
      )}

      <div className="map-zoom-controls">
        <button
          className="zoom-button"
          onClick={handleZoomIn}
          title="Увеличить"
        >
          <Icon name="add" size="md" />
        </button>
        
        <button
          className="zoom-button"
          onClick={handleZoomOut}
          title="Уменьшить"
        >
          <Icon name="remove" size="md" />
        </button>
        
        <button
          className="zoom-button"
          onClick={toggleMapType}
          title={mapType === 'satellite' ? 'Переключить на схему' : 'Переключить на спутник'}
        >
          <Icon name={mapType === 'satellite' ? 'map' : 'satellite_alt'} size="md" />
        </button>
        
        <button
          className="zoom-button"
          onClick={toggleFilters}
          title="Фильтры"
        >
          <Icon name="filter_list" size="md" />
        </button>
      </div>


      {showFilters && (
        <MapFilters
          fishSpecies={fishSpecies}
          selectedSpecies={selectedSpecies}
          pointTypes={pointTypes}
          selectedPointTypes={selectedPointTypes}
          onSpeciesChange={setSelectedSpecies}
          onPointTypesChange={setSelectedPointTypes}
          onClearFilters={clearFilters}
          onClose={() => setShowFilters(false)}
        />
      )}

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
                  onClick={handleSaveWeatherClick}
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

      <WeatherPointModal
        isOpen={showWeatherModal}
        onClose={() => {
          setShowWeatherModal(false);
          setClickedCoords(null);
        }}
        onSave={handleSaveWeatherFav}
        coordinates={clickedCoords}
      />
    </div>
  );
};

export default MapScreen;

