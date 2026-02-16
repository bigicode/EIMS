import React, { useState, useContext, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { DevicesContext } from '../../contexts/DevicesContext';
import { UserContext } from '../../contexts/UserContext';
import { NotificationsContext } from '../../contexts/NotificationsContext';
import { lifecycleService } from '../../services/lifecycleService';
import './DeviceLifecycleForm.css';

function DeviceLifecycleForm() {
  const { deviceId } = useParams();
  const navigate = useNavigate();
  const { devices } = useContext(DevicesContext);
  const { user } = useContext(UserContext);
  const { addNotification } = useContext(NotificationsContext);

  const [device, setDevice] = useState(null);
  const [formData, setFormData] = useState({
    reportedDate: '',
    issue: '',
    actions: '',
    disposal: '',
    notes: ''
  });
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(true);
  const [success, setSuccess] = useState(false);

  useEffect(() => {
    if (!user || user.role !== 'admin') {
      navigate('/devices');
      return;
    }

    const selectedDevice = devices.find((d) => String(d.id) === String(deviceId));
    if (selectedDevice) {
      setDevice(selectedDevice);
      // Load existing lifecycle data if any
      const existingData = lifecycleService.getByDeviceId(deviceId);
      if (existingData && Object.keys(existingData).length > 0) {
        setFormData(existingData);
      }
    }
    setLoading(false);
  }, [deviceId, devices, user, navigate]);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
    // Clear error when user starts typing
    if (errors[name]) {
      setErrors(prev => ({
        ...prev,
        [name]: ''
      }));
    }
  };

  const validateForm = () => {
    const newErrors = {};
    
    if (!device.active && !formData.disposal) {
      newErrors.disposal = 'Disposal information is required for inactive devices';
    }
    
    if (device.active && !formData.issue && formData.disposal) {
      newErrors.disposal = 'Cannot set disposal for active devices without issues';
    }

    if (formData.issue && !formData.reportedDate) {
      newErrors.reportedDate = 'Reported date is required when reporting an issue';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    
    if (!validateForm()) return;

    lifecycleService.saveByDeviceId(deviceId, formData);

    // Create notification
    addNotification({
      title: 'Lifecycle Update',
      description: `Device ${device.name} lifecycle information has been updated.`,
      imei: device.imei,
      deviceNumber: device.deviceNumber,
      type: device.type,
      action: 'Maintenance',
      priority: 'Low',
      dateReported: new Date().toISOString().split('T')[0],
      status: 'resolved'
    });

    setSuccess(true);
    setTimeout(() => {
      navigate('/deviceslifecycle');
    }, 1500);
  };

  if (loading) {
    return (
      <div className="lifecycle-form-container loading">
        <div className="spinner"></div>
        <p>Loading device information...</p>
      </div>
    );
  }

  if (!device) {
    return (
      <div className="lifecycle-form-container error">
        <h2>Error</h2>
        <p>Device not found</p>
        <button onClick={() => navigate('/devices')} className="back-button">
          Back to Devices
        </button>
      </div>
    );
  }

  return (
    <div className="lifecycle-form-container">
      {success ? (
        <div className="success-message">
          <i className="bi bi-check-circle"></i>
          <h2>Lifecycle Information Updated</h2>
          <p>Redirecting to lifecycle overview...</p>
        </div>
      ) : (
        <>
          <div className="form-header">
            <h2>Device Lifecycle Management</h2>
            <p>Update lifecycle information for {device.name}</p>
          </div>

          <div className="device-info-card">
            <h3>Device Information</h3>
            <div className="info-grid">
              <div className="info-item">
                <span className="label">Device Name:</span>
                <span className="value">{device.name}</span>
              </div>
              <div className="info-item">
                <span className="label">Device Number:</span>
                <span className="value">#{device.deviceNumber}</span>
              </div>
              <div className="info-item">
                <span className="label">Type:</span>
                <span className="value">{device.type}</span>
              </div>
              <div className="info-item">
                <span className="label">Status:</span>
                <span className={`value status ${device.active ? 'active' : 'inactive'}`}>
                  {device.active ? 'Active' : 'Inactive'}
                </span>
              </div>
              <div className="info-item">
                <span className="label">Office:</span>
                <span className="value">{device.office}</span>
              </div>
              <div className="info-item">
                <span className="label">IMEI:</span>
                <span className="value">{device.imei}</span>
              </div>
            </div>
          </div>

          <form onSubmit={handleSubmit} className="lifecycle-form">
            <div className="form-card">
              <h3>Issue Information</h3>
              <div className="form-group">
                <label htmlFor="reportedDate">Reported Date</label>
                <input
                  type="date"
                  id="reportedDate"
                  name="reportedDate"
                  value={formData.reportedDate}
                  onChange={handleInputChange}
                  className={errors.reportedDate ? 'error' : ''}
                />
                {errors.reportedDate && <div className="error-message">{errors.reportedDate}</div>}
              </div>

              <div className="form-group">
                <label htmlFor="issue">Issue Description</label>
                <textarea
                  id="issue"
                  name="issue"
                  value={formData.issue}
                  onChange={handleInputChange}
                  placeholder="Describe the issue..."
                  rows="4"
                />
                <div className="hint-text">Describe any problems or issues with the device</div>
              </div>
            </div>

            <div className="form-card">
              <h3>Actions & Resolution</h3>
              <div className="form-group">
                <label htmlFor="actions">Actions Taken</label>
                <textarea
                  id="actions"
                  name="actions"
                  value={formData.actions}
                  onChange={handleInputChange}
                  placeholder="List actions taken..."
                  rows="4"
                />
                <div className="hint-text">Separate multiple actions with semicolons (;)</div>
              </div>

              <div className="form-group">
                <label htmlFor="disposal">
                  Disposal Information
                  {!device.active && <span className="required">*</span>}
                </label>
                <select
                  id="disposal"
                  name="disposal"
                  value={formData.disposal}
                  onChange={handleInputChange}
                  className={errors.disposal ? 'error' : ''}
                >
                  <option value="">Select disposal status</option>
                  <option value="Pending Disposal">Pending Disposal</option>
                  <option value="Recycled">Recycled</option>
                  <option value="Donated">Donated</option>
                  <option value="Sold">Sold</option>
                  <option value="Destroyed">Destroyed</option>
                </select>
                {errors.disposal && <div className="error-message">{errors.disposal}</div>}
              </div>
            </div>

            <div className="form-card">
              <h3>Additional Information</h3>
              <div className="form-group">
                <label htmlFor="notes">Notes</label>
                <textarea
                  id="notes"
                  name="notes"
                  value={formData.notes}
                  onChange={handleInputChange}
                  placeholder="Add any additional notes..."
                  rows="4"
                />
              </div>
            </div>

            <div className="form-actions">
              <button type="button" onClick={() => navigate('/deviceslifecycle')} className="cancel-button">
                Cancel
              </button>
              <button type="submit" className="submit-button">
                Save Lifecycle Information
              </button>
            </div>
          </form>
        </>
      )}
    </div>
  );
}

export default DeviceLifecycleForm;
