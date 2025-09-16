import React, { useState, useEffect } from 'react';
import { getTracks, createTrack } from '../api';
import { Track } from '../types';
import ModernTrackCard from '../components/ModernTrackCard';

type TabType = 'all' | 'active' | 'completed';

const TracksPage: React.FC = () => {
  const [tracks, setTracks] = useState<Track[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showCreateForm, setShowCreateForm] = useState(false);
  const [activeTab, setActiveTab] = useState<TabType>('all');
  const [creating, setCreating] = useState(false);

  useEffect(() => {
    loadTracks();
  }, []);

  const tabs = [
    { id: 'all' as TabType, label: 'Все треки', icon: '📊' },
    { id: 'active' as TabType, label: 'Активные', icon: '🔄' },
    { id: 'completed' as TabType, label: 'Завершенные', icon: '✅' }
  ];

  const filteredTracks = tracks.filter(track => {
    switch (activeTab) {
      case 'active':
        return !track.ended_at;
      case 'completed':
        return track.ended_at;
      default:
        return true;
    }
  });

  const loadTracks = async () => {
    try {
      setLoading(true);
      const response = await getTracks();
      setTracks(response.data || []);
    } catch (err) {
      setError('Ошибка загрузки треков');
      console.error('Tracks loading error:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleCreateTrack = async (formData: any) => {
    try {
      setCreating(true);
      const response = await createTrack(formData);
      setTracks(prev => [response.data, ...prev]);
      setShowCreateForm(false);
    } catch (err) {
      setError('Ошибка создания трека');
      console.error('Track creation error:', err);
    } finally {
      setCreating(false);
    }
  };


  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">Загрузка треков...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Header with Glassmorphism */}
        <div className="glass-surface p-6 mb-8">
          <div className="flex justify-between items-center">
            <div>
              <h1 className="md3-headline-large">Треки рыбалки</h1>
              <p className="md3-body-large text-gray-600 mt-2">Длительные сессии рыбалки с автоматическим отслеживанием</p>
            </div>
            <button
              onClick={() => setShowCreateForm(true)}
              className="md3-button md3-button-filled"
            >
              Начать новый трек
            </button>
          </div>
        </div>

        {/* Tab Filter */}
        <div className="mb-8">
          <div className="md3-tabs">
            {tabs.map((tab) => (
              <button
                key={tab.id}
                className={`md3-tab ${activeTab === tab.id ? 'active' : ''}`}
                onClick={() => setActiveTab(tab.id)}
              >
                <span className="mr-2">{tab.icon}</span>
                <span>{tab.label}</span>
              </button>
            ))}
          </div>
        </div>

        {error && (
          <div className="glass-card p-4 mb-6 border-red-200 bg-red-50">
            <p className="text-red-800">{error}</p>
          </div>
        )}

        {filteredTracks.length === 0 ? (
          <div className="glass-card p-12 text-center">
            <div className="text-gray-400 mb-4">
              <svg className="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
              </svg>
            </div>
            <h3 className="md3-title-large mb-2">Нет треков</h3>
            <p className="md3-body-large text-gray-600 mb-6">Начните свой первый трек рыбалки</p>
            <button
              onClick={() => setShowCreateForm(true)}
              className="md3-button md3-button-filled"
            >
              Начать трек
            </button>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {filteredTracks.map((track) => (
              <div key={track.id} className="md3-card">
                <ModernTrackCard
                  track={track}
                />
              </div>
            ))}
          </div>
        )}

        {showCreateForm && (
          <CreateTrackModal
            onClose={() => setShowCreateForm(false)}
            onSubmit={handleCreateTrack}
            loading={creating}
          />
        )}
      </div>
    </div>
  );
};

interface CreateTrackModalProps {
  onClose: () => void;
  onSubmit: (data: any) => void;
  loading: boolean;
}

const CreateTrackModal: React.FC<CreateTrackModalProps> = ({ onClose, onSubmit, loading }) => {
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    lat: 55.7558,
    lng: 37.6176
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit(formData);
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <h2 className="text-xl font-semibold mb-4">Начать новый трек</h2>
        
        <form onSubmit={handleSubmit}>
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Название трека
              </label>
              <input
                type="text"
                value={formData.title}
                onChange={(e) => setFormData(prev => ({ ...prev, title: e.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Например: Утренняя рыбалка на Волге"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Описание
              </label>
              <textarea
                value={formData.description}
                onChange={(e) => setFormData(prev => ({ ...prev, description: e.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                rows={3}
                placeholder="Описание трека (необязательно)"
              />
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Широта
                </label>
                <input
                  type="number"
                  step="any"
                  value={formData.lat}
                  onChange={(e) => setFormData(prev => ({ ...prev, lat: parseFloat(e.target.value) }))}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Долгота
                </label>
                <input
                  type="number"
                  step="any"
                  value={formData.lng}
                  onChange={(e) => setFormData(prev => ({ ...prev, lng: parseFloat(e.target.value) }))}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
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
              {loading ? 'Создание...' : 'Начать трек'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default TracksPage;
