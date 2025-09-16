import React, { useState, useEffect } from 'react';
import Icon from './Icon';

interface NotificationSettings {
  pushNotifications: boolean;
  emailNotifications: boolean;
  smsNotifications: boolean;
  newCatchNotifications: boolean;
  weatherAlerts: boolean;
  eventReminders: boolean;
  friendRequests: boolean;
  comments: boolean;
  likes: boolean;
}

const NotificationSettings: React.FC = () => {
  const [settings, setSettings] = useState<NotificationSettings>({
    pushNotifications: true,
    emailNotifications: true,
    smsNotifications: false,
    newCatchNotifications: true,
    weatherAlerts: true,
    eventReminders: true,
    friendRequests: true,
    comments: true,
    likes: false
  });

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);

  useEffect(() => {
    loadSettings();
  }, []);

  const loadSettings = async () => {
    try {
      setLoading(true);
      // Здесь будет загрузка настроек с сервера
      // const data = await getNotificationSettings();
      // setSettings(data);
    } catch (err) {
      console.error('Failed to load notification settings:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleSettingChange = (key: keyof NotificationSettings, value: boolean) => {
    setSettings(prev => ({
      ...prev,
      [key]: value
    }));
  };

  const handleSave = async () => {
    try {
      setLoading(true);
      setError(null);
      
      // Здесь будет сохранение настроек на сервер
      // await updateNotificationSettings(settings);
      
      setSuccess('Настройки уведомлений сохранены');
      setTimeout(() => setSuccess(null), 3000);
    } catch (err) {
      setError('Не удалось сохранить настройки');
      console.error('Failed to save notification settings:', err);
    } finally {
      setLoading(false);
    }
  };

  const SettingToggle: React.FC<{
    title: string;
    description: string;
    value: boolean;
    onChange: (value: boolean) => void;
    icon: string;
  }> = ({ title, description, value, onChange, icon }) => (
    <div className="setting-item">
      <div className="setting-item__info">
        <div className="setting-item__icon">
          <Icon name={icon} size="md" />
        </div>
        <div className="setting-item__content">
          <h4 className="setting-item__title">{title}</h4>
          <p className="setting-item__description">{description}</p>
        </div>
      </div>
      <label className="toggle-switch">
        <input
          type="checkbox"
          checked={value}
          onChange={(e) => onChange(e.target.checked)}
        />
        <span className="toggle-switch__slider"></span>
      </label>
    </div>
  );

  if (loading) {
    return (
      <div className="settings-loading">
        <Icon name="refresh" size="lg" />
        <p>Загрузка настроек...</p>
      </div>
    );
  }

  return (
    <div className="notification-settings">
      {error && (
        <div className="settings-error">
          <Icon name="error" size="md" />
          <span>{error}</span>
        </div>
      )}

      {success && (
        <div className="settings-success">
          <Icon name="check_circle" size="md" />
          <span>{success}</span>
        </div>
      )}

      <div className="settings-section">
        <h3 className="settings-section__title">Общие уведомления</h3>
        
        <SettingToggle
          title="Push-уведомления"
          description="Получать уведомления в браузере"
          value={settings.pushNotifications}
          onChange={(value) => handleSettingChange('pushNotifications', value)}
          icon="notifications"
        />

        <SettingToggle
          title="Email-уведомления"
          description="Получать уведомления на email"
          value={settings.emailNotifications}
          onChange={(value) => handleSettingChange('emailNotifications', value)}
          icon="email"
        />

        <SettingToggle
          title="SMS-уведомления"
          description="Получать уведомления по SMS"
          value={settings.smsNotifications}
          onChange={(value) => handleSettingChange('smsNotifications', value)}
          icon="sms"
        />
      </div>

      <div className="settings-section">
        <h3 className="settings-section__title">Уведомления о рыбалке</h3>
        
        <SettingToggle
          title="Новые уловы"
          description="Уведомления о новых уловах друзей"
          value={settings.newCatchNotifications}
          onChange={(value) => handleSettingChange('newCatchNotifications', value)}
          icon="pets"
        />

        <SettingToggle
          title="Погодные предупреждения"
          description="Уведомления о погодных условиях"
          value={settings.weatherAlerts}
          onChange={(value) => handleSettingChange('weatherAlerts', value)}
          icon="wb_sunny"
        />

        <SettingToggle
          title="Напоминания о событиях"
          description="Уведомления о предстоящих событиях"
          value={settings.eventReminders}
          onChange={(value) => handleSettingChange('eventReminders', value)}
          icon="event"
        />
      </div>

      <div className="settings-section">
        <h3 className="settings-section__title">Социальные уведомления</h3>
        
        <SettingToggle
          title="Запросы в друзья"
          description="Уведомления о новых запросах в друзья"
          value={settings.friendRequests}
          onChange={(value) => handleSettingChange('friendRequests', value)}
          icon="person_add"
        />

        <SettingToggle
          title="Комментарии"
          description="Уведомления о новых комментариях"
          value={settings.comments}
          onChange={(value) => handleSettingChange('comments', value)}
          icon="comment"
        />

        <SettingToggle
          title="Лайки"
          description="Уведомления о лайках ваших уловов"
          value={settings.likes}
          onChange={(value) => handleSettingChange('likes', value)}
          icon="favorite"
        />
      </div>

      <div className="settings-actions">
        <button 
          className="btn btn-primary"
          onClick={handleSave}
          disabled={loading}
        >
          {loading ? 'Сохранение...' : 'Сохранить настройки'}
        </button>
      </div>
    </div>
  );
};

export default NotificationSettings;

