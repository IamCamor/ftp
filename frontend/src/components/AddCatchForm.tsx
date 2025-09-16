import React, { useState, useEffect } from 'react';
import Icon from './Icon';
import MapPointSelector from './MapPointSelector';
import FishSpeciesSelector from './FishSpeciesSelector';
import { getWeather } from '../api';

interface AddCatchFormProps {
  onClose: () => void;
  onSubmit: (data: CatchFormData) => void;
}

interface CatchFormData {
  species: string[];
  weight: string;
  length: string;
  style: string;
  lure: string;
  tackle: string;
  notes: string;
  photo_url: string;
  additional_photos: string;
  caught_at: string;
  privacy: 'all' | 'friends' | 'private';
  lat: number;
  lng: number;
  // Поля погоды
  temperature?: number;
  pressure?: number;
  wind_speed?: number;
  cloudiness?: string;
  precipitation?: string;
  wind_direction?: string;
  // Параметры места
  is_paid_place?: boolean;
  has_security?: boolean;
  has_parking?: boolean;
}

const AddCatchForm: React.FC<AddCatchFormProps> = ({ onClose, onSubmit }) => {
  const [formData, setFormData] = useState<CatchFormData>({
    species: [],
    weight: '',
    length: '',
    style: '',
    lure: '',
    tackle: '',
    notes: '',
    photo_url: '',
    additional_photos: '',
    caught_at: new Date().toISOString().slice(0, 16),
    privacy: 'all',
    lat: 55.7558,
    lng: 37.6176,
    // Поля погоды
    temperature: undefined,
    pressure: undefined,
    wind_speed: undefined,
    cloudiness: undefined,
    precipitation: undefined,
    wind_direction: undefined,
    // Параметры места
    is_paid_place: false,
    has_security: false,
    has_parking: false
  });

  const [photos, setPhotos] = useState<string[]>([]);
  const [showMapSelector, setShowMapSelector] = useState(false);
  const [uploading, setUploading] = useState(false);
  const [loadingWeather, setLoadingWeather] = useState(false);
  const [weatherLoaded, setWeatherLoaded] = useState(false);
  const [weatherExpanded, setWeatherExpanded] = useState(false);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value, type } = e.target;
    if (type === 'checkbox') {
      const checked = (e.target as HTMLInputElement).checked;
      setFormData(prev => ({
        ...prev,
        [name]: checked
      }));
    } else {
      setFormData(prev => ({
        ...prev,
        [name]: value
      }));
    }
  };

  // Функция для загрузки погоды по координатам и дате
  const loadWeather = async (lat: number, lng: number, date?: string, forceReload = false) => {
    if (weatherLoaded && !forceReload) return; // Не загружаем повторно, если не принудительно
    
    try {
      setLoadingWeather(true);
      const response = await getWeather(lat, lng, date);
      
      if (response.success && response.data) {
        const weather = response.data;
        setFormData(prev => ({
          ...prev,
          temperature: weather.temperature,
          pressure: weather.pressure,
          wind_speed: weather.wind_speed,
          cloudiness: weather.cloudiness,
          precipitation: weather.precipitation,
          wind_direction: weather.wind_direction
        }));
        setWeatherLoaded(true);
        setWeatherExpanded(true); // Автоматически разворачиваем аккордеон при загрузке погоды
      }
    } catch (error) {
      console.error('Ошибка загрузки погоды:', error);
    } finally {
      setLoadingWeather(false);
    }
  };

  // Автоматически загружаем погоду при изменении координат или даты
  useEffect(() => {
    if (formData.lat && formData.lng) {
      setWeatherLoaded(false); // Сбрасываем флаг загрузки при изменении координат или даты
      const date = formData.caught_at ? new Date(formData.caught_at).toISOString().split('T')[0] : undefined;
      loadWeather(formData.lat, formData.lng, date, true);
    }
  }, [formData.lat, formData.lng, formData.caught_at]);


  const handleRemovePhoto = (index: number) => {
    setPhotos(prev => prev.filter((_, i) => i !== index));
  };

  const handleFileUpload = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const files = event.target.files;
    if (!files || files.length === 0) return;

    setUploading(true);
    try {
      for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = (e) => {
            const result = e.target?.result as string;
            if (result) {
              if (photos.length === 0) {
                setFormData(prev => ({ ...prev, photo_url: result }));
              }
              setPhotos(prev => [...prev, result]);
            }
          };
          reader.readAsDataURL(file);
        }
      }
    } catch (error) {
      console.error('Error uploading files:', error);
      alert('Ошибка при загрузке файлов');
    } finally {
      setUploading(false);
    }
  };

  const handlePointSelect = (lat: number, lng: number) => {
    setFormData(prev => ({
      ...prev,
      lat,
      lng
    }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const submitData = {
      ...formData,
      additional_photos: JSON.stringify(photos.slice(1))
    };
    onSubmit(submitData);
  };

  return (
    <div className="screen">
      <div className="page-header">
        <button className="back-button" onClick={onClose}>
          <Icon name="arrow_back" size="md" />
        </button>
        <h1>Добавить улов</h1>
      </div>

      <div className="page-content">
        <form onSubmit={handleSubmit} className="catch-form">
          <div className="form-group">
            <label>Место улова</label>
            <div className="location-selector">
              <div className="location-info">
                <Icon name="place" size="md" />
                <div className="location-details">
                  <span className="location-coords">
                    {formData.lat.toFixed(6)}, {formData.lng.toFixed(6)}
                  </span>
                  <span className="location-label">Выбранное место</span>
                </div>
              </div>
              <button
                type="button"
                className="btn btn-secondary"
                onClick={() => setShowMapSelector(true)}
              >
                <Icon name="map" size="md" />
                Выбрать на карте
              </button>
            </div>
          </div>

          {/* Параметры места */}
          <div className="form-group">
            <label>Параметры места</label>
            <div className="place-parameters">
              <div className="parameter-item">
                <label className="checkbox-label">
                  <input
                    type="checkbox"
                    name="is_paid_place"
                    checked={formData.is_paid_place || false}
                    onChange={handleInputChange}
                  />
                  <span className="checkbox-custom"></span>
                  <Icon name="attach_money" size="md" />
                  <span>Платное место</span>
                </label>
              </div>
              
              <div className="parameter-item">
                <label className="checkbox-label">
                  <input
                    type="checkbox"
                    name="has_security"
                    checked={formData.has_security || false}
                    onChange={handleInputChange}
                  />
                  <span className="checkbox-custom"></span>
                  <Icon name="security" size="md" />
                  <span>Есть охрана</span>
                </label>
              </div>
              
              <div className="parameter-item">
                <label className="checkbox-label">
                  <input
                    type="checkbox"
                    name="has_parking"
                    checked={formData.has_parking || false}
                    onChange={handleInputChange}
                  />
                  <span className="checkbox-custom"></span>
                  <Icon name="local_parking" size="md" />
                  <span>Есть парковка</span>
                </label>
              </div>
            </div>
          </div>

          <div className="form-row">
            <div className="form-group">
              <label htmlFor="species">Виды рыб</label>
              <FishSpeciesSelector
                selectedSpecies={formData.species}
                onSpeciesChange={(species) => setFormData(prev => ({ ...prev, species }))}
                placeholder="Выберите виды рыб (необязательно)"
                maxSelections={5}
              />
            </div>

            <div className="form-group">
              <label htmlFor="weight">Вес (кг)</label>
              <input
                type="number"
                id="weight"
                name="weight"
                value={formData.weight}
                onChange={handleInputChange}
                step="0.1"
                placeholder="2.5"
              />
            </div>
          </div>

          <div className="form-row">
            <div className="form-group">
              <label htmlFor="length">Длина (см)</label>
              <input
                type="number"
                id="length"
                name="length"
                value={formData.length}
                onChange={handleInputChange}
                placeholder="45"
              />
            </div>

            <div className="form-group">
              <label htmlFor="caught_at">Дата и время</label>
              <input
                type="datetime-local"
                id="caught_at"
                name="caught_at"
                value={formData.caught_at}
                onChange={handleInputChange}
              />
            </div>
          </div>

          <div className="form-group">
            <label htmlFor="style">Способ ловли</label>
            <input
              type="text"
              id="style"
              name="style"
              value={formData.style}
              onChange={handleInputChange}
              placeholder="Например: Спиннинг"
            />
          </div>

          <div className="form-row">
            <div className="form-group">
              <label htmlFor="lure">Приманка</label>
              <input
                type="text"
                id="lure"
                name="lure"
                value={formData.lure}
                onChange={handleInputChange}
                placeholder="Например: Воблер"
              />
            </div>

            <div className="form-group">
              <label htmlFor="tackle">Снасть</label>
              <input
                type="text"
                id="tackle"
                name="tackle"
                value={formData.tackle}
                onChange={handleInputChange}
                placeholder="Например: Леска 0.3"
              />
            </div>
          </div>

          <div className="form-group">
            <label>Фотографии</label>
            <div className="photo-upload-section">
              <div className="file-upload-section">
                <input
                  type="file"
                  id="photo-upload"
                  multiple
                  accept="image/*"
                  onChange={handleFileUpload}
                  style={{ display: 'none' }}
                />
                <label htmlFor="photo-upload" className="file-upload-btn">
                  <Icon name="cloud_upload" size="md" />
                  {uploading ? 'Загружаем...' : 'Загрузить с устройства'}
                </label>
              </div>
            </div>

            {photos.length > 0 && (
              <div className="photos-preview">
                {photos.map((photo, index) => (
                  <div key={index} className="photo-item">
                    <img src={photo} alt={`Фото ${index + 1}`} />
                    <button
                      type="button"
                      onClick={() => handleRemovePhoto(index)}
                      className="remove-photo-btn"
                    >
                      <Icon name="close" size="sm" />
                    </button>
                  </div>
                ))}
              </div>
            )}
          </div>

          <div className="form-group">
            <label htmlFor="notes">Заметки</label>
            <textarea
              id="notes"
              name="notes"
              value={formData.notes}
              onChange={handleInputChange}
              rows={3}
              placeholder="Расскажите о вашем улове..."
            />
          </div>

          {/* Секция погоды - Аккордеон */}
          <div className="weather-section">
            <div className="weather-header">
              <button
                type="button"
                className="weather-toggle"
                onClick={() => setWeatherExpanded(!weatherExpanded)}
              >
                <h3>Погодные условия</h3>
                <Icon 
                  name={weatherExpanded ? "expand_less" : "expand_more"} 
                  size="md" 
                />
              </button>
              {weatherExpanded && (
                <button
                  type="button"
                  className="btn btn-secondary btn-sm"
                  onClick={() => {
                    setWeatherLoaded(false);
                    loadWeather(formData.lat, formData.lng, formData.caught_at ? new Date(formData.caught_at).toISOString().split('T')[0] : undefined, true);
                  }}
                  disabled={loadingWeather}
                >
                  <Icon name="refresh" size="sm" />
                  {loadingWeather ? 'Загружаем...' : 'Обновить погоду'}
                </button>
              )}
            </div>
            {weatherExpanded && (
              <div className="weather-grid">
              <div className="form-group">
                <label htmlFor="temperature">Температура (°C)</label>
                <input
                  type="number"
                  id="temperature"
                  name="temperature"
                  value={formData.temperature || ''}
                  onChange={handleInputChange}
                  step="0.1"
                  placeholder="20.5"
                />
              </div>

              <div className="form-group">
                <label htmlFor="pressure">Давление (мм рт.ст.)</label>
                <input
                  type="number"
                  id="pressure"
                  name="pressure"
                  value={formData.pressure || ''}
                  onChange={handleInputChange}
                  step="0.1"
                  placeholder="760"
                />
              </div>

              <div className="form-group">
                <label htmlFor="wind_speed">Скорость ветра (м/с)</label>
                <input
                  type="number"
                  id="wind_speed"
                  name="wind_speed"
                  value={formData.wind_speed || ''}
                  onChange={handleInputChange}
                  step="0.1"
                  placeholder="5.2"
                />
              </div>

              <div className="form-group">
                <label htmlFor="cloudiness">Облачность</label>
                <select
                  id="cloudiness"
                  name="cloudiness"
                  value={formData.cloudiness || ''}
                  onChange={handleInputChange}
                >
                  <option value="">Выберите облачность</option>
                  <option value="ясно">Ясно</option>
                  <option value="малооблачно">Малооблачно</option>
                  <option value="облачно">Облачно</option>
                  <option value="пасмурно">Пасмурно</option>
                </select>
              </div>

              <div className="form-group">
                <label htmlFor="precipitation">Осадки</label>
                <select
                  id="precipitation"
                  name="precipitation"
                  value={formData.precipitation || ''}
                  onChange={handleInputChange}
                >
                  <option value="">Выберите осадки</option>
                  <option value="без осадков">Без осадков</option>
                  <option value="дождь">Дождь</option>
                  <option value="снег">Снег</option>
                  <option value="град">Град</option>
                  <option value="туман">Туман</option>
                </select>
              </div>

              <div className="form-group">
                <label htmlFor="wind_direction">Направление ветра</label>
                <select
                  id="wind_direction"
                  name="wind_direction"
                  value={formData.wind_direction || ''}
                  onChange={handleInputChange}
                >
                  <option value="">Выберите направление</option>
                  <option value="С">Север</option>
                  <option value="СВ">Северо-восток</option>
                  <option value="В">Восток</option>
                  <option value="ЮВ">Юго-восток</option>
                  <option value="Ю">Юг</option>
                  <option value="ЮЗ">Юго-запад</option>
                  <option value="З">Запад</option>
                  <option value="СЗ">Северо-запад</option>
                </select>
              </div>
            </div>
            )}
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

          <div className="form-actions">
            <button type="submit" className="btn btn-primary">
              <Icon name="add" size="md" />
              Добавить улов
            </button>
          </div>
        </form>
      </div>

      {showMapSelector && (
        <MapPointSelector
          onPointSelect={handlePointSelect}
          initialPosition={{ lat: formData.lat, lng: formData.lng }}
          onClose={() => setShowMapSelector(false)}
        />
      )}
    </div>
  );
};

export default AddCatchForm;