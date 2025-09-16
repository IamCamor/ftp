import React, { useState, useEffect } from 'react';
import Icon from './Icon';
import { getPrivacySettings, updatePrivacySettings, type PrivacySettings } from '../api';

const PrivacySettings: React.FC = () => {
  const [settings, setSettings] = useState<PrivacySettings>({
    allow_friend_requests: true,
    allow_follow_notifications: true,
    show_online_status: true,
    show_last_seen: true
  });
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);

  useEffect(() => {
    loadSettings();
  }, []);

  const loadSettings = async () => {
    try {
      setLoading(true);
      const response = await getPrivacySettings();
      setSettings(response.data);
    } catch (err) {
      console.error('Failed to load privacy settings:', err);
      setError('Не удалось загрузить настройки приватности');
    } finally {
      setLoading(false);
    }
  };

  const handleSettingChange = async (key: keyof PrivacySettings, value: boolean) => {
    try {
      setSaving(true);
      setError(null);
      setSuccess(null);

      const response = await updatePrivacySettings({ [key]: value });
      
      setSettings(response.data);
      setSuccess('Настройки сохранены');
      
      // Скрываем сообщение об успехе через 3 секунды
      setTimeout(() => setSuccess(null), 3000);
    } catch (err: any) {
      console.error('Failed to update privacy settings:', err);
      setError(err.message || 'Не удалось сохранить настройки');
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="privacy-settings">
        <div className="loading">Загрузка настроек...</div>
      </div>
    );
  }

  return (
    <div className="privacy-settings">
      <div className="settings-header">
        <h2>Настройки приватности</h2>
        <p>Управляйте тем, кто может добавлять вас в друзья и присылать уведомления</p>
      </div>

      {error && (
        <div className="error-message">
          <Icon name="error" size="sm" />
          <span>{error}</span>
        </div>
      )}

      {success && (
        <div className="success-message">
          <Icon name="check_circle" size="sm" />
          <span>{success}</span>
        </div>
      )}

      <div className="settings-section">
        <h3>Друзья и подписки</h3>
        
        <div className="setting-item">
          <div className="setting-info">
            <h4>Разрешить добавлять в друзья</h4>
            <p>Другие пользователи смогут отправлять вам запросы на добавление в друзья</p>
          </div>
          <label className="toggle-switch">
            <input
              type="checkbox"
              checked={settings.allow_friend_requests}
              onChange={(e) => handleSettingChange('allow_friend_requests', e.target.checked)}
              disabled={saving}
            />
            <span className="toggle-slider"></span>
          </label>
        </div>

        <div className="setting-item">
          <div className="setting-info">
            <h4>Уведомления о подписках</h4>
            <p>Получать уведомления когда кто-то подписывается на вас</p>
          </div>
          <label className="toggle-switch">
            <input
              type="checkbox"
              checked={settings.allow_follow_notifications}
              onChange={(e) => handleSettingChange('allow_follow_notifications', e.target.checked)}
              disabled={saving}
            />
            <span className="toggle-slider"></span>
          </label>
        </div>
      </div>

      <div className="settings-section">
        <h3>Статус и активность</h3>
        
        <div className="setting-item">
          <div className="setting-info">
            <h4>Показывать статус онлайн</h4>
            <p>Другие пользователи смогут видеть, что вы в сети</p>
          </div>
          <label className="toggle-switch">
            <input
              type="checkbox"
              checked={settings.show_online_status}
              onChange={(e) => handleSettingChange('show_online_status', e.target.checked)}
              disabled={saving}
            />
            <span className="toggle-slider"></span>
          </label>
        </div>

        <div className="setting-item">
          <div className="setting-info">
            <h4>Показывать время последнего посещения</h4>
            <p>Другие пользователи смогут видеть, когда вы последний раз были в приложении</p>
          </div>
          <label className="toggle-switch">
            <input
              type="checkbox"
              checked={settings.show_last_seen}
              onChange={(e) => handleSettingChange('show_last_seen', e.target.checked)}
              disabled={saving}
            />
            <span className="toggle-slider"></span>
          </label>
        </div>
      </div>
    </div>
  );
};

export default PrivacySettings;
