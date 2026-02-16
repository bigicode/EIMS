import React, { createContext, useCallback, useEffect, useReducer } from 'react';
import { deviceService } from '../services/deviceService';

export const DevicesContext = createContext(null);

const initialState = {
  devices: [],
  loading: true,
  error: null,
};

const deviceReducer = (state, action) => {
  switch (action.type) {
    case 'FETCH_START':
      return { ...state, loading: true, error: null };
    case 'FETCH_SUCCESS':
      return { ...state, loading: false, devices: action.payload, error: null };
    case 'FETCH_ERROR':
      return { ...state, loading: false, error: action.payload };
    case 'ADD_SUCCESS':
      return { ...state, devices: [...state.devices, action.payload], error: null };
    case 'UPDATE_SUCCESS':
      return {
        ...state,
        devices: state.devices.map((device) =>
          String(device.id) === String(action.payload.id) ? action.payload : device
        ),
        error: null,
      };
    case 'DELETE_SUCCESS':
      return {
        ...state,
        devices: state.devices.filter((device) => String(device.id) !== String(action.payload)),
        error: null,
      };
    default:
      return state;
  }
};

export const DevicesProvider = ({ children }) => {
  const [state, dispatch] = useReducer(deviceReducer, initialState);

  const fetchDevices = useCallback(async () => {
    dispatch({ type: 'FETCH_START' });
    try {
      const devices = await deviceService.listDevices();
      dispatch({ type: 'FETCH_SUCCESS', payload: devices });
    } catch (error) {
      dispatch({ type: 'FETCH_ERROR', payload: error.message || 'Failed to fetch devices' });
    }
  }, []);

  useEffect(() => {
    fetchDevices();
  }, [fetchDevices]);

  const addDevice = useCallback(async (device) => {
    const createdDevice = await deviceService.addDevice(device);
    dispatch({ type: 'ADD_SUCCESS', payload: createdDevice });
    return createdDevice;
  }, []);

  const editDevice = useCallback(async (updatedDevice) => {
    const savedDevice = await deviceService.updateDevice(updatedDevice);
    dispatch({ type: 'UPDATE_SUCCESS', payload: savedDevice });
    return savedDevice;
  }, []);

  const deleteDevice = useCallback(async (id) => {
    await deviceService.removeDevice(id);
    dispatch({ type: 'DELETE_SUCCESS', payload: id });
  }, []);

  return (
    <DevicesContext.Provider
      value={{
        devices: state.devices,
        loading: state.loading,
        error: state.error,
        addDevice,
        editDevice,
        deleteDevice,
        refreshDevices: fetchDevices,
      }}
    >
      {children}
    </DevicesContext.Provider>
  );
};
