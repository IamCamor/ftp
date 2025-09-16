import { useState, useEffect, useCallback, useRef } from 'react';
import { useGeolocationWatch } from './useGeolocation';
import { updateTrack } from '../api';

interface TrackLocationOptions {
  trackId: number;
  interval: number; // интервал в миллисекундах
  enabled: boolean;
  onLocationUpdate?: (lat: number, lng: number) => void;
  onError?: (error: string) => void;
}

interface TrackLocationState {
  isTracking: boolean;
  lastUpdate: Date | null;
  locationCount: number;
  error: string | null;
}

export const useTrackLocation = (options: TrackLocationOptions) => {
  const { trackId, interval, enabled, onLocationUpdate, onError } = options;
  
  const [state, setState] = useState<TrackLocationState>({
    isTracking: false,
    lastUpdate: null,
    locationCount: 0,
    error: null
  });

  const intervalRef = useRef<number | null>(null);
  const lastLocationRef = useRef<{ lat: number; lng: number } | null>(null);

  const {
    latitude,
    longitude,
    accuracy,
    error: geoError,
    startWatching,
    stopWatching,
    isWatching
  } = useGeolocationWatch({
    enableHighAccuracy: true,
    timeout: 10000,
    maximumAge: 30000
  });

  const updateTrackLocation = useCallback(async (lat: number, lng: number) => {
    try {
      // Проверяем, изменилось ли местоположение значительно
      if (lastLocationRef.current) {
        const distance = calculateDistance(
          lastLocationRef.current.lat,
          lastLocationRef.current.lng,
          lat,
          lng
        );
        
        // Обновляем только если переместились больше чем на 10 метров
        if (distance < 10) {
          return;
        }
      }

      await updateTrack(trackId, {
        action: 'add_point',
        lat,
        lng,
        smartwatch_data: {
          timestamp: new Date().toISOString(),
          accuracy: accuracy || 0,
          source: 'gps'
        }
      });

      lastLocationRef.current = { lat, lng };
      
      setState(prev => ({
        ...prev,
        lastUpdate: new Date(),
        locationCount: prev.locationCount + 1,
        error: null
      }));

      onLocationUpdate?.(lat, lng);
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Ошибка обновления трека';
      setState(prev => ({ ...prev, error: errorMessage }));
      onError?.(errorMessage);
    }
  }, [trackId, accuracy, onLocationUpdate, onError]);

  const startTracking = useCallback(() => {
    if (enabled && !isWatching) {
      startWatching();
      setState(prev => ({ ...prev, isTracking: true, error: null }));
    }
  }, [enabled, isWatching, startWatching]);

  const stopTracking = useCallback(() => {
    stopWatching();
    if (intervalRef.current) {
      clearInterval(intervalRef.current);
      intervalRef.current = null;
    }
    setState(prev => ({ ...prev, isTracking: false }));
  }, [stopWatching]);

  // Обработка геолокации
  useEffect(() => {
    if (latitude && longitude && enabled && isWatching) {
      updateTrackLocation(latitude, longitude);
    }
  }, [latitude, longitude, enabled, isWatching, updateTrackLocation]);

  // Обработка ошибок геолокации
  useEffect(() => {
    if (geoError) {
      setState(prev => ({ ...prev, error: geoError }));
      onError?.(geoError);
    }
  }, [geoError, onError]);

  // Автоматический старт/стоп отслеживания
  useEffect(() => {
    if (enabled) {
      startTracking();
    } else {
      stopTracking();
    }

    return () => {
      stopTracking();
    };
  }, [enabled, startTracking, stopTracking]);

  // Периодическое обновление каждые 5 минут
  useEffect(() => {
    if (enabled && isWatching) {
      intervalRef.current = setInterval(() => {
        if (latitude && longitude) {
          updateTrackLocation(latitude, longitude);
        }
      }, interval);

      return () => {
        if (intervalRef.current) {
          clearInterval(intervalRef.current);
        }
      };
    }
  }, [enabled, isWatching, latitude, longitude, interval, updateTrackLocation]);

  return {
    ...state,
    isWatching,
    startTracking,
    stopTracking,
    currentLocation: latitude && longitude ? { lat: latitude, lng: longitude } : null,
    accuracy
  };
};

// Функция для расчета расстояния между двумя точками (в метрах)
function calculateDistance(lat1: number, lng1: number, lat2: number, lng2: number): number {
  const R = 6371e3; // радиус Земли в метрах
  const φ1 = lat1 * Math.PI / 180;
  const φ2 = lat2 * Math.PI / 180;
  const Δφ = (lat2 - lat1) * Math.PI / 180;
  const Δλ = (lng2 - lng1) * Math.PI / 180;

  const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
          Math.cos(φ1) * Math.cos(φ2) *
          Math.sin(Δλ/2) * Math.sin(Δλ/2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

  return R * c; // расстояние в метрах
}
