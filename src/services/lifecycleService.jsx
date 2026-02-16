import { storageService } from './storageService';

const keyForDevice = (deviceId) => `device_lifecycle_${deviceId}`;

export const lifecycleService = {
  getByDeviceId(deviceId) {
    return storageService.get(keyForDevice(deviceId), {});
  },

  saveByDeviceId(deviceId, lifecycleData) {
    storageService.set(keyForDevice(deviceId), lifecycleData);
    return lifecycleData;
  },

  hasData(deviceId) {
    const data = this.getByDeviceId(deviceId);
    return Object.keys(data || {}).length > 0;
  },
};
