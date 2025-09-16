import { useState, useEffect, useCallback } from 'react';

export interface Notification {
  id: string;
  type: 'info' | 'success' | 'warning' | 'error';
  title: string;
  message: string;
  timestamp: Date;
  read: boolean;
  actionUrl?: string;
}

export const useNotifications = () => {
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);

  // Загружаем уведомления из localStorage при инициализации
  useEffect(() => {
    const savedNotifications = localStorage.getItem('notifications');
    if (savedNotifications) {
      try {
        const parsed = JSON.parse(savedNotifications);
        const notificationsWithDates = parsed.map((n: any) => ({
          ...n,
          timestamp: new Date(n.timestamp)
        }));
        setNotifications(notificationsWithDates);
        updateUnreadCount(notificationsWithDates);
      } catch (error) {
        console.error('Failed to parse saved notifications:', error);
      }
    } else {
      // Создаем тестовые уведомления
      createTestNotifications();
    }
  }, []);

  const updateUnreadCount = useCallback((notificationsList: Notification[]) => {
    const unread = notificationsList.filter(n => !n.read).length;
    setUnreadCount(unread);
  }, []);

  const createTestNotifications = () => {
    const testNotifications: Notification[] = [
      {
        id: '1',
        type: 'success',
        title: 'Новый улов!',
        message: 'Иван Петров поймал щуку весом 3.2 кг',
        timestamp: new Date(Date.now() - 1000 * 60 * 5), // 5 минут назад
        read: false,
        actionUrl: '/catch/123'
      },
      {
        id: '2',
        type: 'info',
        title: 'Обновление погоды',
        message: 'Прогноз погоды для ваших точек обновлен',
        timestamp: new Date(Date.now() - 1000 * 60 * 30), // 30 минут назад
        read: false
      },
      {
        id: '3',
        type: 'warning',
        title: 'Штормовое предупреждение',
        message: 'Ожидается сильный ветер в районе озера Светлое',
        timestamp: new Date(Date.now() - 1000 * 60 * 60), // 1 час назад
        read: true
      },
      {
        id: '4',
        type: 'success',
        title: 'Достижение разблокировано!',
        message: 'Вы получили достижение "Первая рыбалка"',
        timestamp: new Date(Date.now() - 1000 * 60 * 60 * 2), // 2 часа назад
        read: true,
        actionUrl: '/achievements'
      },
      {
        id: '5',
        type: 'info',
        title: 'Новый участник группы',
        message: 'Алексей присоединился к группе "Рыбалка на Волге"',
        timestamp: new Date(Date.now() - 1000 * 60 * 60 * 3), // 3 часа назад
        read: false,
        actionUrl: '/groups/volga-fishing'
      }
    ];

    setNotifications(testNotifications);
    updateUnreadCount(testNotifications);
    saveNotifications(testNotifications);
  };

  const saveNotifications = (notificationsList: Notification[]) => {
    localStorage.setItem('notifications', JSON.stringify(notificationsList));
  };

  const addNotification = useCallback((notification: Omit<Notification, 'id' | 'timestamp' | 'read'>) => {
    const newNotification: Notification = {
      ...notification,
      id: Date.now().toString(),
      timestamp: new Date(),
      read: false
    };

    const updatedNotifications = [newNotification, ...notifications];
    setNotifications(updatedNotifications);
    updateUnreadCount(updatedNotifications);
    saveNotifications(updatedNotifications);
  }, [notifications, updateUnreadCount]);

  const markAsRead = useCallback((id: string) => {
    const updatedNotifications = notifications.map(n =>
      n.id === id ? { ...n, read: true } : n
    );
    setNotifications(updatedNotifications);
    updateUnreadCount(updatedNotifications);
    saveNotifications(updatedNotifications);
  }, [notifications, updateUnreadCount]);

  const markAllAsRead = useCallback(() => {
    const updatedNotifications = notifications.map(n => ({ ...n, read: true }));
    setNotifications(updatedNotifications);
    updateUnreadCount(updatedNotifications);
    saveNotifications(updatedNotifications);
  }, [notifications, updateUnreadCount]);

  const removeNotification = useCallback((id: string) => {
    const updatedNotifications = notifications.filter(n => n.id !== id);
    setNotifications(updatedNotifications);
    updateUnreadCount(updatedNotifications);
    saveNotifications(updatedNotifications);
  }, [notifications, updateUnreadCount]);

  const clearAllNotifications = useCallback(() => {
    setNotifications([]);
    setUnreadCount(0);
    localStorage.removeItem('notifications');
  }, []);

  return {
    notifications,
    unreadCount,
    addNotification,
    markAsRead,
    markAllAsRead,
    removeNotification,
    clearAllNotifications
  };
};

