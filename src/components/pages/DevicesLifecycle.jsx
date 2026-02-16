import React, { useContext, useEffect, useState } from 'react';
import { DevicesContext } from '../../contexts/DevicesContext';
import { UserContext } from '../../contexts/UserContext';
import { useNavigate } from 'react-router-dom';
import { lifecycleService } from '../../services/lifecycleService';
import './DevicesLifecycle.css';

function DevicesLifecycle() {
  const { devices } = useContext(DevicesContext);
  const { user } = useContext(UserContext);
  const [lifecycleDevices, setLifecycleDevices] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [filterType, setFilterType] = useState('All');
  const [filterDisposal, setFilterDisposal] = useState('All');
  const [sortConfig, setSortConfig] = useState({ key: 'reportedDate', direction: 'descending' });
  const navigate = useNavigate();

  useEffect(() => {
    // Aggregate all devices that have lifecycle data
    const withLifecycle = devices.filter(device => {
      return lifecycleService.hasData(device.id);
    }).map(device => {
      const lifecycleData = lifecycleService.getByDeviceId(device.id);
      return { ...device, lifecycle: lifecycleData };
    });
    
    setLifecycleDevices(withLifecycle);
  }, [devices]);

  // Filter and sort devices
  const filteredDevices = lifecycleDevices.filter(device => {
    // Search filter
    const searchMatch = 
      device.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      device.imei.toLowerCase().includes(searchTerm.toLowerCase()) ||
      device.deviceNumber.toLowerCase().includes(searchTerm.toLowerCase()) ||
      (device.lifecycle.issue && device.lifecycle.issue.toLowerCase().includes(searchTerm.toLowerCase()));
    
    // Type filter
    const typeMatch = filterType === 'All' || device.type === filterType;
    
    // Disposal filter
    const disposalMatch = filterDisposal === 'All' || 
      (device.lifecycle.disposal && device.lifecycle.disposal.toLowerCase().includes(filterDisposal.toLowerCase()));
    
    return searchMatch && typeMatch && disposalMatch;
  }).sort((a, b) => {
    // Handle sorting
    const key = sortConfig.key;
    
    // Special handling for lifecycle properties
    if (key.startsWith('lifecycle.')) {
      const lifecycleKey = key.split('.')[1];
      const valueA = a.lifecycle[lifecycleKey] || '';
      const valueB = b.lifecycle[lifecycleKey] || '';
      
      if (valueA < valueB) {
        return sortConfig.direction === 'ascending' ? -1 : 1;
      }
      if (valueA > valueB) {
        return sortConfig.direction === 'ascending' ? 1 : -1;
      }
      return 0;
    }
    
    // Regular device properties
    if (a[key] < b[key]) {
      return sortConfig.direction === 'ascending' ? -1 : 1;
    }
    if (a[key] > b[key]) {
      return sortConfig.direction === 'ascending' ? 1 : -1;
    }
    return 0;
  });

  // Get unique device types for filter
  const deviceTypes = ['All', ...new Set(devices.map(device => device.type))];
  
  // Get unique disposal statuses for filter
  const disposalStatuses = ['All', ...new Set(lifecycleDevices
    .filter(device => device.lifecycle && device.lifecycle.disposal)
    .map(device => device.lifecycle.disposal)
  )];

  const handleSort = (key) => {
    let direction = 'ascending';
    if (sortConfig.key === key && sortConfig.direction === 'ascending') {
      direction = 'descending';
    }
    setSortConfig({ key, direction });
  };

  // Calculate lifecycle statistics
  const totalDevices = lifecycleDevices.length;
  const devicesWithIssues = lifecycleDevices.filter(d => d.lifecycle.issue && d.lifecycle.issue.trim() !== '').length;
  const devicesWithActions = lifecycleDevices.filter(d => d.lifecycle.actions && d.lifecycle.actions.trim() !== '').length;
  const disposedDevices = lifecycleDevices.filter(d => d.lifecycle.disposal && d.lifecycle.disposal.toLowerCase().includes('disposed')).length;

  return (
    <div className="lifecycle-container">
      <div className="lifecycle-header">
        <h2>Device Lifecycle Management</h2>
        <div className="lifecycle-stats">
          <div className="stat-card">
            <span className="stat-value">{totalDevices}</span>
            <span className="stat-label">Total Devices</span>
          </div>
          <div className="stat-card issues">
            <span className="stat-value">{devicesWithIssues}</span>
            <span className="stat-label">With Issues</span>
          </div>
          <div className="stat-card actions">
            <span className="stat-value">{devicesWithActions}</span>
            <span className="stat-label">With Actions</span>
          </div>
          <div className="stat-card disposed">
            <span className="stat-value">{disposedDevices}</span>
            <span className="stat-label">Disposed</span>
          </div>
        </div>
      </div>

      <div className="lifecycle-controls">
        <div className="search-box">
          <input
            type="text"
            placeholder="Search devices..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
          <i className="bi bi-search"></i>
        </div>
        
        <div className="filter-controls">
          <select 
            value={filterType} 
            onChange={(e) => setFilterType(e.target.value)}
            className="filter-select"
          >
            {deviceTypes.map(type => (
              <option key={type} value={type}>{type}</option>
            ))}
          </select>
          
          <select 
            value={filterDisposal} 
            onChange={(e) => setFilterDisposal(e.target.value)}
            className="filter-select"
          >
            {disposalStatuses.map(status => (
              <option key={status} value={status}>
                {status === 'All' ? 'All Disposal Statuses' : status}
              </option>
            ))}
          </select>
        </div>
      </div>

      {user?.role === 'admin' && (
        <div className="lifecycle-actions">
          <p className="lifecycle-info">
            <i className="bi bi-info-circle"></i>
            Device lifecycle management tracks the complete history of devices from registration to disposal.
          </p>
        </div>
      )}

      {filteredDevices.length === 0 ? (
        <div className="empty-state">
          <i className="bi bi-laptop"></i>
          <p>No devices with lifecycle data found</p>
          {searchTerm || filterType !== 'All' || filterDisposal !== 'All' ? (
            <button onClick={() => {
              setSearchTerm('');
              setFilterType('All');
              setFilterDisposal('All');
            }} className="reset-filters-btn">
              Reset Filters
            </button>
          ) : (
            <p className="empty-state-hint">Add lifecycle data to devices to track their complete history</p>
          )}
        </div>
      ) : (
        <div className="lifecycle-table-container">
          <table className="lifecycle-table">
            <thead>
              <tr>
                <th onClick={() => handleSort('name')} className="sortable">
                  Name {sortConfig.key === 'name' && (
                    <i className={`bi bi-arrow-${sortConfig.direction === 'ascending' ? 'up' : 'down'}`}></i>
                  )}
                </th>
                <th>IMEI</th>
                <th>Device Number</th>
                <th onClick={() => handleSort('type')} className="sortable">
                  Type {sortConfig.key === 'type' && (
                    <i className={`bi bi-arrow-${sortConfig.direction === 'ascending' ? 'up' : 'down'}`}></i>
                  )}
                </th>
                <th onClick={() => handleSort('lifecycle.reportedDate')} className="sortable">
                  Reported Date {sortConfig.key === 'lifecycle.reportedDate' && (
                    <i className={`bi bi-arrow-${sortConfig.direction === 'ascending' ? 'up' : 'down'}`}></i>
                  )}
                </th>
                <th>Issue</th>
                <th>Actions Taken</th>
                <th onClick={() => handleSort('lifecycle.disposal')} className="sortable">
                  Disposal Status {sortConfig.key === 'lifecycle.disposal' && (
                    <i className={`bi bi-arrow-${sortConfig.direction === 'ascending' ? 'up' : 'down'}`}></i>
                  )}
                </th>
                <th>Device Actions</th>
              </tr>
            </thead>
            <tbody>
              {filteredDevices.map(device => (
                <tr key={device.id}>
                  <td>{device.name}</td>
                  <td>{device.imei}</td>
                  <td>{device.deviceNumber}</td>
                  <td>
                    <span className="type-badge">{device.type}</span>
                  </td>
                  <td>{device.lifecycle.reportedDate || '-'}</td>
                  <td className="issue-cell">
                    <div className="issue-content">{device.lifecycle.issue || '-'}</div>
                  </td>
                  <td className="actions-cell">
                    <div className="actions-content">
                      {device.lifecycle.actions ? (
                        <ul className="actions-list">
                          {device.lifecycle.actions.split(';').map((action, index) => (
                            <li key={index}>{action.trim()}</li>
                          ))}
                        </ul>
                      ) : '-'}
                    </div>
                  </td>
                  <td>
                    <span className={`disposal-badge ${getDisposalClass(device.lifecycle.disposal)}`}>
                      {device.lifecycle.disposal || 'Not specified'}
                    </span>
                  </td>
                  <td className="action-buttons">
                    <button 
                      className="view-btn" 
                      onClick={() => navigate(`/devices/history/${device.id}`)}
                      title="View device history"
                    >
                      <i className="bi bi-clock-history"></i>
                    </button>
                    {user?.role === 'admin' && (
                      <button 
                        className="edit-btn" 
                        onClick={() => navigate(`/devices/lifecycle/${device.id}`)}
                        title="Edit lifecycle data"
                      >
                        <i className="bi bi-pencil"></i>
                      </button>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {user?.role === 'admin' && filteredDevices.length > 0 && (
        <div className="lifecycle-footer">
          <button 
            className="add-lifecycle-btn" 
            onClick={() => navigate('/devices')}
          >
            <i className="bi bi-plus-circle"></i> Add Lifecycle Data to Another Device
          </button>
        </div>
      )}
    </div>
  );
}

// Helper function to determine disposal badge class
function getDisposalClass(disposal) {
  if (!disposal) return 'unknown';
  
  const disposalLower = disposal.toLowerCase();
  if (disposalLower.includes('disposed') || disposalLower.includes('discarded')) {
    return 'disposed';
  } else if (disposalLower.includes('stored') || disposalLower.includes('warehouse')) {
    return 'stored';
  } else if (disposalLower.includes('sold') || disposalLower.includes('auction')) {
    return 'sold';
  } else if (disposalLower.includes('donated') || disposalLower.includes('charity')) {
    return 'donated';
  } else if (disposalLower.includes('recycled') || disposalLower.includes('recycle')) {
    return 'recycled';
  } else {
    return 'other';
  }
}

export default DevicesLifecycle;
