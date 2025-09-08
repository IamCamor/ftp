import React, { useState } from 'react';
import Icon from '../Icon';

interface BlockModalProps {
  isOpen: boolean;
  onClose: () => void;
  onConfirm: (reason: string) => void;
  title: string;
  itemName: string;
  isBlocked: boolean;
}

const BlockModal: React.FC<BlockModalProps> = ({
  isOpen,
  onClose,
  onConfirm,
  title,
  itemName,
  isBlocked
}) => {
  const [reason, setReason] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (isBlocked || reason.trim()) {
      onConfirm(reason);
      setReason('');
    }
  };

  if (!isOpen) return null;

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal" onClick={(e) => e.stopPropagation()}>
        <div className="modal__header">
          <h3>{title}</h3>
          <button className="modal__close" onClick={onClose}>
            <Icon name="close" />
          </button>
        </div>
        
        <form onSubmit={handleSubmit} className="modal__content">
          <p>
            {isBlocked 
              ? `Разблокировать ${itemName}?`
              : `Заблокировать ${itemName}?`
            }
          </p>
          
          {!isBlocked && (
            <div className="form-group">
              <label htmlFor="reason">Причина блокировки:</label>
              <textarea
                id="reason"
                value={reason}
                onChange={(e) => setReason(e.target.value)}
                placeholder="Укажите причину блокировки..."
                rows={3}
                required
              />
            </div>
          )}
          
          <div className="modal__actions">
            <button type="button" className="btn btn--secondary" onClick={onClose}>
              Отмена
            </button>
            <button 
              type="submit" 
              className={`btn ${isBlocked ? 'btn--success' : 'btn--danger'}`}
            >
              {isBlocked ? 'Разблокировать' : 'Заблокировать'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default BlockModal;
