import React, { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import AddPointForm from '../components/AddPointForm';

interface PointFormData {
  name: string;
  description: string;
  latitude: number;
  longitude: number;
  type: 'fishing' | 'weather' | 'general';
  privacy: 'all' | 'friends' | 'private';
}

const AddPointPage: React.FC = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [initialData, setInitialData] = useState<Partial<PointFormData> | null>(null);

  // Получаем данные из state (если переходим с карты)
  useEffect(() => {
    if (location.state?.coordinates) {
      setInitialData({
        latitude: location.state.coordinates.lat,
        longitude: location.state.coordinates.lng,
        name: location.state.locationName || ''
      });
    }
  }, [location.state]);

  const handleSubmit = async (data: PointFormData) => {
    try {
      setLoading(true);
      setError(null);
      
      // Здесь будет API вызов для добавления места
      console.log('Adding point:', data);
      
      // Пока что просто переходим обратно
      navigate('/map');
    } catch (err) {
      setError('Не удалось добавить место. Попробуйте еще раз.');
      console.error('Add point error:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleClose = () => {
    navigate('/map');
  };

  if (loading) {
    return (
      <div className="screen">
        <div className="loading">Добавление места...</div>
      </div>
    );
  }

  return (
    <div className="screen">
      {error && (
        <div className="error-message">
          <p>{error}</p>
        </div>
      )}
      
      <AddPointForm 
        onClose={handleClose}
        onSubmit={handleSubmit}
        initialData={initialData}
      />
    </div>
  );
};

export default AddPointPage;

