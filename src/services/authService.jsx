import { STORAGE_KEYS, USER_ROLES } from '../constants/deviceOptions';
import { storageService } from './storageService';

const mockDelay = (value, delayMs = 220) =>
  new Promise((resolve) => {
    setTimeout(() => resolve(value), delayMs);
  });

export const authService = {
  async getCurrentUser() {
    const user = storageService.get(STORAGE_KEYS.USER, null);
    return mockDelay(user, 120);
  },

  async login({ email, password, userType }) {
    if (!email || !password) {
      throw new Error('Email and password are required');
    }

    const role = userType === USER_ROLES.ADMIN ? USER_ROLES.ADMIN : USER_ROLES.USER;
    const username = email.split('@')[0] || 'user';
    const user = {
      id: Date.now(),
      name: username,
      username,
      email,
      role,
      userType: role,
    };

    storageService.set(STORAGE_KEYS.USER, user);
    return mockDelay(user);
  },

  async logout() {
    storageService.remove(STORAGE_KEYS.USER);
    return mockDelay(true, 100);
  },
};
