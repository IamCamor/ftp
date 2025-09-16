import React, { useState, useEffect } from 'react';
import { 
  getPromoCodes, 
  createPromoCode, 
  updatePromoCode, 
  deletePromoCode 
} from '../../api';
import type { PromoCode, CreatePromoCodeRequest } from '../../types';
import Icon from '../Icon';

const PromoCodeManager: React.FC = () => {
  const [promoCodes, setPromoCodes] = useState<PromoCode[]>([]);
  const [loading, setLoading] = useState(true);
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [editingCode, setEditingCode] = useState<PromoCode | null>(null);
  const [formData, setFormData] = useState<CreatePromoCodeRequest>({
    code: '',
    type: 'percentage',
    value: 0,
    subscription_types: [],
    max_uses: undefined,
    valid_from: undefined,
    valid_until: undefined
  });

  useEffect(() => {
    loadPromoCodes();
  }, []);

  const loadPromoCodes = async () => {
    try {
      setLoading(true);
      const response = await getPromoCodes();
      setPromoCodes(response.data);
    } catch (error) {
      console.error('Error loading promo codes:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleCreate = async () => {
    try {
      await createPromoCode(formData);
      setShowCreateModal(false);
      resetForm();
      loadPromoCodes();
    } catch (error) {
      console.error('Error creating promo code:', error);
    }
  };

  const handleUpdate = async () => {
    if (!editingCode) return;
    
    try {
      await updatePromoCode(editingCode.id, formData);
      setEditingCode(null);
      resetForm();
      loadPromoCodes();
    } catch (error) {
      console.error('Error updating promo code:', error);
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Вы уверены, что хотите удалить этот промокод?')) return;
    
    try {
      await deletePromoCode(id);
      loadPromoCodes();
    } catch (error) {
      console.error('Error deleting promo code:', error);
    }
  };

  const resetForm = () => {
    setFormData({
      code: '',
      type: 'percentage',
      value: 0,
      subscription_types: [],
      max_uses: undefined,
      valid_from: undefined,
      valid_until: undefined
    });
  };

  const openEditModal = (code: PromoCode) => {
    setEditingCode(code);
    setFormData({
      code: code.code,
      type: code.type,
      value: code.value,
      subscription_types: code.subscription_types,
      max_uses: code.max_uses,
      valid_from: code.valid_from,
      valid_until: code.valid_until
    });
  };

  const subscriptionTypes = ['pro', 'premium', 'guide'];

  if (loading) {
    return <div className="loading">Загрузка промокодов...</div>;
  }

  return (
    <div className="promo-code-manager">
      <div className="admin-header">
        <h2>Управление промокодами</h2>
        <button 
          className="btn btn-primary"
          onClick={() => setShowCreateModal(true)}
        >
          <Icon name="add" size="md" />
          Создать промокод
        </button>
      </div>

      <div className="promo-codes-list">
        {promoCodes.map((code) => (
          <div key={code.id} className="promo-code-card">
            <div className="code-info">
              <div className="code-header">
                <h3>{code.code}</h3>
                <div className={`status-badge ${code.is_active ? 'active' : 'inactive'}`}>
                  {code.is_active ? 'Активен' : 'Неактивен'}
                </div>
              </div>
              
              <div className="code-details">
                <div className="detail-item">
                  <span className="label">Тип:</span>
                  <span className="value">
                    {code.type === 'percentage' ? 'Процент' : 'Фиксированная сумма'}
                  </span>
                </div>
                
                <div className="detail-item">
                  <span className="label">Значение:</span>
                  <span className="value">
                    {code.type === 'percentage' ? `${code.value}%` : `${code.value} ₽`}
                  </span>
                </div>
                
                <div className="detail-item">
                  <span className="label">Типы подписок:</span>
                  <span className="value">
                    {code.subscription_types.join(', ')}
                  </span>
                </div>
                
                <div className="detail-item">
                  <span className="label">Использований:</span>
                  <span className="value">
                    {code.used_count}{code.max_uses ? ` / ${code.max_uses}` : ' / ∞'}
                  </span>
                </div>
                
                {code.valid_from && (
                  <div className="detail-item">
                    <span className="label">Действует с:</span>
                    <span className="value">
                      {new Date(code.valid_from).toLocaleDateString()}
                    </span>
                  </div>
                )}
                
                {code.valid_until && (
                  <div className="detail-item">
                    <span className="label">Действует до:</span>
                    <span className="value">
                      {new Date(code.valid_until).toLocaleDateString()}
                    </span>
                  </div>
                )}
              </div>
            </div>
            
            <div className="code-actions">
              <button 
                className="btn btn-secondary"
                onClick={() => openEditModal(code)}
              >
                <Icon name="edit" size="sm" />
                Редактировать
              </button>
              <button 
                className="btn btn-danger"
                onClick={() => handleDelete(code.id)}
              >
                <Icon name="delete" size="sm" />
                Удалить
              </button>
            </div>
          </div>
        ))}
      </div>

      {/* Create/Edit Modal */}
      {(showCreateModal || editingCode) && (
        <div className="modal-overlay">
          <div className="modal-content">
            <div className="modal-header">
              <h3>{editingCode ? 'Редактировать промокод' : 'Создать промокод'}</h3>
              <button 
                className="close-button"
                onClick={() => {
                  setShowCreateModal(false);
                  setEditingCode(null);
                  resetForm();
                }}
              >
                <Icon name="close" size="md" />
              </button>
            </div>
            
            <div className="modal-body">
              <div className="form-group">
                <label>Код промокода</label>
                <input
                  type="text"
                  value={formData.code}
                  onChange={(e) => setFormData(prev => ({ ...prev, code: e.target.value }))}
                  placeholder="Введите код промокода"
                />
              </div>
              
              <div className="form-group">
                <label>Тип скидки</label>
                <select
                  value={formData.type}
                  onChange={(e) => setFormData(prev => ({ 
                    ...prev, 
                    type: e.target.value as 'percentage' | 'fixed_amount' 
                  }))}
                >
                  <option value="percentage">Процент</option>
                  <option value="fixed_amount">Фиксированная сумма</option>
                </select>
              </div>
              
              <div className="form-group">
                <label>Значение скидки</label>
                <input
                  type="number"
                  value={formData.value}
                  onChange={(e) => setFormData(prev => ({ ...prev, value: Number(e.target.value) }))}
                  placeholder={formData.type === 'percentage' ? 'Процент' : 'Сумма в рублях'}
                />
              </div>
              
              <div className="form-group">
                <label>Типы подписок</label>
                <div className="checkbox-group">
                  {subscriptionTypes.map((type) => (
                    <label key={type} className="checkbox-label">
                      <input
                        type="checkbox"
                        checked={formData.subscription_types.includes(type)}
                        onChange={(e) => {
                          if (e.target.checked) {
                            setFormData(prev => ({
                              ...prev,
                              subscription_types: [...prev.subscription_types, type]
                            }));
                          } else {
                            setFormData(prev => ({
                              ...prev,
                              subscription_types: prev.subscription_types.filter(t => t !== type)
                            }));
                          }
                        }}
                      />
                      <span className="checkbox-custom"></span>
                      {type === 'pro' ? 'Pro' : type === 'premium' ? 'Premium' : 'Гид'}
                    </label>
                  ))}
                </div>
              </div>
              
              <div className="form-group">
                <label>Максимум использований (оставьте пустым для неограниченного)</label>
                <input
                  type="number"
                  value={formData.max_uses || ''}
                  onChange={(e) => setFormData(prev => ({ 
                    ...prev, 
                    max_uses: e.target.value ? Number(e.target.value) : undefined 
                  }))}
                  placeholder="Неограниченно"
                />
              </div>
              
              <div className="form-row">
                <div className="form-group">
                  <label>Действует с</label>
                  <input
                    type="datetime-local"
                    value={formData.valid_from || ''}
                    onChange={(e) => setFormData(prev => ({ ...prev, valid_from: e.target.value }))}
                  />
                </div>
                
                <div className="form-group">
                  <label>Действует до</label>
                  <input
                    type="datetime-local"
                    value={formData.valid_until || ''}
                    onChange={(e) => setFormData(prev => ({ ...prev, valid_until: e.target.value }))}
                  />
                </div>
              </div>
            </div>
            
            <div className="modal-footer">
              <button 
                className="btn btn-secondary"
                onClick={() => {
                  setShowCreateModal(false);
                  setEditingCode(null);
                  resetForm();
                }}
              >
                Отмена
              </button>
              <button 
                className="btn btn-primary"
                onClick={editingCode ? handleUpdate : handleCreate}
              >
                {editingCode ? 'Сохранить' : 'Создать'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default PromoCodeManager;

