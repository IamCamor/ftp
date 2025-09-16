import React from 'react';
import { useNavigate } from 'react-router-dom';
import Icon from '../components/Icon';
import { useNotifications, type Notification } from '../hooks/useNotifications';
import { formatDistanceToNow } from 'date-fns';
import { ru } from 'date-fns/locale';

const NotificationsPage: React.FC = () => {
  const navigate = useNavigate();
  const { notifications, markAsRead, markAllAsRead, removeNotification } = useNotifications();

  const getNotificationIcon = (type: Notification['type']) => {
    switch (type) {
      case 'success':
        return 'check_circle';
      case 'warning':
        return 'warning';
      case 'error':
        return 'error';
      default:
        return 'info';
    }
  };

  const getNotificationColor = (type: Notification['type']) => {
    switch (type) {
      case 'success':
        return 'var(--success-color, #10b981)';
      case 'warning':
        return 'var(--warning-color, #f59e0b)';
      case 'error':
        return 'var(--error-color, #ef4444)';
      default:
        return 'var(--info-color, #3b82f6)';
    }
  };

  const handleNotificationClick = (notification: Notification) => {
    if (!notification.read) {
      markAsRead(notification.id);
    }
    
    if (notification.actionUrl) {
      navigate(notification.actionUrl);
    }
  };

  const unreadNotifications = notifications.filter(n => !n.read);
  const readNotifications = notifications.filter(n => n.read);

  return (
    <div className="notifications-page">
      <div className="page-header">
        <button className="back-button" onClick={() => navigate(-1)}>
          <Icon name="arrow_back" size="md" />
        </button>
        <h1>Уведомления</h1>
        {unreadNotifications.length > 0 && (
          <button
            className="mark-all-read-button"
            onClick={markAllAsRead}
            title="Отметить все как прочитанные"
          >
            <Icon name="done_all" size="sm" />
          </button>
        )}
      </div>

      <div className="notifications-content">
        {notifications.length === 0 ? (
          <div className="notifications-empty">
            <Icon name="notifications_off" size="xl" />
            <h3>Нет уведомлений</h3>
            <p>Здесь будут появляться важные уведомления</p>
          </div>
        ) : (
          <>
            {unreadNotifications.length > 0 && (
              <div className="notifications-section">
                <h2 className="section-title">Новые ({unreadNotifications.length})</h2>
                <div className="notifications-list">
                  {unreadNotifications.map((notification) => (
                    <div
                      key={notification.id}
                      className={`notification-item unread`}
                      onClick={() => handleNotificationClick(notification)}
                    >
                      <div className="notification-icon">
                        <Icon 
                          name={getNotificationIcon(notification.type)} 
                          size="md"
                          style={{ color: getNotificationColor(notification.type) }}
                        />
                      </div>
                      <div className="notification-content">
                        <div className="notification-header">
                          <h4 className="notification-title">{notification.title}</h4>
                          <span className="notification-time">
                            {formatDistanceToNow(notification.timestamp, { 
                              addSuffix: true, 
                              locale: ru 
                            })}
                          </span>
                        </div>
                        <p className="notification-message">{notification.message}</p>
                      </div>
                      <button
                        className="notification-remove"
                        onClick={(e) => {
                          e.stopPropagation();
                          removeNotification(notification.id);
                        }}
                        title="Удалить уведомление"
                      >
                        <Icon name="close" size="sm" />
                      </button>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {readNotifications.length > 0 && (
              <div className="notifications-section">
                <h2 className="section-title">Прочитанные</h2>
                <div className="notifications-list">
                  {readNotifications.map((notification) => (
                    <div
                      key={notification.id}
                      className="notification-item read"
                      onClick={() => handleNotificationClick(notification)}
                    >
                      <div className="notification-icon">
                        <Icon 
                          name={getNotificationIcon(notification.type)} 
                          size="md"
                          style={{ color: getNotificationColor(notification.type) }}
                        />
                      </div>
                      <div className="notification-content">
                        <div className="notification-header">
                          <h4 className="notification-title">{notification.title}</h4>
                          <span className="notification-time">
                            {formatDistanceToNow(notification.timestamp, { 
                              addSuffix: true, 
                              locale: ru 
                            })}
                          </span>
                        </div>
                        <p className="notification-message">{notification.message}</p>
                      </div>
                      <button
                        className="notification-remove"
                        onClick={(e) => {
                          e.stopPropagation();
                          removeNotification(notification.id);
                        }}
                        title="Удалить уведомление"
                      >
                        <Icon name="close" size="sm" />
                      </button>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
};

export default NotificationsPage;