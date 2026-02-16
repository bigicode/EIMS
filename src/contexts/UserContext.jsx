import React, { createContext, useCallback, useEffect, useState } from 'react';
import { STORAGE_KEYS } from '../constants/deviceOptions';
import { authService } from '../services/authService';
import { storageService } from '../services/storageService';

export const UserContext = createContext(null);

export const UserProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    let isMounted = true;

    const initUser = async () => {
      try {
        const storedUser = await authService.getCurrentUser();
        if (isMounted) {
          setUser(storedUser);
        }
      } catch (initError) {
        if (isMounted) {
          setError(initError.message || 'Failed to initialize user');
        }
      } finally {
        if (isMounted) {
          setLoading(false);
        }
      }
    };

    initUser();

    return () => {
      isMounted = false;
    };
  }, []);

  const login = useCallback(async (credentials) => {
    setLoading(true);
    setError(null);
    try {
      const authenticatedUser = await authService.login(credentials);
      setUser(authenticatedUser);
      return authenticatedUser;
    } catch (loginError) {
      const message = loginError.message || 'Unable to sign in';
      setError(message);
      throw new Error(message);
    } finally {
      setLoading(false);
    }
  }, []);

  const logout = useCallback(async () => {
    setLoading(true);
    try {
      await authService.logout();
      setUser(null);
    } finally {
      setLoading(false);
    }
  }, []);

  const updateUser = useCallback((nextUser) => {
    setUser(nextUser);
    storageService.set(STORAGE_KEYS.USER, nextUser);
  }, []);

  return (
    <UserContext.Provider
      value={{
        user,
        loading,
        error,
        login,
        logout,
        updateUser,
        isAuthenticated: !!user,
      }}
    >
      {children}
    </UserContext.Provider>
  );
};
