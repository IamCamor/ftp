import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import AddCatchForm from '../components/AddCatchForm';
import { addCatch } from '../api';

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
  lat?: number;
  lng?: number;
  // Поля погоды
  temperature?: number;
  pressure?: number;
  wind_speed?: number;
  cloudiness?: string;
  precipitation?: string;
  wind_direction?: string;
}

const AddCatchPage: React.FC = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (data: CatchFormData) => {
    try {
      setLoading(true);
      setError(null);
      
      const submitData = {
        lat: data.lat || 55.7558, // Default coordinates
        lng: data.lng || 37.6176,
        species: data.species.length > 0 ? data.species.join(', ') : undefined,
        length: data.length ? parseFloat(data.length) : undefined,
        weight: data.weight ? parseFloat(data.weight) : undefined,
        style: data.style,
        lure: data.lure,
        tackle: data.tackle,
        notes: data.notes,
        photo_url: data.photo_url,
        privacy: (data.privacy === 'private' ? 'me' : data.privacy) as 'all' | 'friends' | 'me',
        caught_at: data.caught_at,
        // Поля погоды
        temperature: data.temperature,
        pressure: data.pressure,
        wind_speed: data.wind_speed,
        cloudiness: data.cloudiness,
        precipitation: data.precipitation,
        wind_direction: data.wind_direction
      } as any;
      
      await addCatch(submitData);
      navigate('/feed');
    } catch (err) {
      setError('Не удалось добавить улов. Попробуйте еще раз.');
      console.error('Add catch error:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleClose = () => {
    navigate('/feed');
  };

  if (loading) {
    return (
      <div className="screen">
        <div className="loading">Добавление улова...</div>
      </div>
    );
  }

  return (
    <>
      {error && (
        <div className="error-message">
          <p>{error}</p>
        </div>
      )}
      
      <AddCatchForm 
        onClose={handleClose}
        onSubmit={handleSubmit}
      />
    </>
  );
};

export default AddCatchPage;
