import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Icon from './Icon';
import ThemeToggle from './ThemeToggle';
import LanguageSelector from './LanguageSelector';
import { useTheme } from '../hooks/useTheme';
import { useLanguage } from '../hooks/useLanguage';

const AccountSettings: React.FC = () => {
  const navigate = useNavigate();
  const { isDark } = useTheme();
  const { getCurrentLanguage } = useLanguage();
  const [loading, setLoading] = useState(false);

  const currentLanguage = getCurrentLanguage();

  const SettingItem: React.FC<{
    title: string;
    description: string;
    icon: string;
    children: React.ReactNode;
  }> = ({ title, description, icon, children }) => (
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
      <div className="setting-item__action">
        {children}
      </div>
    </div>
  );

  const handleExportData = async () => {
    setLoading(true);
    try {
      // Здесь будет экспорт данных пользователя
      console.log('Exporting user data...');
      // await exportUserData();
    } catch (error) {
      console.error('Failed to export data:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteAccount = () => {
    if (window.confirm('Вы уверены, что хотите удалить аккаунт? Это действие нельзя отменить.')) {
      // Здесь будет удаление аккаунта
      console.log('Deleting account...');
    }
  };

  return (
    <div className="account-settings">
      <div className="settings-section">
        <h3 className="settings-section__title">Внешний вид</h3>
        
        <SettingItem
          title="Тема приложения"
          description="Выберите светлую или тёмную тему"
          icon="palette"
        >
          <ThemeToggle showLabel={true} size="md" />
        </SettingItem>

        <SettingItem
          title="Язык интерфейса"
          description="Выберите язык приложения"
          icon="language"
        >
          <LanguageSelector showLabel={true} size="medium" />
        </SettingItem>
      </div>

      <div className="settings-section">
        <h3 className="settings-section__title">Данные аккаунта</h3>
        
        <SettingItem
          title="Экспорт данных"
          description="Скачать все ваши данные в формате JSON"
          icon="download"
        >
          <button 
            className="btn btn-outline btn-sm"
            onClick={handleExportData}
            disabled={loading}
          >
            <Icon name="download" size="sm" />
            Экспорт
          </button>
        </SettingItem>

        <SettingItem
          title="Очистить кэш"
          description="Удалить временные данные приложения"
          icon="clear_all"
        >
          <button 
            className="btn btn-outline btn-sm"
            onClick={() => {
              localStorage.clear();
              window.location.reload();
            }}
          >
            <Icon name="clear_all" size="sm" />
            Очистить
          </button>
        </SettingItem>
      </div>

      <div className="settings-section">
        <h3 className="settings-section__title">Информация о приложении</h3>
        
        <div className="app-info">
          <div className="info-item">
            <span className="info-label">Версия приложения:</span>
            <span className="info-value">1.0.0</span>
          </div>
          <div className="info-item">
            <span className="info-label">Текущая тема:</span>
            <span className="info-value">{isDark ? 'Тёмная' : 'Светлая'}</span>
          </div>
          <div className="info-item">
            <span className="info-label">Язык интерфейса:</span>
            <span className="info-value">{currentLanguage.nativeName}</span>
          </div>
          <div className="info-item">
            <span className="info-label">Размер кэша:</span>
            <span className="info-value">
              {Math.round(JSON.stringify(localStorage).length / 1024)} KB
            </span>
          </div>
        </div>
      </div>

      <div className="settings-section">
        <h3 className="settings-section__title">Опасная зона</h3>
        
        <div className="danger-zone">
          <div className="danger-item">
            <div className="danger-info">
              <Icon name="warning" size="md" />
              <div>
                <h4>Удалить аккаунт</h4>
                <p>Навсегда удалить ваш аккаунт и все данные</p>
              </div>
            </div>
            <button 
              className="btn btn-danger btn-sm"
              onClick={handleDeleteAccount}
            >
              <Icon name="delete_forever" size="sm" />
              Удалить
            </button>
          </div>
        </div>
      </div>

      <div className="settings-section">
        <h3 className="settings-section__title">Полезные ссылки</h3>
        
        <div className="links-list">
          <a 
            href="/help" 
            className="link-item"
            onClick={(e) => {
              e.preventDefault();
              navigate('/help');
            }}
          >
            <Icon name="help" size="md" />
            <span>Справка и поддержка</span>
            <Icon name="chevron_right" size="md" />
          </a>
          
          <a 
            href="/privacy" 
            className="link-item"
            onClick={(e) => {
              e.preventDefault();
              navigate('/privacy');
            }}
          >
            <Icon name="privacy_tip" size="md" />
            <span>Политика конфиденциальности</span>
            <Icon name="chevron_right" size="md" />
          </a>
          
          <a 
            href="/terms" 
            className="link-item"
            onClick={(e) => {
              e.preventDefault();
              navigate('/terms');
            }}
          >
            <Icon name="description" size="md" />
            <span>Условия использования</span>
            <Icon name="chevron_right" size="md" />
          </a>
        </div>
      </div>
    </div>
  );
};

export default AccountSettings;
