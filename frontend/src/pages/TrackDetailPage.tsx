import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { getTrack, updateTrack, addCatchToTrack } from '../api';
import { Track, CatchRecord } from '../types';
import TrackMap from '../components/TrackMap';
import { useTrackLocation } from '../hooks/useTrackLocation';

const TrackDetailPage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [track, setTrack] = useState<Track | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showAddCatchForm, setShowAddCatchForm] = useState(false);
  const [addingCatch, setAddingCatch] = useState(false);
  const [isLocationTrackingEnabled, setIsLocationTrackingEnabled] = useState(false);

  // Хук для автоматического отслеживания местоположения
  const {
    isTracking,
    lastUpdate,
    locationCount,
    error: locationError,
    currentLocation,
    accuracy
  } = useTrackLocation({
    trackId: track?.id || 0,
    interval: 5 * 60 * 1000, // 5 минут
    enabled: isLocationTrackingEnabled && track?.status === 'active',
    onLocationUpdate: (lat, lng) => {
      console.log(`Location updated: ${lat}, ${lng}`);
      // Обновляем трек в реальном времени
      if (track) {
        setTrack(prev => prev ? {
          ...prev,
          track_points: [
            ...prev.track_points,
            {
              lat,
              lng,
              timestamp: new Date().toISOString(),
              smartwatch_data: {
                heart_rate: Math.floor(Math.random() * 40) + 60, // Моковые данные
                steps: Math.floor(Math.random() * 100),
                calories: Math.floor(Math.random() * 50)
              }
            }
          ]
        } : null);
      }
    },
    onError: (error) => {
      console.error('Location tracking error:', error);
      setError(`Ошибка отслеживания: ${error}`);
    }
  });

  useEffect(() => {
    if (id) {
      loadTrack(parseInt(id));
    }
  }, [id]);

  const loadTrack = async (trackId: number) => {
    try {
      setLoading(true);
      const response = await getTrack(trackId);
      setTrack(response.data);
    } catch (err) {
      setError('Ошибка загрузки трека');
      console.error('Track loading error:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleUpdateTrack = async (action: string, data?: any) => {
    if (!track) return;

    try {
      const response = await updateTrack(track.id, { action, ...data });
      setTrack(response.data);
    } catch (err) {
      setError('Ошибка обновления трека');
      console.error('Track update error:', err);
    }
  };

  const handleAddCatch = async (catchData: any) => {
    if (!track) return;

    try {
      setAddingCatch(true);
      const response = await addCatchToTrack(track.id, catchData);
      setTrack(prev => prev ? {
        ...prev,
        catches: [response.data, ...prev.catches],
        total_catches: prev.total_catches + 1,
        total_weight: (prev.total_weight || 0) + (response.data.weight || 0)
      } : null);
      setShowAddCatchForm(false);
    } catch (err) {
      setError('Ошибка добавления улова');
      console.error('Add catch error:', err);
    } finally {
      setAddingCatch(false);
    }
  };

  const formatDuration = (minutes: number | null) => {
    if (!minutes) return 'В процессе';
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return hours > 0 ? `${hours}ч ${mins}м` : `${mins}м`;
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active': return 'text-green-600 bg-green-100';
      case 'paused': return 'text-yellow-600 bg-yellow-100';
      case 'completed': return 'text-gray-600 bg-gray-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'active': return 'Активен';
      case 'paused': return 'Приостановлен';
      case 'completed': return 'Завершен';
      default: return status;
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'active': return '🔄';
      case 'paused': return '⏸️';
      case 'completed': return '✅';
      default: return '❓';
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">Загрузка трека...</p>
        </div>
      </div>
    );
  }

  if (error || !track) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <p className="text-red-600 mb-4">{error || 'Трек не найден'}</p>
          <button
            onClick={() => navigate('/tracks')}
            className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
          >
            Вернуться к трекам
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Header */}
        <div className="glass-surface p-6 mb-8">
          <div className="flex justify-between items-start">
            <div>
              <h1 className="md3-headline-large">{track.name || track.title}</h1>
              {track.description && (
                <p className="md3-body-large text-gray-600 mt-2">{track.description}</p>
              )}
            </div>
            <div className="flex items-center space-x-3">
              <div className={`md3-chip ${getStatusColor(track.status)}`}>
                {getStatusIcon(track.status)}
                <span className="ml-1">{getStatusText(track.status)}</span>
              </div>
              {track.status === 'active' && (
                <button
                  onClick={() => handleUpdateTrack('pause')}
                  className="md3-button md3-button-outlined"
                >
                  Приостановить
                </button>
              )}
              {track.status === 'paused' && (
                <button
                  onClick={() => handleUpdateTrack('resume')}
                  className="md3-button md3-button-filled"
                >
                  Продолжить
                </button>
              )}
              {track.status === 'active' && (
                <button
                  onClick={() => handleUpdateTrack('complete')}
                  className="md3-button md3-button-filled bg-red-600 hover:bg-red-700"
                >
                  Завершить
                </button>
              )}
            </div>
          </div>
        </div>

        {error && (
          <div className="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <p className="text-red-800">{error}</p>
          </div>
        )}

        {/* Track Map - в верхней части */}
        {track.track_points && track.track_points.length > 0 && (
          <div className="mb-8">
            <div className="bg-white rounded-lg shadow-md p-6">
              <h2 className="text-lg font-semibold text-gray-900 mb-4">Карта трека</h2>
              <TrackMap track={track} className="h-96" />
            </div>
          </div>
        )}

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Track Info */}
          <div className="lg:col-span-1">
            <div className="bg-white rounded-lg shadow-md p-6 mb-6">
              <h2 className="text-lg font-semibold text-gray-900 mb-4">Информация о треке</h2>
              <div className="space-y-3 text-sm">
                <div className="flex justify-between">
                  <span className="text-gray-600">Начало:</span>
                  <span>{new Date(track.started_at).toLocaleString('ru-RU')}</span>
                </div>
                {track.ended_at && (
                  <div className="flex justify-between">
                    <span className="text-gray-600">Окончание:</span>
                    <span>{new Date(track.ended_at).toLocaleString('ru-RU')}</span>
                  </div>
                )}
                <div className="flex justify-between">
                  <span className="text-gray-600">Длительность:</span>
                  <span>{formatDuration(track.duration_minutes || 0)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Дистанция:</span>
                  <span>{track.total_distance} км</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Уловов:</span>
                  <span>{track.total_catches}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Общий вес:</span>
                  <span>{track.total_weight} кг</span>
                </div>
                {track.average_weight && (
                  <div className="flex justify-between">
                    <span className="text-gray-600">Средний вес:</span>
                    <span>{track.average_weight} кг</span>
                  </div>
                )}
              </div>
            </div>

            {/* Location Tracking Controls */}
            {track.status === 'active' && (
              <div className="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">Отслеживание местоположения</h3>
                
                <div className="space-y-4">
                  <div className="flex items-center justify-between">
                    <span className="text-sm text-gray-600">Автоматическое отслеживание</span>
                    <button
                      onClick={() => setIsLocationTrackingEnabled(!isLocationTrackingEnabled)}
                      className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors ${
                        isLocationTrackingEnabled ? 'bg-green-600' : 'bg-gray-200'
                      }`}
                    >
                      <span
                        className={`inline-block h-4 w-4 transform rounded-full bg-white transition-transform ${
                          isLocationTrackingEnabled ? 'translate-x-6' : 'translate-x-1'
                        }`}
                      />
                    </button>
                  </div>

                  {isLocationTrackingEnabled && (
                    <div className="text-sm text-gray-600 space-y-1">
                      <div className="flex justify-between">
                        <span>Статус:</span>
                        <span className={isTracking ? 'text-green-600' : 'text-red-600'}>
                          {isTracking ? 'Отслеживается' : 'Остановлено'}
                        </span>
                      </div>
                      <div className="flex justify-between">
                        <span>Точек записано:</span>
                        <span>{locationCount}</span>
                      </div>
                      {lastUpdate && (
                        <div className="flex justify-between">
                          <span>Последнее обновление:</span>
                          <span>{lastUpdate.toLocaleTimeString('ru-RU')}</span>
                        </div>
                      )}
                      {currentLocation && (
                        <div className="flex justify-between">
                          <span>Текущее местоположение:</span>
                          <span className="text-xs">
                            {currentLocation.lat.toFixed(6)}, {currentLocation.lng.toFixed(6)}
                          </span>
                        </div>
                      )}
                      {accuracy && (
                        <div className="flex justify-between">
                          <span>Точность:</span>
                          <span>{Math.round(accuracy)}м</span>
                        </div>
                      )}
                    </div>
                  )}

                  {locationError && (
                    <div className="text-sm text-red-600 bg-red-50 p-2 rounded">
                      {locationError}
                    </div>
                  )}
                </div>
              </div>
            )}

            {/* Quick Add Catch Button */}
            {track.status === 'active' && (
              <div className="bg-white rounded-lg shadow-md p-6">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">Быстрое добавление улова</h3>
                <button
                  onClick={() => setShowAddCatchForm(true)}
                  className="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors"
                >
                  Добавить улов
                </button>
              </div>
            )}
          </div>

          {/* Catches List */}
          <div className="lg:col-span-2">
            <div className="bg-white rounded-lg shadow-md p-6">
              <h2 className="text-lg font-semibold text-gray-900 mb-4">Уловы ({track.catches.length})</h2>
              
              {track.catches.length === 0 ? (
                <div className="text-center py-8 text-gray-500">
                  <p>Пока нет уловов в этом треке</p>
                </div>
              ) : (
                <div className="space-y-4">
                  {track.catches.map((catchRecord) => (
                    <div 
                      key={catchRecord.id} 
                      className="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
                      onClick={() => navigate(`/catch/${catchRecord.id}`)}
                    >
                      <div className="flex justify-between items-start mb-2">
                        <h3 className="font-semibold text-gray-900">{catchRecord.species}</h3>
                        <span className="text-sm text-gray-500">
                          {catchRecord.caught_at ? new Date(catchRecord.caught_at).toLocaleString('ru-RU') : 'Не указано'}
                        </span>
                      </div>
                      
                      {/* Картинка улова */}
                      {catchRecord.photo_url && (
                        <div className="mb-3">
                          <img 
                            src={catchRecord.photo_url} 
                            alt={catchRecord.species}
                            className="w-full h-48 object-cover rounded-lg"
                          />
                        </div>
                      )}
                      
                      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600">
                        <div>
                          <span className="font-medium">Вес:</span> {catchRecord.weight} кг
                        </div>
                        <div>
                          <span className="font-medium">Длина:</span> {catchRecord.length} см
                        </div>
                        {catchRecord.style && (
                          <div>
                            <span className="font-medium">Стиль:</span> {catchRecord.style}
                          </div>
                        )}
                        {catchRecord.lure && (
                          <div>
                            <span className="font-medium">Приманка:</span> {catchRecord.lure}
                          </div>
                        )}
                        {catchRecord.tackle && (
                          <div>
                            <span className="font-medium">Снасть:</span> {catchRecord.tackle}
                          </div>
                        )}
                      </div>
                      
                      {/* Погодные данные */}
                      {catchRecord.weather && (
                        <div className="mt-3 p-3 bg-gray-50 rounded-lg">
                          <h4 className="text-sm font-medium text-gray-700 mb-2">Погодные условия</h4>
                          <div className="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs text-gray-600">
                            {catchRecord.weather.temperature && (
                              <div>🌡️ {catchRecord.weather.temperature}°C</div>
                            )}
                            {catchRecord.weather.pressure && (
                              <div>📊 {catchRecord.weather.pressure} мм рт.ст.</div>
                            )}
                            {catchRecord.weather.cloudiness && (
                              <div>☁️ {catchRecord.weather.cloudiness}</div>
                            )}
                            {catchRecord.weather.wind_speed && (
                              <div>💨 {catchRecord.weather.wind_speed} м/с</div>
                            )}
                            {catchRecord.weather.precipitation && (
                              <div>🌧️ {catchRecord.weather.precipitation}</div>
                            )}
                            {catchRecord.weather.wind_direction && (
                              <div>🧭 {catchRecord.weather.wind_direction}</div>
                            )}
                          </div>
                        </div>
                      )}
                      
                      {catchRecord.notes && (
                        <p className="mt-2 text-sm text-gray-600">{catchRecord.notes}</p>
                      )}
                      
                      <div className="mt-3 text-xs text-blue-600">
                        Нажмите для просмотра подробностей →
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Add Catch Modal */}
        {showAddCatchForm && (
          <AddCatchModal
            onClose={() => setShowAddCatchForm(false)}
            onSubmit={handleAddCatch}
            loading={addingCatch}
            lastCatch={track.catches[0]}
          />
        )}
      </div>
    </div>
  );
};

interface AddCatchModalProps {
  onClose: () => void;
  onSubmit: (data: any) => void;
  loading: boolean;
  lastCatch?: CatchRecord;
}

const AddCatchModal: React.FC<AddCatchModalProps> = ({ onClose, onSubmit, loading, lastCatch }) => {
  const [formData, setFormData] = useState({
    species: '',
    weight: '',
    length: '',
    style: lastCatch?.style || '',
    lure: lastCatch?.lure || '',
    tackle: lastCatch?.tackle || '',
    notes: ''
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit({
      ...formData,
      weight: parseFloat(formData.weight),
      length: parseFloat(formData.length)
    });
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <h2 className="text-xl font-semibold mb-4">Добавить улов</h2>
        
        <form onSubmit={handleSubmit}>
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Вид рыбы *
              </label>
              <input
                type="text"
                value={formData.species}
                onChange={(e) => setFormData(prev => ({ ...prev, species: e.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Например: Щука"
                required
              />
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Вес (кг) *
                </label>
                <input
                  type="number"
                  step="0.1"
                  value={formData.weight}
                  onChange={(e) => setFormData(prev => ({ ...prev, weight: e.target.value }))}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  required
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Длина (см) *
                </label>
                <input
                  type="number"
                  step="0.1"
                  value={formData.length}
                  onChange={(e) => setFormData(prev => ({ ...prev, length: e.target.value }))}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  required
                />
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Стиль ловли
              </label>
              <input
                type="text"
                value={formData.style}
                onChange={(e) => setFormData(prev => ({ ...prev, style: e.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Например: спиннинг"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Приманка
              </label>
              <input
                type="text"
                value={formData.lure}
                onChange={(e) => setFormData(prev => ({ ...prev, lure: e.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Например: воблер"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Снасть
              </label>
              <input
                type="text"
                value={formData.tackle}
                onChange={(e) => setFormData(prev => ({ ...prev, tackle: e.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Например: Shimano"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Заметки
              </label>
              <textarea
                value={formData.notes}
                onChange={(e) => setFormData(prev => ({ ...prev, notes: e.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                rows={3}
                placeholder="Дополнительные заметки"
              />
            </div>
          </div>

          <div className="flex justify-end space-x-3 mt-6">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors"
            >
              Отмена
            </button>
            <button
              type="submit"
              disabled={loading}
              className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
              {loading ? 'Добавление...' : 'Добавить улов'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default TrackDetailPage;
