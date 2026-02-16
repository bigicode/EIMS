import React, { useState } from 'react';
import DeviceForm from './DeviceForm';
import { useDevices } from '../../hooks/useDevices';
import { useNotifications } from '../../hooks/useNotifications';

function RegisterDevicePage() {
  const [submittedDevice, setSubmittedDevice] = useState(null);
  const { addDevice, loading, error } = useDevices();
  const { addToast } = useNotifications();

  const handleDeviceRegister = async (device) => {
    try {
      const createdDevice = await addDevice(device);
      setSubmittedDevice(createdDevice);
      addToast(`Device ${createdDevice.name} registered successfully.`, 'success');
    } catch (createError) {
      addToast(createError.message || 'Failed to register device.', 'danger');
    }
  };

  return (
    <div className="container mt-4">
      <h4 className='headreg'>Register New Device:</h4>
      {loading && <div className="alert alert-info">Saving device...</div>}
      {error && <div className="alert alert-danger">{error}</div>}
      <DeviceForm onSubmit={handleDeviceRegister} />
      {submittedDevice && (
        <div className="alert alert-success mt-4">
          Device <strong>{submittedDevice.name}</strong> registered successfully!
        </div>
      )}
    </div>
  );
}

export default RegisterDevicePage;
