import React, { useState, useEffect } from 'react';
import { 
  getInviteCodes, 
  createInviteCode, 
  getInviteUsages 
} from '../../api';
import type { InviteCode, InviteUsage, CreateInviteCodeRequest } from '../../types';
import Icon from '../Icon';

const InviteManager: React.FC = () => {
  const [inviteCodes, setInviteCodes] = useState<InviteCode[]>([]);
  const [inviteUsages, setInviteUsages] = useState<InviteUsage[]>([]);
  const [loading, setLoading] = useState(true);
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [formData, setFormData] = useState<CreateInviteCodeRequest>({
    discount_percentage: 10,
    bonus_amount: 100,
    max_uses: undefined,
    valid_until: undefined
  });

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setLoading(true);
      const [codesResponse, usagesResponse] = await Promise.all([
        getInviteCodes(),
        getInviteUsages()
      ]);
      setInviteCodes(codesResponse.data);
      setInviteUsages(usagesResponse.data);
    } catch (error) {
      console.error('Error loading invite data:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleCreate = async () => {
    try {
      await createInviteCode(formData);
      setShowCreateModal(false);
      resetForm();
      loadData();
    } catch (error) {
      console.error('Error creating invite code:', error);
    }
  };

  const resetForm = () => {
    setFormData({
      discount_percentage: 10,
      bonus_amount: 100,
      max_uses: undefined,
      valid_until: undefined
    });
  };

  const copyInviteLink = (code: string) => {
    const inviteLink = `${window.location.origin}/register?invite=${code}`;
    navigator.clipboard.writeText(inviteLink);
    alert('Ссылка скопирована в буфер обмена!');
  };

  if (loading) {
    return <div className="loading">Загрузка инвайтов...</div>;
  }

  return (
    <div className="invite-manager">
      <div className="admin-header">
        <h2>Управление инвайтами</h2>
        <button 
          className="btn btn-primary"
          onClick={() => setShowCreateModal(true)}
        >
          <Icon name="add" size="md" />
          Создать инвайт
        </button>
      </div>

      <div className="invite-codes-list">
        {inviteCodes.map((code) => (
          <div key={code.id} className="invite-code-card">
            <div className="code-info">
              <div className="code-header">
                <h3>Инвайт код: {code.code}</h3>
                <div className={`status-badge ${code.is_active ? 'active' : 'inactive'}`}>
                  {code.is_active ? 'Активен' : 'Неактивен'}
                </div>
              </div>
              
              <div className="code-details">
                <div className="detail-item">
                  <span className="label">Скидка:</span>
                  <span className="value">{code.discount_percentage}%</span>
                </div>
                
                <div className="detail-item">
                  <span className="label">Бонус пригласившему:</span>
                  <span className="value">{code.bonus_amount} бонусов</span>
                </div>
                
                <div className="detail-item">
                  <span className="label">Использований:</span>
                  <span className="value">
                    {code.used_count}{code.max_uses ? ` / ${code.max_uses}` : ' / ∞'}
                  </span>
                </div>
                
                <div className="detail-item">
                  <span className="label">Создан:</span>
                  <span className="value">
                    {new Date(code.created_at).toLocaleDateString()}
                  </span>
                </div>
                
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
                onClick={() => copyInviteLink(code.code)}
              >
                <Icon name="content_copy" size="sm" />
                Копировать ссылку
              </button>
            </div>
          </div>
        ))}
      </div>

      {/* Invite Usages */}
      <div className="invite-usages">
        <h3>Использования инвайтов</h3>
        <div className="usages-list">
          {inviteUsages.map((usage) => (
            <div key={usage.id} className="usage-item">
              <div className="usage-info">
                <div className="user-info">
                  <span className="user-name">{usage.invitee?.name || 'Неизвестный пользователь'}</span>
                  <span className="subscription-type">{usage.subscription_type}</span>
                </div>
                <div className="usage-details">
                  <span>Скидка: {usage.discount_applied}%</span>
                  <span>Бонус: {usage.bonus_earned}</span>
                  <span>{new Date(usage.created_at).toLocaleDateString()}</span>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Create Modal */}
      {showCreateModal && (
        <div className="modal-overlay">
          <div className="modal-content">
            <div className="modal-header">
              <h3>Создать инвайт</h3>
              <button 
                className="close-button"
                onClick={() => {
                  setShowCreateModal(false);
                  resetForm();
                }}
              >
                <Icon name="close" size="md" />
              </button>
            </div>
            
            <div className="modal-body">
              <div className="form-group">
                <label>Процент скидки для приглашенного</label>
                <input
                  type="number"
                  value={formData.discount_percentage}
                  onChange={(e) => setFormData(prev => ({ 
                    ...prev, 
                    discount_percentage: Number(e.target.value) 
                  }))}
                  min="1"
                  max="100"
                />
              </div>
              
              <div className="form-group">
                <label>Количество бонусов для пригласившего</label>
                <input
                  type="number"
                  value={formData.bonus_amount}
                  onChange={(e) => setFormData(prev => ({ 
                    ...prev, 
                    bonus_amount: Number(e.target.value) 
                  }))}
                  min="1"
                />
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
              
              <div className="form-group">
                <label>Действует до</label>
                <input
                  type="datetime-local"
                  value={formData.valid_until || ''}
                  onChange={(e) => setFormData(prev => ({ ...prev, valid_until: e.target.value }))}
                />
              </div>
            </div>
            
            <div className="modal-footer">
              <button 
                className="btn btn-secondary"
                onClick={() => {
                  setShowCreateModal(false);
                  resetForm();
                }}
              >
                Отмена
              </button>
              <button 
                className="btn btn-primary"
                onClick={handleCreate}
              >
                Создать
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default InviteManager;

