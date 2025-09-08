import React, { useState, useEffect } from 'react';
import Icon from '../components/Icon';
import { getWeatherFavs } from '../api';
import type { WeatherFav } from '../types';

const WeatherPage: React.FC = () => {
  const [favs, setFavs] = useState<WeatherFav[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    loadWeatherFavs();
  }, []);

  const loadWeatherFavs = async () => {
    try {
      setLoading(true);
      const data = await getWeatherFavs();
      setFavs(data);
    } catch (err) {
      setError('Не удалось загрузить избранные места');
      console.error('Weather favs loading error:', err);
    } finally {
      setLoading(false);
    }
  };

  const getWeatherData = (_fav: WeatherFav) => {
    // Mock weather data - в реальном приложении здесь будет API погоды
    return {
      temperature: Math.round(Math.random() * 30 - 10), // -10 to 20°C
      condition: ['Ясно', 'Облачно', 'Дождь', 'Снег'][Math.floor(Math.random() * 4)],
      humidity: Math.round(Math.random() * 40 + 40), // 40-80%
      windSpeed: Math.round(Math.random() * 15 + 2), // 2-17 km/h
    };
  };

  if (loading) {
    return (
      <div className="screen">
        <div className="loading">Загрузка погоды...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="screen">
        <div className="error">
          <p>{error}</p>
          <button onClick={loadWeatherFavs} className="btn btn-primary">
            Попробовать снова
          </button>
        </div>
      </div>
    );
  }

  if (favs.length === 0) {
    return (
      <div className="screen">
        <div className="empty-state">
          <Icon name="cloud_off" size={64} />
          <h3>Нет избранных мест</h3>
          <p>Добавьте места на карте для отслеживания погоды</p>
        </div>
      </div>
    );
  }

  return (
    <div className="screen">
      <div className="weather-header">
        <h2>Погода в избранных местах</h2>
        <p>Обновлено: {new Date().toLocaleTimeString()}</p>
      </div>

      <div className="weather-list">
        {favs.map((fav) => {
          const weather = getWeatherData(fav);
          return (
            <div key={fav.id} className="weather-card glass">
              <div className="weather-location">
                <Icon name="location_on" size={20} />
                <div>
                  <h3>{fav.label}</h3>
                  <p>{fav.lat.toFixed(4)}, {fav.lng.toFixed(4)}</p>
                </div>
              </div>

              <div className="weather-main">
                <div className="temperature">
                  <span className="temp-value">{weather.temperature}°</span>
                  <span className="temp-unit">C</span>
                </div>
                <div className="condition">
                  <Icon name="wb_sunny" size={32} />
                  <span>{weather.condition}</span>
                </div>
              </div>

              <div className="weather-details">
                <div className="detail-item">
                  <Icon name="water_drop" size={16} />
                  <span>Влажность: {weather.humidity}%</span>
                </div>
                <div className="detail-item">
                  <Icon name="air" size={16} />
                  <span>Ветер: {weather.windSpeed} км/ч</span>
                </div>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};

export default WeatherPage;

