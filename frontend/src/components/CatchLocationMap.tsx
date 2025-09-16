import React, { useEffect, useRef } from 'react';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';
import type { CatchRecord } from '../types';

interface CatchLocationMapProps {
  catchRecord: CatchRecord;
  height?: string;
  showControls?: boolean;
  onMapClick?: () => void;
}

const CatchLocationMap: React.FC<CatchLocationMapProps> = ({
  catchRecord,
  height = '300px',
  showControls = true,
  onMapClick
}) => {
  const mapRef = useRef<HTMLDivElement>(null);
  const mapInstanceRef = useRef<L.Map | null>(null);
  const markerRef = useRef<L.Marker | null>(null);

  useEffect(() => {
    if (!mapRef.current || !catchRecord.lat || !catchRecord.lng) return;

    // Инициализация карты
    const map = L.map(mapRef.current, {
      center: [catchRecord.lat, catchRecord.lng],
      zoom: 13,
      zoomControl: showControls,
      attributionControl: true,
      dragging: showControls,
      touchZoom: showControls,
      doubleClickZoom: showControls,
      scrollWheelZoom: showControls,
      boxZoom: showControls,
      keyboard: showControls
    });

    // Добавляем тайлы
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Создаем кастомную иконку для улова
    const catchIcon = L.icon({
      iconUrl: '/icons/fish-pin.svg', // Используем существующую иконку рыбы
      iconSize: [32, 32],
      iconAnchor: [16, 32],
      popupAnchor: [0, -32],
      className: 'catch-marker-icon'
    });

    // Добавляем маркер улова
    const marker = L.marker([catchRecord.lat, catchRecord.lng], {
      icon: catchIcon
    }).addTo(map);

    // Создаем попап с информацией об улове
    const popupContent = `
      <div class="catch-popup">
        <h4>${catchRecord.species || 'Улов'}</h4>
        <p><strong>Автор:</strong> ${catchRecord.user.name}</p>
        ${catchRecord.weight ? `<p><strong>Вес:</strong> ${catchRecord.weight} кг</p>` : ''}
        ${catchRecord.length ? `<p><strong>Длина:</strong> ${catchRecord.length} см</p>` : ''}
        ${catchRecord.caught_at ? `<p><strong>Время:</strong> ${new Date(catchRecord.caught_at).toLocaleString()}</p>` : ''}
      </div>
    `;

    marker.bindPopup(popupContent);

    // Обработчик клика по карте
    if (onMapClick) {
      map.on('click', onMapClick);
    }

    // Сохраняем ссылки
    mapInstanceRef.current = map;
    markerRef.current = marker;

    // Очистка при размонтировании
    return () => {
      if (mapInstanceRef.current) {
        mapInstanceRef.current.remove();
        mapInstanceRef.current = null;
      }
    };
  }, [catchRecord, showControls, onMapClick]);

  return (
    <div className="catch-location-map">
      <div 
        ref={mapRef} 
        style={{ height, width: '100%' }}
        className="map-container"
      />
    </div>
  );
};

export default CatchLocationMap;
