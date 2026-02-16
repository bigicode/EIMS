import React from 'react';
import '../../App.css';
import DeviceForm from './DeviceForm';

function RegisterDevice({ onRegister }) {
  const handleRegister = async (newDevice) => {
    await onRegister?.(newDevice);
  };

  return (
      <div className="device-form">
        <DeviceForm onSubmit={handleRegister} />
      </div>
  );
}

export default RegisterDevice;
