import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Icon from '../components/Icon';
import PrivacySettings from '../components/PrivacySettings';
import NotificationSettings from '../components/NotificationSettings';
import AccountSettings from '../components/AccountSettings';

const SettingsPage: React.FC = () => {
  const navigate = useNavigate();
  const [activeTab, setActiveTab] = useState<'privacy' | 'notifications' | 'account'>('privacy');

  const handleBack = () => {
    navigate(-1);
  };

  const tabs = [
    { id: 'privacy', label: 'Приватность', icon: 'privacy_tip' },
    { id: 'notifications', label: 'Уведомления', icon: 'notifications' },
    { id: 'account', label: 'Аккаунт', icon: 'account_circle' }
  ];

  return (
    <div className="screen">
      <div className="settings-page">
        {/* Header */}
        <div className="settings-header">
          <button className="back-button" onClick={handleBack}>
            <Icon name="arrow_back" size="md" />
          </button>
          <h1>Настройки</h1>
        </div>

        {/* Tabs */}
        <div className="settings-tabs">
          {tabs.map(tab => (
            <button
              key={tab.id}
              className={`tab-button ${activeTab === tab.id ? 'active' : ''}`}
              onClick={() => setActiveTab(tab.id as any)}
            >
              <Icon name={tab.icon} size="md" />
              <span>{tab.label}</span>
            </button>
          ))}
        </div>

        {/* Content */}
        <div className="settings-content">
          {activeTab === 'privacy' && <PrivacySettings />}
          {activeTab === 'notifications' && <NotificationSettings />}
          {activeTab === 'account' && <AccountSettings />}
        </div>
      </div>
    </div>
  );
};

export default SettingsPage;
