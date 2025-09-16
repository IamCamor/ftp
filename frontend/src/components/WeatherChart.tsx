import React, { useEffect, useRef } from 'react';
import Icon from './Icon';
import type { WeatherPoint } from '../types';

interface WeatherData {
  point: WeatherPoint;
  temperature: number;
  pressure: number;
  humidity: number;
  windSpeed: number;
  description: string;
  icon: string;
}

interface WeatherChartProps {
  weatherData: WeatherData[];
  selectedPoint?: WeatherPoint | null;
}

const WeatherChart: React.FC<WeatherChartProps> = ({ weatherData, selectedPoint }) => {
  const chartRef = useRef<HTMLCanvasElement>(null);

  useEffect(() => {
    if (weatherData.length === 0) return;

    const canvas = chartRef.current;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Устанавливаем размеры canvas
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * window.devicePixelRatio;
    canvas.height = rect.height * window.devicePixelRatio;
    ctx.scale(window.devicePixelRatio, window.devicePixelRatio);

    // Очищаем canvas
    ctx.clearRect(0, 0, rect.width, rect.height);

    // Настройки графика
    const padding = 40;
    const chartWidth = rect.width - padding * 2;
    const chartHeight = rect.height - padding * 2;

    // Находим минимум и максимум для масштабирования
    const temperatures = weatherData.map(d => d.temperature);
    const pressures = weatherData.map(d => d.pressure);
    
    const minTemp = Math.min(...temperatures);
    const maxTemp = Math.max(...temperatures);
    const minPressure = Math.min(...pressures);
    const maxPressure = Math.max(...pressures);

    // Функция для преобразования значения в координаты
    const tempToY = (temp: number) => {
      const normalized = (temp - minTemp) / (maxTemp - minTemp);
      return padding + chartHeight - (normalized * chartHeight);
    };

    const pressureToY = (pressure: number) => {
      const normalized = (pressure - minPressure) / (maxPressure - minPressure);
      return padding + chartHeight - (normalized * chartHeight);
    };

    // Рисуем оси
    ctx.strokeStyle = '#e5e7eb';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(padding, padding);
    ctx.lineTo(padding, padding + chartHeight);
    ctx.lineTo(padding + chartWidth, padding + chartHeight);
    ctx.stroke();

    // Рисуем сетку
    ctx.strokeStyle = '#f3f4f6';
    ctx.lineWidth = 0.5;
    for (let i = 1; i <= 4; i++) {
      const y = padding + (chartHeight / 5) * i;
      ctx.beginPath();
      ctx.moveTo(padding, y);
      ctx.lineTo(padding + chartWidth, y);
      ctx.stroke();
    }

    // Рисуем линию температуры
    ctx.strokeStyle = '#ef4444';
    ctx.lineWidth = 2;
    ctx.beginPath();
    weatherData.forEach((data, index) => {
      const x = padding + (chartWidth / (weatherData.length - 1)) * index;
      const y = tempToY(data.temperature);
      
      if (index === 0) {
        ctx.moveTo(x, y);
      } else {
        ctx.lineTo(x, y);
      }
    });
    ctx.stroke();

    // Рисуем точки температуры
    ctx.fillStyle = '#ef4444';
    weatherData.forEach((data, index) => {
      const x = padding + (chartWidth / (weatherData.length - 1)) * index;
      const y = tempToY(data.temperature);
      
      ctx.beginPath();
      ctx.arc(x, y, 4, 0, 2 * Math.PI);
      ctx.fill();
    });

    // Рисуем линию давления
    ctx.strokeStyle = '#3b82f6';
    ctx.lineWidth = 2;
    ctx.beginPath();
    weatherData.forEach((data, index) => {
      const x = padding + (chartWidth / (weatherData.length - 1)) * index;
      const y = pressureToY(data.pressure);
      
      if (index === 0) {
        ctx.moveTo(x, y);
      } else {
        ctx.lineTo(x, y);
      }
    });
    ctx.stroke();

    // Рисуем точки давления
    ctx.fillStyle = '#3b82f6';
    weatherData.forEach((data, index) => {
      const x = padding + (chartWidth / (weatherData.length - 1)) * index;
      const y = pressureToY(data.pressure);
      
      ctx.beginPath();
      ctx.arc(x, y, 4, 0, 2 * Math.PI);
      ctx.fill();
    });

    // Подписи осей
    ctx.fillStyle = '#6b7280';
    ctx.font = '12px system-ui';
    ctx.textAlign = 'center';
    
    // Температура
    ctx.fillText('Температура (°C)', padding - 20, padding + chartHeight / 2);
    
    // Давление
    ctx.fillText('Давление (мм рт.ст.)', padding + chartWidth + 20, padding + chartHeight / 2);

    // Подписи точек
    ctx.textAlign = 'center';
    ctx.font = '10px system-ui';
    weatherData.forEach((data, index) => {
      const x = padding + (chartWidth / (weatherData.length - 1)) * index;
      const y = padding + chartHeight + 20;
      
      ctx.fillText(data.point.name, x, y);
    });

  }, [weatherData, selectedPoint]);

  if (weatherData.length === 0) {
    return (
      <div className="weather-chart-empty">
        <Icon name="show_chart" size="xl" />
        <p>Выберите точки для отображения графика</p>
      </div>
    );
  }

  return (
    <div className="weather-chart">
      <div className="chart-header">
        <h3>График погоды</h3>
        <div className="chart-legend">
          <div className="legend-item">
            <div className="legend-color" style={{ backgroundColor: '#ef4444' }}></div>
            <span>Температура</span>
          </div>
          <div className="legend-item">
            <div className="legend-color" style={{ backgroundColor: '#3b82f6' }}></div>
            <span>Давление</span>
          </div>
        </div>
      </div>
      <div className="chart-container">
        <canvas ref={chartRef} className="weather-chart-canvas"></canvas>
      </div>
    </div>
  );
};

export default WeatherChart;