import React, { useState, useEffect } from 'react';
import { getAppSettings, updateAppSetting } from '../../api';
import type { AppSettings } from '../../types';
import Icon from '../Icon';

const AppSettings: React.FC = () => {
  const [settings, setSettings] = useState<AppSettings[]>([]);
  const [loading, setLoading] = useState(true);
  const [editingKey, setEditingKey] = useState<string | null>(null);
  const [editValue, setEditValue] = useState('');

  useEffect(() => {
    loadSettings();
  }, []);

  const loadSettings = async () => {
    try {
      setLoading(true);
      const response = await getAppSettings();
      setSettings(response.data);
    } catch (error) {
      console.error('Error loading settings:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleEdit = (setting: AppSettings) => {
    setEditingKey(setting.key);
    setEditValue(setting.value);
  };

  const handleSave = async () => {
    if (!editingKey) return;
    
    try {
      await updateAppSetting(editingKey, editValue);
      setEditingKey(null);
      setEditValue('');
      loadSettings();
    } catch (error) {
      console.error('Error updating setting:', error);
    }
  };

  const handleCancel = () => {
    setEditingKey(null);
    setEditValue('');
  };

  if (loading) {
    return <div className="loading">Загрузка настроек...</div>;
  }

  return (
    <div className="app-settings">
      <div className="admin-header">
        <h2>Настройки приложения</h2>
      </div>

      <div className="settings-list">
        {settings.map((setting) => (
          <div key={setting.key} className="setting-item">
            <div className="setting-info">
              <h3>{setting.key}</h3>
              {setting.description && (
                <p className="setting-description">{setting.description}</p>
              )}
            </div>
            
            <div className="setting-value">
              {editingKey === setting.key ? (
                <div className="edit-form">
                  <input
                    type="text"
                    value={editValue}
                    onChange={(e) => setEditValue(e.target.value)}
                    className="setting-input"
                  />
                  <div className="edit-actions">
                    <button 
                      className="btn btn-primary btn-sm"
                      onClick={handleSave}
                    >
                      <Icon name="check" size="sm" />
                    </button>
                    <button 
                      className="btn btn-secondary btn-sm"
                      onClick={handleCancel}
                    >
                      <Icon name="close" size="sm" />
                    </button>
                  </div>
                </div>
              ) : (
                <div className="value-display">
                  <span className="value">{setting.value}</span>
                  <button 
                    className="btn btn-secondary btn-sm"
                    onClick={() => handleEdit(setting)}
                  >
                    <Icon name="edit" size="sm" />
                  </button>
                </div>
              )}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default AppSettings;

