import React, { useState, useEffect } from 'react';
import Icon from '../components/Icon';
import { getWeatherPoints, getWeather } from '../api';
import { checkTokenValidity } from '../utils/auth';
import type { WeatherPoint } from '../types';
import WeatherChart from '../components/WeatherChart';
import WeatherPointManager from '../components/WeatherPointManager';

interface WeatherDataWithPoint {
  point: WeatherPoint;
  temperature: number;
  pressure: number;
  humidity: number;
  windSpeed: number;
  description: string;
  icon: string;
}

const WeatherPageNew: React.FC = () => {
  const [points, setPoints] = useState<WeatherPoint[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [weatherData, setWeatherData] = useState<WeatherDataWithPoint[]>([]);
  const [loadingWeather, setLoadingWeather] = useState(false);
  const [selectedPoint, setSelectedPoint] = useState<WeatherPoint | undefined>(undefined);

  useEffect(() => {
    loadWeatherPoints();
  }, []);

  useEffect(() => {
    if (points.length > 0) {
      loadWeatherForAllPoints();
    }
  }, [points]);

  const loadWeatherPoints = async () => {
    try {
      setLoading(true);
      setError(null);
      
      const token = localStorage.getItem('token');
      console.log('WeatherPage: Token check:', token ? 'Token exists' : 'No token');
      if (!token) {
        console.log('User not authenticated, skipping weather points load');
        setLoading(false);
        return;
      }

      const isValidToken = await checkTokenValidity();
      if (!isValidToken) {
        console.log('Token is invalid, skipping weather points load');
        setLoading(false);
        return;
      }

      console.log('Loading weather points...');
      const response = await getWeatherPoints();
      setPoints(response.data);
    } catch (error) {
      console.error('Weather points loading error:', error);
      setError('Не удалось загрузить точки погоды');
    } finally {
      setLoading(false);
    }
  };

  const loadWeatherForAllPoints = async () => {
    if (points.length === 0) return;

    try {
      setLoadingWeather(true);
      const weatherPromises = points.map(async (point) => {
        try {
          const weather = await getWeather(point.lat, point.lng);
          return {
            point,
            temperature: weather.data.temperature,
            pressure: weather.data.pressure,
            humidity: 0, // API не возвращает влажность
            windSpeed: weather.data.wind_speed,
            description: weather.data.cloudiness || 'Ясно',
            icon: 'wb_sunny' // API не возвращает иконку
          };
        } catch (error) {
          console.error(`Error loading weather for point ${point.id}:`, error);
          return null;
        }
      });

      const results = await Promise.all(weatherPromises);
      const validResults = results.filter((result): result is WeatherDataWithPoint => result !== null);
      setWeatherData(validResults);
    } catch (error) {
      console.error('Error loading weather data:', error);
    } finally {
      setLoadingWeather(false);
    }
  };

  const handlePointSelect = (point: WeatherPoint) => {
    setSelectedPoint(point);
  };

  const handlePointsChange = (newPoints: WeatherPoint[]) => {
    setPoints(newPoints);
  };

  if (loading) {
    return (
      <div className="weather-page">
        <div className="weather-loading">
          <Icon name="refresh" size="xl" />
          <p>Загрузка точек погоды...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="weather-page">
        <div className="weather-error">
          <Icon name="error" size="xl" />
          <p>{error}</p>
          <button className="btn btn-primary" onClick={loadWeatherPoints}>
            <Icon name="refresh" size="sm" />
            Попробовать снова
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="weather-page">
      <div className="weather-header">
        <h1>Прогноз погоды</h1>
        <p>Выберите точки для просмотра прогноза погоды</p>
      </div>

      <div className="weather-content">
        <div className="weather-sidebar">
          <WeatherPointManager
            onPointSelect={handlePointSelect}
            onPointsChange={handlePointsChange}
          />
        </div>

        <div className="weather-main">
          {points.length === 0 ? (
            <div className="weather-empty">
              <Icon name="location_on" size="xl" />
              <h3>Добавьте точки погоды</h3>
              <p>Создайте до 5 точек для отслеживания погоды</p>
            </div>
          ) : (
            <>
              {loadingWeather ? (
                <div className="weather-loading">
                  <Icon name="refresh" size="lg" />
                  <p>Загрузка данных о погоде...</p>
                </div>
              ) : (
                <>
                  <div className="weather-cards">
                    {weatherData.map((data) => (
                      <div 
                        key={data.point.id} 
                        className={`weather-card ${selectedPoint?.id === data.point.id ? 'selected' : ''}`}
                        onClick={() => setSelectedPoint(data.point)}
                      >
                        <div className="weather-card-header">
                          <h3>{data.point.name}</h3>
                          <div className="weather-icon">
                            <Icon name={data.icon} size="lg" />
                          </div>
                        </div>
                        <div className="weather-card-content">
                          <div className="weather-temp">
                            <span className="temp-value">{Math.round(data.temperature)}°</span>
                            <span className="temp-desc">{data.description}</span>
                          </div>
                          <div className="weather-details">
                            <div className="weather-detail">
                              <Icon name="speed" size="sm" />
                              <span>{data.windSpeed} м/с</span>
                            </div>
                            <div className="weather-detail">
                              <Icon name="water_drop" size="sm" />
                              <span>{data.humidity}%</span>
                            </div>
                            <div className="weather-detail">
                              <Icon name="air" size="sm" />
                              <span>{data.pressure} мм рт.ст.</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>

                  {weatherData.length > 1 && (
                    <div className="weather-chart-section">
                      <WeatherChart 
                        weatherData={weatherData} 
                        selectedPoint={selectedPoint}
                      />
                    </div>
                  )}
                </>
              )}
            </>
          )}
        </div>
      </div>
    </div>
  );
};

export default WeatherPageNew;
