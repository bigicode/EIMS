import React, { useState } from 'react';
import '../../App.css';
import { DEVICE_TYPES } from '../../constants/deviceOptions';

function ReportForm({ onSubmit }) {
  const [formData, setFormData] = useState({
    name: '',
    imei: '',
    deviceNumber: '',
    type: '',
    description: '',
    action: '',
    priority: '',
  });
  const [errors, setErrors] = useState({});

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
    if (errors[name]) {
      setErrors((prev) => ({ ...prev, [name]: '' }));
    }
  };

  const validate = () => {
    const nextErrors = {};
    if (!formData.name.trim()) nextErrors.name = 'Device name is required';
    if (!/^\d{12,17}$/.test(formData.imei)) nextErrors.imei = 'IMEI should be 12-17 digits';
    if (!formData.deviceNumber.trim()) nextErrors.deviceNumber = 'Device number is required';
    if (!formData.type) nextErrors.type = 'Type is required';
    if (!formData.description.trim()) nextErrors.description = 'Description is required';
    if (!formData.action) nextErrors.action = 'Action type is required';
    if (!formData.priority) nextErrors.priority = 'Priority is required';
    setErrors(nextErrors);
    return Object.keys(nextErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validate()) {
      return;
    }
    await onSubmit?.(formData);
    setFormData({
      name: '',
      imei: '',
      deviceNumber: '',
      type: '',
      description: '',
      action: '',
      priority: '',
    });
  };

  return (
    <div className="devices-container2">
      <h4>Device Issue Report Form:</h4>
      <form onSubmit={handleSubmit} className="formreg">

        <div className="mb-3">
          <label className="form-label">Device Name</label>
          <input type="text" className="form-control" name="name" value={formData.name} onChange={handleChange} />
          {errors.name && <small className="text-danger">{errors.name}</small>}
        </div>

        <div className="mb-3">
          <label className="form-label">IMEI</label>
          <input type="text" className="form-control" name="imei" value={formData.imei} onChange={handleChange} />
          {errors.imei && <small className="text-danger">{errors.imei}</small>}
        </div>

        <div className="mb-3">
          <label className="form-label">Device Number</label>
          <input type="text" className="form-control" name="deviceNumber" value={formData.deviceNumber} onChange={handleChange} />
          {errors.deviceNumber && <small className="text-danger">{errors.deviceNumber}</small>}
        </div>

        <div className="mb-3">
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

        <div className="mb-3">
          <label className="form-label">Problem Description</label>
          <textarea className="form-control" name="description" rows="3" value={formData.description} onChange={handleChange}></textarea>
          {errors.description && <small className="text-danger">{errors.description}</small>}
        </div>

        <div className="mb-3">
          <label className="form-label me-3">Action Type:</label>
          <div className="form-check form-check-inline">
            <input className="form-check-input" type="radio" name="action" value="Removal" checked={formData.action === 'Removal'} onChange={handleChange} />
            <label className="form-check-label">Removal</label>
          </div>
          <div className="form-check form-check-inline">
            <input className="form-check-input" type="radio" name="action" value="Maintenance" checked={formData.action === 'Maintenance'} onChange={handleChange} />
            <label className="form-check-label">Maintenance</label>
          </div>
          {errors.action && <small className="text-danger d-block">{errors.action}</small>}
        </div>

        <div className="mb-3">
          <label className="form-label me-3">Priority:</label>
          <div className="form-check form-check-inline">
            <input className="form-check-input" type="radio" name="priority" value="High" checked={formData.priority === 'High'} onChange={handleChange} />
            <label className="form-check-label">High</label>
          </div>
          <div className="form-check form-check-inline">
            <input className="form-check-input" type="radio" name="priority" value="Medium" checked={formData.priority === 'Medium'} onChange={handleChange} />
            <label className="form-check-label">Medium</label>
          </div>
          <div className="form-check form-check-inline">
            <input className="form-check-input" type="radio" name="priority" value="Low" checked={formData.priority === 'Low'} onChange={handleChange} />
            <label className="form-check-label">Low</label>
          </div>
          {errors.priority && <small className="text-danger d-block">{errors.priority}</small>}
        </div>
        <div className='butan'>
        <button type="submit" className="btn btn-primary">Submit Report</button>
        </div>
      </form>
    </div>
  );
}

export default ReportForm;
