import { useContext } from 'react';
import { DevicesContext } from '../contexts/DevicesContext';

export const useDevices = () => {
  const context = useContext(DevicesContext);
  if (!context) {
    throw new Error('useDevices must be used within DevicesProvider');
  }

  return context;
};
