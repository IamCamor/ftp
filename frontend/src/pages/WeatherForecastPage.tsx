import React, { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, BarChart, Bar } from 'recharts';
import Icon from '../components/Icon';
import { reverseGeocode } from '../api';

interface ForecastData {
  date: string;
  time: string;
  temperature: number;
  windSpeed: number;
  pressure: number;
  humidity: number;
  precipitation: string;
  cloudiness: string;
  windDirection: string;
}

const WeatherForecastPage: React.FC = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const [forecastData, setForecastData] = useState<ForecastData[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [locationName, setLocationName] = useState<string>('');
  const [chartType, setChartType] = useState<'temperature' | 'wind' | 'pressure'>('temperature');

  // Получаем координаты из state
  const coordinates = location.state?.coordinates || { lat: 55.7558, lng: 37.6176 };

  useEffect(() => {
    loadForecastData();
    loadLocationName();
  }, []);

  const loadLocationName = async () => {
    try {
      const result = await reverseGeocode(coordinates.lat, coordinates.lng);
      setLocationName(result.data.name);
    } catch (err) {
      console.error('Failed to get location name:', err);
      setLocationName(`Место ${coordinates.lat.toFixed(4)}, ${coordinates.lng.toFixed(4)}`);
    }
  };

  const loadForecastData = async () => {
    try {
      setLoading(true);
      setError(null);

      // В реальном приложении здесь будет запрос к API
      // const response = await getWeatherForecast(coordinates.lat, coordinates.lng, 10);
      // setForecastData(response.data);

      // Пока используем моковые данные
      const mockData = generateMockForecastData();
      setForecastData(mockData);
    } catch (err) {
      console.error('Failed to load forecast data:', err);
      setError('Не удалось загрузить прогноз погоды');
    } finally {
      setLoading(false);
    }
  };

  const generateMockForecastData = (): ForecastData[] => {
    const data: ForecastData[] = [];
    const today = new Date();

    for (let i = 0; i < 10; i++) {
      const date = new Date(today);
      date.setDate(today.getDate() + i);
      
      const times = ['06:00', '12:00', '18:00'];
      
      for (const time of times) {
        const baseTemp = 15 + Math.sin((date.getTime() / (1000 * 60 * 60 * 24)) * 0.1) * 10;
        const timeMultiplier = time === '06:00' ? 0.8 : time === '12:00' ? 1.2 : 0.9;
        const randomVariation = (Math.random() - 0.5) * 8;
        
        data.push({
          date: date.toISOString().split('T')[0],
          time: time,
          temperature: Math.round(baseTemp * timeMultiplier + randomVariation),
          windSpeed: Math.round(2 + Math.random() * 12 + Math.sin((date.getTime() / (1000 * 60 * 60 * 24)) * 0.3) * 5),
          pressure: Math.round(750 + Math.sin((date.getTime() / (1000 * 60 * 60 * 24)) * 0.2) * 20 + (Math.random() - 0.5) * 10),
          humidity: Math.round(40 + Math.random() * 40),
          precipitation: ['Нет', 'Дождь', 'Снег', 'Град'][Math.floor(Math.random() * 4)],
          cloudiness: ['Ясно', 'Облачно', 'Пасмурно'][Math.floor(Math.random() * 3)],
          windDirection: ['С', 'СВ', 'В', 'ЮВ', 'Ю', 'ЮЗ', 'З', 'СЗ'][Math.floor(Math.random() * 8)]
        });
      }
    }

    return data;
  };

  const formatDate = (dateStr: string) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('ru-RU', { 
      day: '2-digit', 
      month: '2-digit',
      weekday: 'short'
    });
  };

  const formatTooltipLabel = (label: string) => {
    const [date, time] = label.split(' ');
    return `${formatDate(date)} ${time}`;
  };

  const handleBack = () => {
    navigate(-1);
  };

  if (loading) {
    return (
      <div className="screen">
        <div className="forecast-header">
          <button className="back-button" onClick={handleBack}>
            <Icon name="arrow_back" size="md" />
          </button>
          <h1>Прогноз погоды</h1>
        </div>
        <div className="loading">Загрузка прогноза...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="screen">
        <div className="forecast-header">
          <button className="back-button" onClick={handleBack}>
            <Icon name="arrow_back" size="md" />
          </button>
          <h1>Прогноз погоды</h1>
        </div>
        <div className="error">
          <Icon name="error" size="xl" />
          <p>{error}</p>
          <button onClick={loadForecastData} className="btn btn-primary">
            Попробовать снова
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="screen">
      <div className="forecast-header">
        <button className="back-button" onClick={handleBack}>
          <Icon name="arrow_back" size="md" />
        </button>
        <div className="forecast-title">
          <h1>Прогноз погоды</h1>
          <p>{locationName}</p>
        </div>
        <button 
          className="btn btn-primary btn-sm"
          onClick={loadForecastData}
        >
          <Icon name="refresh" size="sm" />
          Обновить
        </button>
      </div>

      <div className="forecast-content">
        <div className="forecast-chart">
          <div className="chart-header">
            <h3>Прогноз на 10 дней</h3>
            <div className="chart-controls">
              <button 
                className={`chart-btn ${chartType === 'temperature' ? 'active' : ''}`}
                onClick={() => setChartType('temperature')}
              >
                <Icon name="thermostat" size="sm" />
                Температура
              </button>
              <button 
                className={`chart-btn ${chartType === 'wind' ? 'active' : ''}`}
                onClick={() => setChartType('wind')}
              >
                <Icon name="air" size="sm" />
                Ветер
              </button>
              <button 
                className={`chart-btn ${chartType === 'pressure' ? 'active' : ''}`}
                onClick={() => setChartType('pressure')}
              >
                <Icon name="speed" size="sm" />
                Давление
              </button>
            </div>
          </div>

          <div className="chart-container">
            <ResponsiveContainer width="100%" height={400}>
              {chartType === 'temperature' ? (
                <LineChart data={forecastData}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis 
                    dataKey="date" 
                    tickFormatter={formatDate}
                    tick={{ fontSize: 12 }}
                  />
                  <YAxis 
                    label={{ value: '°C', angle: -90, position: 'insideLeft' }}
                    tick={{ fontSize: 12 }}
                  />
                  <Tooltip 
                    labelFormatter={formatTooltipLabel}
                    formatter={(value: number) => [
                      `${value}°C`, 
                      'Температура'
                    ]}
                  />
                  <Legend />
                  <Line 
                    type="monotone" 
                    dataKey="temperature" 
                    stroke="#ff6b35" 
                    strokeWidth={2}
                    dot={{ fill: '#ff6b35', strokeWidth: 2, r: 4 }}
                    name="Температура"
                  />
                </LineChart>
              ) : chartType === 'wind' ? (
                <BarChart data={forecastData}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis 
                    dataKey="date" 
                    tickFormatter={formatDate}
                    tick={{ fontSize: 12 }}
                  />
                  <YAxis 
                    label={{ value: 'м/с', angle: -90, position: 'insideLeft' }}
                    tick={{ fontSize: 12 }}
                  />
                  <Tooltip 
                    labelFormatter={formatTooltipLabel}
                    formatter={(value: number) => [
                      `${value} м/с`, 
                      'Скорость ветра'
                    ]}
                  />
                  <Legend />
                  <Bar 
                    dataKey="windSpeed" 
                    fill="#4fc3f7"
                    name="Скорость ветра"
                  />
                </BarChart>
              ) : (
                <LineChart data={forecastData}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis 
                    dataKey="date" 
                    tickFormatter={formatDate}
                    tick={{ fontSize: 12 }}
                  />
                  <YAxis 
                    label={{ value: 'мм рт.ст.', angle: -90, position: 'insideLeft' }}
                    tick={{ fontSize: 12 }}
                  />
                  <Tooltip 
                    labelFormatter={formatTooltipLabel}
                    formatter={(value: number) => [
                      `${value} мм рт.ст.`, 
                      'Давление'
                    ]}
                  />
                  <Legend />
                  <Line 
                    type="monotone" 
                    dataKey="pressure" 
                    stroke="#9c27b0" 
                    strokeWidth={2}
                    dot={{ fill: '#9c27b0', strokeWidth: 2, r: 4 }}
                    name="Давление"
                  />
                </LineChart>
              )}
            </ResponsiveContainer>
          </div>
        </div>

        <div className="forecast-details">
          <h3>Детальный прогноз</h3>
          <div className="forecast-list">
            {forecastData.slice(0, 9).map((item, index) => (
              <div key={index} className="forecast-item">
                <div className="forecast-date">
                  <span className="date">{formatDate(item.date)}</span>
                  <span className="time">{item.time}</span>
                </div>
                <div className="forecast-weather">
                  <div className="temperature">
                    {item.temperature}°C
                  </div>
                  <div className="details">
                    <span>Ветер: {item.windSpeed} м/с {item.windDirection}</span>
                    <span>Давление: {item.pressure} мм рт.ст.</span>
                    <span>Влажность: {item.humidity}%</span>
                    <span>Осадки: {item.precipitation}</span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};

export default WeatherForecastPage;
