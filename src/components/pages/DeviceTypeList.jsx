import React, { useContext, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { DevicesContext } from '../../contexts/DevicesContext';

function DeviceTypeList() {
  const { type } = useParams();
  const { devices } = useContext(DevicesContext);
  const navigate = useNavigate();
  const [filter, setFilter] = useState('all');

  const filteredDevices = devices.filter(device => {
    const matchesType = device.type.toLowerCase() === type.toLowerCase();
    if (filter === 'active') return matchesType && device.active;
    if (filter === 'inactive') return matchesType && !device.active;
    return matchesType;
  });

  return (
    <div className="container mt-4">
      <h3 className="mb-4">{type} Devices</h3>
      <div className="mb-3 d-flex align-items-center">
        <label className="me-2">Filter:</label>
        <select className="form-select w-auto" value={filter} onChange={e => setFilter(e.target.value)}>
          <option value="all">All</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
      <table className="table table-bordered bg-white shadow-sm">
        <thead>
          <tr>
            <th>Name</th>
            <th>IMEI</th>
            <th>Device Number</th>
            <th>Office</th>
            <th>Date Registered</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {filteredDevices.map(device => (
            <tr key={device.id}>
              <td>{device.name}</td>
              <td>{device.imei}</td>
              <td>{device.deviceNumber}</td>
              <td>{device.office}</td>
              <td>{device.dateRegistered}</td>
              <td>
                <span className={`badge ${device.active ? 'bg-success' : 'bg-secondary'}`}>{device.active ? 'Active' : 'Inactive'}</span>
              </td>
              <td>
                <button className="btn btn-info btn-sm me-2" onClick={() => navigate(`/devices/view/${device.id}`)}>View</button>
                <button className="btn btn-warning btn-sm me-2" onClick={() => navigate(`/device/${device.id}/edit`)}>Edit</button>
              </td>
            </tr>
          ))}
          {filteredDevices.length === 0 && (
            <tr>
              <td colSpan="7" className="text-center">No devices found.</td>
            </tr>
          )}
        </tbody>
      </table>
    </div>
  );
}

export default DeviceTypeList;