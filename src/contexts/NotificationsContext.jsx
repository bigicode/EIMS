import React, { createContext, useCallback, useEffect, useMemo, useState } from 'react';
import { notificationService } from '../services/notificationService';

export const NotificationsContext = createContext(null);

export const NotificationsProvider = ({ children }) => {
  const [notifications, setNotifications] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [toasts, setToasts] = useState([]);

  const loadNotifications = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await notificationService.listNotifications();
      setNotifications(data);
    } catch (fetchError) {
      setError(fetchError.message || 'Unable to load notifications');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    loadNotifications();
  }, [loadNotifications]);

  const removeToast = useCallback((toastId) => {
    setToasts((prevToasts) => prevToasts.filter((toast) => toast.id !== toastId));
  }, []);

  const addToast = useCallback(
    (message, variant = 'info', duration = 3200) => {
      const toast = {
        id: Date.now() + Math.random(),
        message,
        variant,
      };

      setToasts((prevToasts) => [...prevToasts, toast]);

      window.setTimeout(() => {
        removeToast(toast.id);
      }, duration);
    },
    [removeToast]
  );

  const addNotification = useCallback(async (notification) => {
    const created = await notificationService.addNotification(notification);
    setNotifications((prevNotifications) => [...prevNotifications, created]);
    return created;
  }, []);

  const updateNotification = useCallback(async (updatedNotification) => {
    const saved = await notificationService.updateNotification(updatedNotification);
    setNotifications((prevNotifications) =>
      prevNotifications.map((notification) =>
        String(notification.id) === String(saved.id) ? saved : notification
      )
    );
    return saved;
  }, []);

  const deleteNotification = useCallback(async (id) => {
    await notificationService.removeNotification(id);
    setNotifications((prevNotifications) =>
      prevNotifications.filter((notification) => String(notification.id) !== String(id))
    );
  }, []);

  const value = useMemo(
    () => ({
      notifications,
      loading,
      error,
      toasts,
      addToast,
      removeToast,
      addNotification,
      updateNotification,
      deleteNotification,
      refreshNotifications: loadNotifications,
    }),
    [
      notifications,
      loading,
      error,
      toasts,
      addToast,
      removeToast,
      addNotification,
      updateNotification,
      deleteNotification,
      loadNotifications,
    ]
  );

  return <NotificationsContext.Provider value={value}>{children}</NotificationsContext.Provider>;
};
