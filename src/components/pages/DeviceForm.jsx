// DeviceForm.jsx
import React, { useState } from 'react';
import { DEVICE_TYPES } from '../../constants/deviceOptions';

function DeviceForm({ onSubmit }) {
  const [formData, setFormData] = useState({
    name: '',
    imei: '',
    deviceNumber: '',
    office: '',
    dateRegistered: '',
    active: true,
    type: 'Printer',
  });
  const [errors, setErrors] = useState({});

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value,
    }));

    if (errors[name]) {
      setErrors((prev) => ({ ...prev, [name]: '' }));
    }
  };

  const validate = () => {
    const nextErrors = {};

    if (!formData.name.trim()) nextErrors.name = 'Device name is required';
    if (!/^\d{12,17}$/.test(formData.imei)) nextErrors.imei = 'IMEI should be 12-17 digits';
    if (!formData.deviceNumber.trim()) nextErrors.deviceNumber = 'Device number is required';
    if (!formData.office.trim()) nextErrors.office = 'Office is required';
    if (!formData.dateRegistered) nextErrors.dateRegistered = 'Date registered is required';
    if (!formData.type) nextErrors.type = 'Device type is required';

    setErrors(nextErrors);
    return Object.keys(nextErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!validate()) {
      return;
    }

    if (onSubmit) {
      await onSubmit(formData);
    }

    setFormData({
      name: '',
      imei: '',
      deviceNumber: '',
      office: '',
      dateRegistered: '',
      active: true,
      type: 'Printer',
    });
  };

  return (
    <form onSubmit={handleSubmit}className='device-form'>
      <div className="row g-3">
        <div className="col-md-6">
          <label className="form-label">Device Name</label>
          <input type="text" className="form-control" name="name" value={formData.name} onChange={handleChange} required />
          {errors.name && <small className="text-danger">{errors.name}</small>}
        </div>
        <div className="col-md-6">
          <label className="form-label">IMEI</label>
          <input type="text" className="form-control" name="imei" value={formData.imei} onChange={handleChange} required />
          {errors.imei && <small className="text-danger">{errors.imei}</small>}
        </div>
        <div className="col-md-6">
          <label className="form-label">Device Number</label>
          <input type="text" className="form-control" name="deviceNumber" value={formData.deviceNumber} onChange={handleChange} required />
          {errors.deviceNumber && <small className="text-danger">{errors.deviceNumber}</small>}
        </div>
        <div className="col-md-6">
          <label className="form-label">Office</label>
          <input type="text" className="form-control" name="office" value={formData.office} onChange={handleChange} required />
          {errors.office && <small className="text-danger">{errors.office}</small>}
        </div>
        <div className="col-md-6">
          <label className="form-label">Date Registered</label>
          <input type="date" className="form-control" name="dateRegistered" value={formData.dateRegistered} onChange={handleChange} required />
          {errors.dateRegistered && <small className="text-danger">{errors.dateRegistered}</small>}
        </div>
        <div className="col-md-6">
          <label className="form-label">Device Type</label>
          <select className="form-select" name="type" value={formData.type} onChange={handleChange}>
            <option value="">Select Type</option>
            {DEVICE_TYPES.map((deviceType) => (
              <option key={deviceType} value={deviceType}>
                {deviceType}
              </option>
            ))}
          </select>
          {errors.type && <small className="text-danger">{errors.type}</small>}
        </div>
        <div className="col-md-6">
          <label className="form-label">Status</label>
          <div className="form-check">
            <input className="form-check-input" type="checkbox" name="active" checked={formData.active} onChange={handleChange} />
            <label className="form-check-label">Active</label>
          </div>
        </div>
      </div>
      <div className='butan'>
      <button type="submit" className="btn btn-primary mt-3">Register Device</button>
      </div>
    </form>
  );
}

export default DeviceForm;
