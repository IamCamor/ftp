import React, { useState, useEffect } from 'react';
import Icon from '../components/Icon';
import { notificationsList, notificationRead } from '../api';
import type { Notification } from '../types';

const NotificationsPage: React.FC = () => {
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    loadNotifications();
  }, []);

  const loadNotifications = async () => {
    try {
      setLoading(true);
      const data = await notificationsList();
      setNotifications(data);
    } catch (err) {
      setError('Не удалось загрузить уведомления');
      console.error('Notifications loading error:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleMarkAsRead = async (id: number) => {
    try {
      await notificationRead(id);
      setNotifications(prev => prev.map(n => 
        n.id === id ? { ...n, is_read: true, read_at: new Date().toISOString() } : n
      ));
    } catch (err) {
      console.error('Failed to mark notification as read:', err);
    }
  };

  const getNotificationIcon = (type: string) => {
    switch (type) {
      case 'new_like':
        return 'favorite';
      case 'new_comment':
        return 'comment';
      case 'catch_added':
        return 'add_circle';
      case 'point_added':
        return 'place';
      case 'system':
        return 'info';
      default:
        return 'notifications';
    }
  };

  if (loading) {
    return (
      <div className="screen">
        <div className="loading">Загрузка уведомлений...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="screen">
        <div className="error">
          <p>{error}</p>
          <button onClick={loadNotifications} className="btn btn-primary">
            Попробовать снова
          </button>
        </div>
      </div>
    );
  }

  if (notifications.length === 0) {
    return (
      <div className="screen">
        <div className="empty-state">
          <Icon name="notifications_off" size={64} />
          <h3>Нет уведомлений</h3>
          <p>Здесь будут появляться новые уведомления</p>
        </div>
      </div>
    );
  }

  return (
    <div className="screen">
      <div className="notifications-header">
        <h2>Уведомления</h2>
        <button 
          className="mark-all-read"
          onClick={() => {
            notifications.forEach(n => {
              if (!n.is_read) {
                handleMarkAsRead(n.id);
              }
            });
          }}
        >
          Отметить все как прочитанные
        </button>
      </div>

      <div className="notifications-list">
        {notifications.map((notification) => (
          <div 
            key={notification.id} 
            className={`notification-item ${notification.is_read ? 'read' : 'unread'}`}
            onClick={() => !notification.is_read && handleMarkAsRead(notification.id)}
          >
            <div className="notification-icon">
              <Icon name={getNotificationIcon(notification.type)} size={24} />
            </div>
            
            <div className="notification-content">
              <h4>{notification.title}</h4>
              {notification.body && <p>{notification.body}</p>}
              <span className="notification-time">
                {new Date(notification.created_at).toLocaleString()}
              </span>
            </div>

            {!notification.is_read && (
              <div className="unread-indicator"></div>
            )}
          </div>
        ))}
      </div>
    </div>
  );
};

export default NotificationsPage;

