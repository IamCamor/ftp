import React, { useState } from 'react';
import Icon from './Icon';

interface WeatherPointModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSave: (label: string) => void;
  coordinates: { lat: number; lng: number } | null;
}

const WeatherPointModal: React.FC<WeatherPointModalProps> = ({
  isOpen,
  onClose,
  onSave,
  coordinates
}) => {
  const [label, setLabel] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!label.trim()) return;

    setLoading(true);
    try {
      await onSave(label.trim());
      setLabel('');
    } catch (error) {
      console.error('Error saving weather point:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleClose = () => {
    setLabel('');
    onClose();
  };

  if (!isOpen) return null;

  return (
    <div className="screen">
      <div className="page-header">
        <button className="back-button" onClick={handleClose}>
          <Icon name="arrow_back" size="md" />
        </button>
        <h1>Сохранить место для погоды</h1>
      </div>

      <div className="page-content">
          {coordinates && (
            <div className="coordinates-info">
              <Icon name="place" size="sm" />
              <span>
                {coordinates.lat.toFixed(6)}, {coordinates.lng.toFixed(6)}
              </span>
            </div>
          )}

          <form onSubmit={handleSubmit}>
            <div className="form-group">
              <label htmlFor="weather-label">Название места</label>
              <input
                id="weather-label"
                type="text"
                value={label}
                onChange={(e) => setLabel(e.target.value)}
                placeholder="Введите название места"
                maxLength={191}
                required
                autoFocus
              />
            </div>

            <div className="form-actions">
              <button 
                type="submit" 
                className="btn btn-primary"
                disabled={!label.trim() || loading}
              >
                {loading ? (
                  <>
                    <Icon name="hourglass_empty" size="sm" />
                    Сохранение...
                  </>
                ) : (
                  <>
                    <Icon name="save" size="sm" />
                    Сохранить
                  </>
                )}
              </button>
            </div>
          </form>
      </div>
    </div>
  );
};

export default WeatherPointModal;
