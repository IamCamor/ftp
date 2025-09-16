import React, { useState, useEffect } from 'react';
import Icon from './Icon';
import WeatherMapSelector from './WeatherMapSelector';
import { getWeatherPoints, addWeatherPoint, updateWeatherPoint, deleteWeatherPoint } from '../api';
import type { WeatherPoint, AddWeatherPointRequest } from '../types';

interface WeatherPointManagerProps {
  onPointSelect: (point: WeatherPoint) => void;
  onPointsChange: (points: WeatherPoint[]) => void;
}

const WeatherPointManager: React.FC<WeatherPointManagerProps> = ({
  onPointSelect,
  onPointsChange
}) => {
  const [points, setPoints] = useState<WeatherPoint[]>([]);
  const [loading, setLoading] = useState(true);
  const [showAddForm, setShowAddForm] = useState(false);
  const [showMapSelector, setShowMapSelector] = useState(false);
  const [editingPoint, setEditingPoint] = useState<WeatherPoint | null>(null);
  const [formData, setFormData] = useState<AddWeatherPointRequest>({
    name: '',
    lat: 0,
    lng: 0,
    city: '',
    country: ''
  });

  useEffect(() => {
    loadPoints();
  }, []);

  const loadPoints = async () => {
    try {
      setLoading(true);
      console.log('WeatherPointManager: Loading points...');
      const response = await getWeatherPoints();
      console.log('WeatherPointManager: Response:', response);
      setPoints(response.data);
      onPointsChange(response.data);
      console.log('WeatherPointManager: Points loaded:', response.data);
    } catch (error) {
      console.error('WeatherPointManager: Error loading weather points:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleAddPoint = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const response = await addWeatherPoint(formData);
      const newPoints = [...points, response.data];
      setPoints(newPoints);
      onPointsChange(newPoints);
      setShowAddForm(false);
      setFormData({ name: '', lat: 0, lng: 0, city: '', country: '' });
    } catch (error) {
      console.error('Error adding weather point:', error);
    }
  };

  const handleEditPoint = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingPoint) return;

    try {
      const response = await updateWeatherPoint(editingPoint.id, formData);
      const updatedPoints = points.map(p => p.id === editingPoint.id ? response.data : p);
      setPoints(updatedPoints);
      onPointsChange(updatedPoints);
      setEditingPoint(null);
      setFormData({ name: '', lat: 0, lng: 0, city: '', country: '' });
    } catch (error) {
      console.error('Error updating weather point:', error);
    }
  };

  const handleDeletePoint = async (id: number) => {
    try {
      await deleteWeatherPoint(id);
      const updatedPoints = points.filter(p => p.id !== id);
      setPoints(updatedPoints);
      onPointsChange(updatedPoints);
    } catch (error) {
      console.error('Error deleting weather point:', error);
    }
  };

  const startEdit = (point: WeatherPoint) => {
    setEditingPoint(point);
    setFormData({
      name: point.name,
      lat: point.lat,
      lng: point.lng,
      city: point.city || '',
      country: point.country || ''
    });
    setShowAddForm(true);
  };

  const cancelEdit = () => {
    setEditingPoint(null);
    setShowAddForm(false);
    setFormData({ name: '', lat: 0, lng: 0, city: '', country: '' });
  };

  const handleMapSelection = () => {
    setShowMapSelector(true);
  };

  const handleCoordinatesSelect = (lat: number, lng: number, locationName: string) => {
    setFormData(prev => ({
      ...prev,
      lat,
      lng,
      city: locationName.split(',')[0] || '',
      country: locationName.split(',')[1]?.trim() || ''
    }));
    setShowMapSelector(false);
  };

  const handleMapClose = () => {
    setShowMapSelector(false);
  };

  if (loading) {
    return (
      <div className="weather-points-loading">
        <Icon name="refresh" size="md" />
        <span>Загрузка точек погоды...</span>
      </div>
    );
  }

  return (
    <div className="weather-point-manager">
      <div className="weather-points-header">
        <h3>Мои точки погоды</h3>
        {points.length < 5 && (
          <button
            className="btn btn--sm btn-primary"
            onClick={() => setShowAddForm(true)}
          >
            <Icon name="add" size="sm" />
            Добавить точку
          </button>
        )}
      </div>

      {points.length === 0 ? (
        <div className="weather-points-empty">
          <Icon name="location_on" size="xl" />
          <p>У вас пока нет сохраненных точек погоды</p>
          <button
            className="btn btn-primary"
            onClick={() => setShowAddForm(true)}
          >
            <Icon name="add" size="sm" />
            Добавить первую точку
          </button>
        </div>
      ) : (
        <div className="weather-points-list">
          {points.map((point) => (
            <div key={point.id} className="weather-point-item">
              <div className="point-info" onClick={() => onPointSelect(point)}>
                <Icon name="location_on" size="sm" />
                <div className="point-details">
                  <h4>{point.name}</h4>
                  <p>{point.city && point.country ? `${point.city}, ${point.country}` : `${point.lat.toFixed(4)}, ${point.lng.toFixed(4)}`}</p>
                </div>
              </div>
              <div className="point-actions">
                <button
                  className="action-button"
                  onClick={() => startEdit(point)}
                  title="Редактировать"
                >
                  <Icon name="edit" size="sm" />
                </button>
                <button
                  className="action-button"
                  onClick={() => handleDeletePoint(point.id)}
                  title="Удалить"
                >
                  <Icon name="delete" size="sm" />
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      {showAddForm && (
        <div className="weather-point-modal">
          <div className="modal-content">
            <div className="modal-header">
              <h3>{editingPoint ? 'Редактировать точку' : 'Добавить точку погоды'}</h3>
              <button className="action-button" onClick={cancelEdit}>
                <Icon name="close" size="sm" />
              </button>
            </div>
            <form onSubmit={editingPoint ? handleEditPoint : handleAddPoint}>
              <div className="form-group">
                <label className="form-label">Название точки *</label>
                <input
                  type="text"
                  className="form-input"
                  value={formData.name}
                  onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                  placeholder="Например: Дом, Рыбалка на Волге"
                  required
                />
              </div>
              <div className="form-group">
                <label className="form-label">Город</label>
                <input
                  type="text"
                  className="form-input"
                  value={formData.city}
                  onChange={(e) => setFormData({ ...formData, city: e.target.value })}
                  placeholder="Москва"
                />
              </div>
              <div className="form-group">
                <label className="form-label">Страна</label>
                <input
                  type="text"
                  className="form-input"
                  value={formData.country}
                  onChange={(e) => setFormData({ ...formData, country: e.target.value })}
                  placeholder="Россия"
                />
              </div>

              <div className="coordinates-section">
                <div className="coordinates-header">
                  <Icon name="place" size="sm" />
                  <span>Координаты</span>
                </div>
                
                <div className="coordinates-display">
                  <div className="coords-row">
                    <span className="coord-label">Широта:</span>
                    <span className="coord-value">{formData.lat.toFixed(6)}</span>
                  </div>
                  <div className="coords-row">
                    <span className="coord-label">Долгота:</span>
                    <span className="coord-value">{formData.lng.toFixed(6)}</span>
                  </div>
                </div>

                <button
                  type="button"
                  className="btn btn-outline btn-sm"
                  onClick={handleMapSelection}
                >
                  <Icon name="map" size="sm" />
                  Выбрать на карте
                </button>
              </div>

              <div className="form-actions">
                <button type="button" className="btn btn-secondary" onClick={cancelEdit}>
                  Отмена
                </button>
                <button type="submit" className="btn btn-primary">
                  {editingPoint ? 'Сохранить' : 'Добавить'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {showMapSelector && (
        <div className="weather-map-modal">
          <WeatherMapSelector
            onCoordinatesSelect={handleCoordinatesSelect}
            onClose={handleMapClose}
            initialCoordinates={{ lat: formData.lat || 55.7558, lng: formData.lng || 37.6176 }}
          />
        </div>
      )}
    </div>
  );
};

export default WeatherPointManager;
