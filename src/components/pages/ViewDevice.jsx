import React, { useContext } from 'react';
import { useParams } from 'react-router-dom';
import { DevicesContext } from '../../contexts/DevicesContext';
import TMDLogo from '../TMD logo.png';
import './ViewDevice.css'; // Import CSS mpya

function ViewDevice() {
  const { id } = useParams();
  const { devices } = useContext(DevicesContext);
  const device = devices.find((d) => String(d.id) === String(id));

  if (!device) {
    return (
      <div className="view-device-container">
        <h3 className="view-device-title">Device Details</h3>
        <div className="error-message">Device not found.</div>
      </div>
    );
  }

  return (
    <div className="view-device-container">
      <h3 className="view-device-title">Device Details</h3>
      <div className="device-image-container">
        <img className="device-image" src={TMDLogo} alt="Device" />
      </div>
      <table className="device-details-table">
        <tbody>
          <tr>
            <th>Name</th>
            <td>{device.name}</td>
          </tr>
          <tr>
            <th>IMEI</th>
            <td>{device.imei}</td>
          </tr>
          <tr>
            <th>Device Number</th>
            <td>{device.deviceNumber}</td>
          </tr>
          <tr>
            <th>Office</th>
            <td>{device.office}</td>
          </tr>
          <tr>
            <th>Date Registered</th>
            <td>{device.dateRegistered}</td>
          </tr>
          <tr>
            <th>Status</th>
            <td>
              <span className={`status-badge ${device.active ? 'status-active' : 'status-inactive'}`}>
                {device.active ? 'Active' : 'Inactive'}
              </span>
            </td>
          </tr>
          <tr>
            <th>Type</th>
            <td>{device.type}</td>
          </tr>
        </tbody>
      </table>
    </div>
  );
}

export default ViewDevice;
