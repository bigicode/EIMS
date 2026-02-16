import React from 'react';

function DeviceTable({ devices, onEdit, onDelete, onView }) {
  return (
    <table className="table table-striped">
      <thead>
        <tr>
          <th>Name</th>
          <th>IMEI</th>
          <th>Device No.</th>
          <th>Office</th>
          <th>Date Registered</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        {devices.map((device, index) => (
          <tr key={index}>
            <td>{device.name}</td>
            <td>{device.imei}</td>
            <td>{device.deviceNumber}</td>
            <td>{device.office}</td>
            <td>{device.dateRegistered}</td>
            <td>
              <span className={`badge ${device.active ? 'bg-success' : 'bg-secondary'}`}>
                {device.active ? 'Active' : 'Inactive'}
              </span>
            </td>
            <td>
              <button className="btn btn-sm btn-info me-2" onClick={() => onView(device)}>View</button>
              <button className="btn btn-sm btn-warning me-2" onClick={() => onEdit(device)}>Edit</button>
              <button className="btn btn-sm btn-danger" onClick={() => onDelete(device.id)}>Delete</button>
            </td>
          </tr>
        ))}
      </tbody>
    </table>
  );
}

export default DeviceTable;
