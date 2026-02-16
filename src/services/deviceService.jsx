import { STORAGE_KEYS } from '../constants/deviceOptions';
import { storageService } from './storageService';

const mockDelay = (value, delayMs = 180) =>
  new Promise((resolve) => {
    setTimeout(() => resolve(value), delayMs);
  });

const defaultDevices = [
  {
    id: 1,
    name: 'HP LaserJet 1020',
    imei: '123456789012345',
    deviceNumber: 'DEV-001',
    office: 'Admin Block',
    dateRegistered: '2024-01-12',
    active: true,
    type: 'Printer',
  },
];

const getStoredDevices = () => {
  const stored = storageService.get(STORAGE_KEYS.DEVICES, null);
  if (!Array.isArray(stored) || stored.length === 0) {
    storageService.set(STORAGE_KEYS.DEVICES, defaultDevices);
    return defaultDevices;
  }

  return stored;
};

const saveDevices = (devices) => {
  storageService.set(STORAGE_KEYS.DEVICES, devices);
  return devices;
};

export const deviceService = {
  async listDevices() {
    return mockDelay(getStoredDevices());
  },

  async addDevice(device) {
    const devices = getStoredDevices();
    const nextId = devices.length > 0 ? Math.max(...devices.map((item) => Number(item.id) || 0)) + 1 : 1;
    const normalized = {
      ...device,
      id: nextId,
    };

    saveDevices([...devices, normalized]);
    return mockDelay(normalized);
  },

  async updateDevice(updatedDevice) {
    const devices = getStoredDevices();
    const updated = devices.map((device) =>
      String(device.id) === String(updatedDevice.id) ? { ...updatedDevice } : device
    );

    saveDevices(updated);
    return mockDelay(updatedDevice);
  },

  async removeDevice(deviceId) {
    const devices = getStoredDevices();
    const updated = devices.filter((device) => String(device.id) !== String(deviceId));
    saveDevices(updated);
    return mockDelay(true);
  },
};
