import { STORAGE_KEYS } from '../constants/deviceOptions';
import { storageService } from './storageService';

const mockDelay = (value, delayMs = 140) =>
  new Promise((resolve) => {
    setTimeout(() => resolve(value), delayMs);
  });

const getStoredNotifications = () => {
  const stored = storageService.get(STORAGE_KEYS.NOTIFICATIONS, []);
  return Array.isArray(stored) ? stored : [];
};

const saveNotifications = (notifications) => {
  storageService.set(STORAGE_KEYS.NOTIFICATIONS, notifications);
  return notifications;
};

export const notificationService = {
  async listNotifications() {
    return mockDelay(getStoredNotifications());
  },

  async addNotification(notification) {
    const notifications = getStoredNotifications();
    const nextId =
      notifications.length > 0 ? Math.max(...notifications.map((item) => Number(item.id) || 0)) + 1 : 1;
    const normalized = {
      ...notification,
      id: nextId,
      status: notification.status || 'unresolved',
      priority: (notification.priority || 'Medium').toLowerCase(),
    };

    saveNotifications([...notifications, normalized]);
    return mockDelay(normalized);
  },

  async updateNotification(updatedNotification) {
    const notifications = getStoredNotifications();
    const updated = notifications.map((notification) =>
      String(notification.id) === String(updatedNotification.id) ? { ...updatedNotification } : notification
    );

    saveNotifications(updated);
    return mockDelay(updatedNotification);
  },

  async removeNotification(notificationId) {
    const notifications = getStoredNotifications();
    const updated = notifications.filter(
      (notification) => String(notification.id) !== String(notificationId)
    );
    saveNotifications(updated);
    return mockDelay(true);
  },
};
