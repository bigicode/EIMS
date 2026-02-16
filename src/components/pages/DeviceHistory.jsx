import React, { useContext } from 'react';
import { useParams } from 'react-router-dom';
import { DevicesContext } from '../../contexts/DevicesContext';
import { lifecycleService } from '../../services/lifecycleService';

function DeviceHistory() {
  const { id } = useParams();
  const { devices } = useContext(DevicesContext);
  const device = devices.find((item) => String(item.id) === String(id));
  const lifecycleData = lifecycleService.getByDeviceId(id);
  const actions = lifecycleData.actions
    ? String(lifecycleData.actions)
        .split(';')
        .map((action, index) => ({
          date: lifecycleData.reportedDate || `Step ${index + 1}`,
          action: action.trim(),
        }))
    : [];

  if (!device) {
    return (
      <div className="container mt-4">
        <h3>Device History</h3>
        <div className="alert alert-warning">Device not found.</div>
      </div>
    );
  }

  return (
    <div className="container mt-4">
      <h3 className="mb-4">Device Lifecycle & History: {device.name}</h3>
      <table className="table table-bordered w-75 mx-auto bg-white shadow-sm">
        <tbody>
          <tr>
            <th>Reported Inactive</th>
            <td>{lifecycleData.reportedDate || '-'}</td>
          </tr>
          <tr>
            <th>Issue</th>
            <td>{lifecycleData.issue || '-'}</td>
          </tr>
          <tr>
            <th>Actions Taken</th>
            <td>
              <ul className="mb-0">
                {actions.map((a, idx) => (
                  <li key={idx}><strong>{a.date}:</strong> {a.action}</li>
                ))}
                {actions.length === 0 && <li>No actions recorded.</li>}
              </ul>
            </td>
          </tr>
          <tr>
            <th>Disposal/Final Status</th>
            <td>{lifecycleData.disposal || '-'}</td>
          </tr>
        </tbody>
      </table>
    </div>
  );
}

export default DeviceHistory;
