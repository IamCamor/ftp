import React, { useEffect, useRef, useState } from 'react';
import { Track } from '../types';

interface TrackMapProps {
  track: Track;
  className?: string;
}

const TrackMap: React.FC<TrackMapProps> = ({ track, className = '' }) => {
  const mapRef = useRef<HTMLDivElement>(null);
  const [map, setMap] = useState<any>(null);
  const [markers, setMarkers] = useState<any[]>([]);
  const [polyline, setPolyline] = useState<any>(null);

  useEffect(() => {
    if (!mapRef.current || map) return;

    // Инициализация карты Leaflet
    const initMap = () => {
      if (typeof window !== 'undefined' && window.L) {
        const mapInstance = window.L.map(mapRef.current).setView(
          [track.track_points[0]?.lat || 55.7558, track.track_points[0]?.lng || 37.6176], 
          13
        );

        // Добавляем слой OpenStreetMap
        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '© OpenStreetMap contributors'
        }).addTo(mapInstance);

        setMap(mapInstance);
      }
    };

    // Загружаем Leaflet CSS и JS если не загружены
    if (typeof window !== 'undefined' && !window.L) {
      // Загружаем CSS
      const cssLink = document.createElement('link');
      cssLink.rel = 'stylesheet';
      cssLink.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
      cssLink.integrity = 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=';
      cssLink.crossOrigin = '';
      document.head.appendChild(cssLink);

      // Загружаем JS
      const script = document.createElement('script');
      script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
      script.integrity = 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=';
      script.crossOrigin = '';
      script.async = true;
      script.onload = initMap;
      document.head.appendChild(script);
    } else {
      initMap();
    }
  }, [track.track_points]);

  useEffect(() => {
    if (!map || !track.track_points.length) return;

    // Очищаем предыдущие маркеры и линии
    markers.forEach(marker => map.removeLayer(marker));
    if (polyline) map.removeLayer(polyline);

    const newMarkers: any[] = [];
    const path: any[] = [];

    // Добавляем точки трека
    track.track_points.forEach((point, index) => {
      const isStart = index === 0;
      const isEnd = index === track.track_points.length - 1;
      
      let icon;
      if (isStart) {
        icon = window.L.divIcon({
          className: 'custom-div-icon',
          html: `<div style="background-color: #4CAF50; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
          iconSize: [12, 12],
          iconAnchor: [6, 6]
        });
      } else if (isEnd) {
        icon = window.L.divIcon({
          className: 'custom-div-icon',
          html: `<div style="background-color: #F44336; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
          iconSize: [12, 12],
          iconAnchor: [6, 6]
        });
      } else {
        icon = window.L.divIcon({
          className: 'custom-div-icon',
          html: `<div style="background-color: #2196F3; width: 8px; height: 8px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
          iconSize: [8, 8],
          iconAnchor: [4, 4]
        });
      }

      const marker = window.L.marker([point.lat, point.lng], { icon })
        .addTo(map)
        .bindPopup(`
          <div style="padding: 8px; min-width: 200px;">
            <h3 style="margin: 0 0 8px 0; font-weight: 600; color: #333;">Точка ${index + 1}</h3>
            <p style="margin: 0 0 8px 0; font-size: 14px; color: #666;">
              Время: ${new Date(point.timestamp).toLocaleString('ru-RU')}
            </p>
            ${point.smartwatch_data ? `
              <div style="margin-top: 8px; font-size: 12px; color: #666;">
                <p style="margin: 2px 0;">Пульс: ${point.smartwatch_data.heart_rate} уд/мин</p>
                <p style="margin: 2px 0;">Шаги: ${point.smartwatch_data.steps}</p>
                <p style="margin: 2px 0;">Калории: ${point.smartwatch_data.calories}</p>
              </div>
            ` : ''}
          </div>
        `);

      newMarkers.push(marker);
      path.push([point.lat, point.lng]);
    });

    // Добавляем маркеры уловов
    track.catches.forEach((catchRecord) => {
      if (catchRecord.lat && catchRecord.lng) {
        const catchIcon = window.L.divIcon({
          className: 'custom-div-icon',
          html: `<div style="background-color: #FF5722; width: 16px; height: 16px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; font-size: 10px; color: white;">🎣</div>`,
          iconSize: [16, 16],
          iconAnchor: [8, 8]
        });

        const catchMarker = window.L.marker([catchRecord.lat, catchRecord.lng], { icon: catchIcon })
          .addTo(map)
          .bindPopup(`
            <div style="padding: 8px; min-width: 200px;">
              <h3 style="margin: 0 0 8px 0; font-weight: 600; color: #FF5722;">🎣 ${catchRecord.species}</h3>
              <p style="margin: 0 0 8px 0; font-size: 14px; color: #666;">
                Время: ${catchRecord.caught_at ? new Date(catchRecord.caught_at).toLocaleString('ru-RU') : 'Не указано'}
              </p>
              <div style="margin-top: 8px; font-size: 12px; color: #666;">
                <p style="margin: 2px 0;">Вес: ${catchRecord.weight} кг</p>
                <p style="margin: 2px 0;">Длина: ${catchRecord.length} см</p>
                ${catchRecord.style ? `<p style="margin: 2px 0;">Стиль: ${catchRecord.style}</p>` : ''}
                ${catchRecord.lure ? `<p style="margin: 2px 0;">Приманка: ${catchRecord.lure}</p>` : ''}
                ${catchRecord.tackle ? `<p style="margin: 2px 0;">Снасть: ${catchRecord.tackle}</p>` : ''}
              </div>
            </div>
          `);

        newMarkers.push(catchMarker);
      }
    });

    // Создаем линию трека
    if (path.length > 1) {
      const trackPolyline = window.L.polyline(path, {
        color: '#4CAF50',
        weight: 4,
        opacity: 0.8,
        smoothFactor: 1
      }).addTo(map);
      setPolyline(trackPolyline);
    }

    setMarkers(newMarkers);

    // Подгоняем карту под все маркеры
    if (path.length > 0) {
      const group = new window.L.featureGroup(newMarkers);
      map.fitBounds(group.getBounds().pad(0.1));
    }
  }, [map, track.track_points, track.catches]);

  return (
    <div className={`relative ${className}`}>
      <div ref={mapRef} className="w-full h-full rounded-lg" style={{ minHeight: '400px' }} />
      
      {/* Легенда */}
      <div className="absolute top-4 right-4 bg-white rounded-lg shadow-lg p-3 text-sm z-[1000]">
        <div className="space-y-2">
          <div className="flex items-center space-x-2">
            <div className="w-3 h-3 bg-green-500 rounded-full border-2 border-white shadow-sm"></div>
            <span>Начало трека</span>
          </div>
          <div className="flex items-center space-x-2">
            <div className="w-3 h-3 bg-red-500 rounded-full border-2 border-white shadow-sm"></div>
            <span>Конец трека</span>
          </div>
          <div className="flex items-center space-x-2">
            <div className="w-2 h-2 bg-blue-500 rounded-full border-2 border-white shadow-sm"></div>
            <span>Точки трека</span>
          </div>
          <div className="flex items-center space-x-2">
            <div className="w-4 h-4 bg-orange-500 rounded-full border-2 border-white shadow-sm flex items-center justify-center text-white text-xs">🎣</div>
            <span>Уловы</span>
          </div>
          <div className="flex items-center space-x-2">
            <div className="w-4 h-1 bg-green-500 rounded"></div>
            <span>Путь трека</span>
          </div>
        </div>
      </div>

      {/* Статистика трека на карте */}
      <div className="absolute bottom-4 left-4 bg-white rounded-lg shadow-lg p-3 text-sm z-[1000]">
        <div className="space-y-1">
          <div className="font-semibold text-gray-800">Статистика трека</div>
          <div className="text-gray-600">Точек: {track.track_points.length}</div>
          <div className="text-gray-600">Уловов: {track.catches.length}</div>
          <div className="text-gray-600">Дистанция: {track.total_distance} км</div>
          <div className="text-gray-600">
            Длительность: {track.duration_minutes ? `${Math.floor(track.duration_minutes / 60)}ч ${track.duration_minutes % 60}м` : 'В процессе'}
          </div>
        </div>
      </div>
    </div>
  );
};

export default TrackMap;
