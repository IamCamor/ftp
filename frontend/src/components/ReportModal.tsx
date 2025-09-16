import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';

interface ReportModalProps {
  isOpen: boolean;
  onClose: () => void;
  type: 'catch' | 'place' | 'user';
  targetId: number;
  targetName: string;
}

const REPORT_REASONS = {
  catch: [
    'Неподходящий контент',
    'Спам',
    'Неточная информация',
    'Нарушение правил',
    'Другое'
  ],
  place: [
    'Неточная информация о месте',
    'Место не существует',
    'Опасное место',
    'Нарушение правил',
    'Другое'
  ],
  user: [
    'Спам',
    'Неподходящий контент',
    'Нарушение правил',
    'Подозрительная активность',
    'Другое'
  ]
};

export const ReportModal: React.FC<ReportModalProps> = ({
  isOpen,
  onClose,
  type,
  targetId,
  targetName
}) => {
  const navigate = useNavigate();
  const [reason, setReason] = useState('');
  const [description, setDescription] = useState('');
  const [success, setSuccess] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onClose();
    navigate('/report', {
      state: {
        entityType: type,
        entityId: targetId.toString(),
        entityTitle: targetName
      }
    });
  };

  const handleClose = () => {
    onClose();
    setReason('');
    setDescription('');
    setSuccess(false);
  };

  if (!isOpen) return null;

  const reasons = REPORT_REASONS[type];
  const typeLabels = {
    catch: 'улов',
    place: 'место',
    user: 'пользователь'
  };

  return (
    <div className="modal-overlay" onClick={handleClose}>
      <div className="modal-content report-modal" onClick={(e) => e.stopPropagation()}>
        <div className="modal-header">
          <h3>Пожаловаться на {typeLabels[type]}</h3>
          <button 
            className="modal-close" 
            onClick={handleClose}
            disabled={false}
          >
            ×
          </button>
        </div>

        {success ? (
          <div className="report-success">
            <div className="success-icon">✓</div>
            <p>Жалоба отправлена успешно!</p>
            <p>Мы рассмотрим вашу жалобу в ближайшее время.</p>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="report-form">
            <div className="report-target">
              <strong>Объект жалобы:</strong> {targetName}
            </div>

            <div className="form-group">
              <label htmlFor="reason">Причина жалобы *</label>
              <select
                id="reason"
                value={reason}
                onChange={(e) => setReason(e.target.value)}
                required
                disabled={false}
              >
                <option value="">Выберите причину</option>
                {reasons.map((reasonOption) => (
                  <option key={reasonOption} value={reasonOption}>
                    {reasonOption}
                  </option>
                ))}
              </select>
            </div>

            <div className="form-group">
              <label htmlFor="description">Дополнительная информация</label>
              <textarea
                id="description"
                value={description}
                onChange={(e) => setDescription(e.target.value)}
                placeholder="Опишите подробнее причину жалобы (необязательно)"
                rows={4}
                maxLength={500}
                disabled={false}
              />
              <div className="char-count">
                {description.length}/500
              </div>
            </div>

            <div className="form-actions">
              <button
                type="button"
                onClick={handleClose}
                className="btn btn-secondary"
                disabled={false}
              >
                Отмена
              </button>
              <button
                type="submit"
                className="btn btn-primary"
                disabled={!reason}
              >
                Отправить жалобу
              </button>
            </div>
          </form>
        )}
      </div>
    </div>
  );
};

