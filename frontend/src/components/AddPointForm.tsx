import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import Icon from './Icon';
import WorkingHoursSelector from './WorkingHoursSelector';
import { reverseGeocode } from '../api';

interface AddPointFormProps {
  onClose: () => void;
  onSubmit: (data: PointFormData) => void;
  initialData?: Partial<PointFormData> | null;
}

interface PointFormData {
  name: string;
  description: string;
  latitude: number;
  longitude: number;
  type: 'fishing' | 'weather' | 'general';
  privacy: 'all' | 'friends' | 'private';
  photo_url?: string;
  working_hours?: {
    is_24_7?: boolean;
    schedule?: {
      monday?: { open: string; close: string; closed?: boolean };
      tuesday?: { open: string; close: string; closed?: boolean };
      wednesday?: { open: string; close: string; closed?: boolean };
      thursday?: { open: string; close: string; closed?: boolean };
      friday?: { open: string; close: string; closed?: boolean };
      saturday?: { open: string; close: string; closed?: boolean };
      sunday?: { open: string; close: string; closed?: boolean };
    };
  };
}

const AddPointForm: React.FC<AddPointFormProps> = ({ onClose, onSubmit, initialData }) => {
  const navigate = useNavigate();
  const [formData, setFormData] = useState<PointFormData>({
    name: '',
    description: '',
    latitude: 55.7558,
    longitude: 37.6176,
    type: 'fishing',
    privacy: 'all',
    photo_url: ''
  });

  const [photo, setPhoto] = useState<string>('');
  const [uploading, setUploading] = useState(false);
  const [loadingLocationName, setLoadingLocationName] = useState(false);

  // Обрабатываем initialData при загрузке
  useEffect(() => {
    if (initialData) {
      setFormData(prev => ({
        ...prev,
        ...initialData
      }));
    }
  }, [initialData]);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleFileUpload = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file || !file.type.startsWith('image/')) return;

    setUploading(true);
    try {
      const reader = new FileReader();
      reader.onload = (e) => {
        const result = e.target?.result as string;
        if (result) {
          setPhoto(result);
          setFormData(prev => ({ ...prev, photo_url: result }));
        }
      };
      reader.readAsDataURL(file);
    } catch (error) {
      console.error('Error uploading file:', error);
      alert('Ошибка при загрузке файла');
    } finally {
      setUploading(false);
    }
  };

  const getLocationName = async (lat: number, lng: number) => {
    try {
      setLoadingLocationName(true);
      const result = await reverseGeocode(lat, lng);
      setFormData(prev => ({
        ...prev,
        name: result.data.name
      }));
    } catch (err) {
      console.error('Failed to get location name:', err);
      setFormData(prev => ({
        ...prev,
        name: `Место ${lat.toFixed(4)}, ${lng.toFixed(4)}`
      }));
    } finally {
      setLoadingLocationName(false);
    }
  };

  const handleGetCurrentLocation = () => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        async (position) => {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;
          
          setFormData(prev => ({
            ...prev,
            latitude: lat,
            longitude: lng
          }));
          
          // Получаем название места
          await getLocationName(lat, lng);
        },
        (error) => {
          console.warn('Error getting location:', error);
          let message = 'Не удалось получить местоположение';
          switch (error.code) {
            case error.PERMISSION_DENIED:
              message = 'Доступ к местоположению запрещен';
              break;
            case error.POSITION_UNAVAILABLE:
              message = 'Информация о местоположении недоступна';
              break;
            case error.TIMEOUT:
              message = 'Время ожидания истекло';
              break;
          }
          console.warn(message);
        }
      );
    } else {
      console.warn('Геолокация не поддерживается');
    }
  };

  const handleMapSelection = () => {
    navigate('/map-selection', {
      state: {
        coordinates: { lat: formData.latitude, lng: formData.longitude }
      }
    });
  };

  const handleWeatherForecast = () => {
    navigate('/weather-forecast', {
      state: {
        coordinates: { lat: formData.latitude, lng: formData.longitude }
      }
    });
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit(formData);
  };

  return (
    <div className="screen">
      <div className="page-header">
        <button className="back-button" onClick={onClose}>
          <Icon name="arrow_back" size="md" />
        </button>
        <h1>Добавить место</h1>
      </div>

      <div className="page-content">
        <form onSubmit={handleSubmit} className="point-form">
          <div className="form-group">
            <label htmlFor="name">Название места *</label>
            <input
              type="text"
              id="name"
              name="name"
              value={formData.name}
              onChange={handleInputChange}
              required
              placeholder="Например: Река Волга, участок 5"
            />
          </div>

          <div className="form-group">
            <label htmlFor="description">Описание</label>
            <textarea
              id="description"
              name="description"
              value={formData.description}
              onChange={handleInputChange}
              rows={3}
              placeholder="Опишите особенности места..."
            />
          </div>

          <div className="form-group">
            <label>Фотография места</label>
            <div className="photo-upload-section">
              <input
                type="file"
                id="point-photo-upload"
                accept="image/*"
                onChange={handleFileUpload}
                style={{ display: 'none' }}
              />
              <label htmlFor="point-photo-upload" className="file-upload-btn">
                <Icon name="cloud_upload" size="md" />
                {uploading ? 'Загружаем...' : 'Загрузить фото'}
              </label>
              {photo && (
                <div className="photo-preview">
                  <img src={photo} alt="Предпросмотр" />
                  <button
                    type="button"
                    onClick={() => {
                      setPhoto('');
                      setFormData(prev => ({ ...prev, photo_url: '' }));
                    }}
                    className="remove-photo-btn"
                  >
                    <Icon name="close" size="sm" />
                  </button>
                </div>
              )}
            </div>
          </div>

          <div className="form-row">
            <div className="form-group">
              <label htmlFor="type">Тип места</label>
              <select
                id="type"
                name="type"
                value={formData.type}
                onChange={handleInputChange}
              >
                <option value="fishing">Место для рыбалки</option>
                <option value="weather">Погодная точка</option>
                <option value="general">Общее место</option>
              </select>
            </div>

            <div className="form-group">
              <label htmlFor="privacy">Приватность</label>
              <select
                id="privacy"
                name="privacy"
                value={formData.privacy}
                onChange={handleInputChange}
              >
                <option value="all">Все пользователи</option>
                <option value="friends">Только друзья</option>
                <option value="private">Только я</option>
              </select>
            </div>
          </div>

          <div className="form-group">
            <WorkingHoursSelector
              value={formData.working_hours}
              onChange={(workingHours) => setFormData(prev => ({ ...prev, working_hours: workingHours }))}
            />
          </div>

          <div className="location-actions">
            <button
              type="button"
              onClick={handleGetCurrentLocation}
              className="btn btn-secondary"
              disabled={loadingLocationName}
            >
              <Icon name="my_location" size="md" />
              {loadingLocationName ? 'Определяем...' : 'Получить текущее местоположение'}
            </button>
            <button
              type="button"
              onClick={handleMapSelection}
              className="btn btn-secondary"
            >
              <Icon name="map" size="md" />
              Выбрать на карте
            </button>
            <button
              type="button"
              onClick={handleWeatherForecast}
              className="btn btn-outline"
            >
              <Icon name="wb_sunny" size="md" />
              Прогноз погоды
            </button>
          </div>

          <div className="coordinates-info">
            <div className="coords-display">
              <Icon name="place" size="md" />
              <div className="coords-text">
                <span className="coords-label">Координаты:</span>
                <span className="coords-values">
                  {formData.latitude.toFixed(6)}, {formData.longitude.toFixed(6)}
                </span>
              </div>
            </div>
            {formData.latitude !== 55.7558 || formData.longitude !== 37.6176 ? (
              <div className="location-status">
                <Icon name="check_circle" size="sm" />
                <span>Координаты получены автоматически</span>
              </div>
            ) : null}
          </div>

          <div className="form-actions">
            <button type="submit" className="btn btn-primary">
              <Icon name="add_location" size="md" />
              Добавить место
            </button>
          </div>
        </form>
      </div>

    </div>
  );
};

export default AddPointForm;