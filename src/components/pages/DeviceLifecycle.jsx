import React, { useContext, useEffect, useState } from 'react';
import { DevicesContext } from '../../contexts/DevicesContext';
import { UserContext } from '../../contexts/UserContext';
import { useNavigate } from 'react-router-dom';
import { lifecycleService } from '../../services/lifecycleService';
import './DeviceLifecycle.css';

function DeviceLifecycle() {
  const { devices } = useContext(DevicesContext);
  const { user } = useContext(UserContext);
  const [lifecycleDevices, setLifecycleDevices] = useState([]);
  const [filter, setFilter] = useState('all');
  const [searchTerm, setSearchTerm] = useState('');
  const [viewMode, setViewMode] = useState('table'); // 'table' or 'cards'
  const [sortConfig, setSortConfig] = useState({ key: 'reportedDate', direction: 'descending' });
  const [isLoading, setIsLoading] = useState(true);
  const navigate = useNavigate();
  const getLifecycle = (deviceId) => lifecycleService.getByDeviceId(deviceId);

  useEffect(() => {
    // Simulate loading for smoother transitions
    setIsLoading(true);
    
    // Aggregate all devices that have lifecycle data
    const withLifecycle = devices.filter(device => {
      return lifecycleService.hasData(device.id);
    });
    
    setLifecycleDevices(withLifecycle);
    
    // Simulate loading completion
    const timer = setTimeout(() => {
      setIsLoading(false);
    }, 500);
    
    return () => clearTimeout(timer);
  }, [devices]);

  // Filter and search functionality
  const filteredDevices = lifecycleDevices.filter(device => {
    const lifecycle = getLifecycle(device.id);
    
    // Filter by status
    const matchesFilter = 
      filter === 'all' || 
      (filter === 'active' && device.active) || 
      (filter === 'inactive' && !device.active) ||
      (filter === 'maintenance' && device.active && lifecycle.issue) ||
      (filter === 'disposal' && lifecycle.disposal);
    
    // Search functionality
    const matchesSearch = searchTerm === '' || [
      device.name,
      device.imei,
      device.deviceNumber,
      device.type,
      device.office,
      lifecycle.issue,
      lifecycle.actions,
      lifecycle.disposal
    ].some(field => 
      field?.toLowerCase().includes(searchTerm.toLowerCase())
    );
    
    return matchesFilter && matchesSearch;
  });

  // Sorting functionality
  const sortedDevices = [...filteredDevices].sort((a, b) => {
    const aLifecycle = getLifecycle(a.id);
    const bLifecycle = getLifecycle(b.id);
    
    let aValue, bValue;
    
    switch(sortConfig.key) {
      case 'name':
        aValue = a.name?.toLowerCase() || '';
        bValue = b.name?.toLowerCase() || '';
        break;
      case 'type':
        aValue = a.type?.toLowerCase() || '';
        bValue = b.type?.toLowerCase() || '';
        break;
      case 'stage':
        aValue = getLifecycleStage(a, aLifecycle);
        bValue = getLifecycleStage(b, bLifecycle);
        break;
      case 'reportedDate':
        aValue = aLifecycle.reportedDate || '0';
        bValue = bLifecycle.reportedDate || '0';
        break;
      case 'progress':
        aValue = getLifecycleProgress(a, aLifecycle);
        bValue = getLifecycleProgress(b, bLifecycle);
        break;
      default:
        aValue = a.name?.toLowerCase() || '';
        bValue = b.name?.toLowerCase() || '';
    }
    
    if (aValue < bValue) {
      return sortConfig.direction === 'ascending' ? -1 : 1;
    }
    if (aValue > bValue) {
      return sortConfig.direction === 'ascending' ? 1 : -1;
    }
    return 0;
  });

  // Handle sorting
  const requestSort = (key) => {
    let direction = 'ascending';
    if (sortConfig.key === key && sortConfig.direction === 'ascending') {
      direction = 'descending';
    }
    setSortConfig({ key, direction });
  };

  // Calculate lifecycle statistics
  const stats = {
    total: lifecycleDevices.length,
    active: lifecycleDevices.filter(d => d.active && !getLifecycle(d.id).issue).length,
    maintenance: lifecycleDevices.filter(d => d.active && getLifecycle(d.id).issue).length,
    inactive: lifecycleDevices.filter(d => !d.active && !getLifecycle(d.id).disposal).length,
    disposal: lifecycleDevices.filter(d => {
      const lifecycle = getLifecycle(d.id);
      return lifecycle.disposal && lifecycle.disposal.trim() !== '';
    }).length
  };

  // Handle view device lifecycle details
  const handleViewLifecycle = (deviceId) => {
    navigate(`/devices/lifecycle/${deviceId}`);
  };

  // Handle view device history
  const handleViewHistory = (deviceId) => {
    navigate(`/devices/history/${deviceId}`);
  };

  // Get lifecycle stage based on device data
  const getLifecycleStage = (device, lifecycle) => {
    if (!device.active && lifecycle.disposal) return 'Disposed';
    if (!device.active) return 'Inactive';
    if (device.active && lifecycle.issue) return 'Maintenance';
    return 'Active';
  };

  // Get stage color class
  const getStageColorClass = (stage) => {
    switch(stage) {
      case 'Active': return 'stage-active';
      case 'Maintenance': return 'stage-maintenance';
      case 'Inactive': return 'stage-inactive';
      case 'Disposed': return 'stage-disposed';
      default: return '';
    }
  };

  // Calculate lifecycle progress percentage
  const getLifecycleProgress = (device, lifecycle) => {
    // This is a simplified example - in a real app you might calculate based on
    // device age, warranty period, expected lifespan, etc.
    if (!device.active && lifecycle.disposal) return 100;
    if (!device.active) return 75;
    if (device.active && lifecycle.issue) return 50;
    return 25;
  };

  // Get sort indicator
  const getSortIndicator = (key) => {
    if (sortConfig.key !== key) return null;
    return sortConfig.direction === 'ascending' ? '↑' : '↓';
  };

  return (
    <div className="lifecycle-container">
      <div className="lifecycle-header">
        <h2>Device Lifecycle Management</h2>
        <p className="lifecycle-description">
          Track and manage the complete lifecycle of all registered devices from acquisition to disposal.
        </p>
      </div>

      <div className="lifecycle-stats-container">
        <div className="lifecycle-stats">
          <div className="stat-card total">
            <div className="stat-icon">
              <i className="bi bi-laptop"></i>
            </div>
            <div className="stat-content">
              <div className="stat-value">{stats.total}</div>
              <div className="stat-label">Total Devices</div>
            </div>
          </div>
          <div className="stat-card active">
            <div className="stat-icon">
              <i className="bi bi-check-circle"></i>
            </div>
            <div className="stat-content">
              <div className="stat-value">{stats.active}</div>
              <div className="stat-label">Active</div>
            </div>
          </div>
          <div className="stat-card maintenance">
            <div className="stat-icon">
              <i className="bi bi-tools"></i>
            </div>
            <div className="stat-content">
              <div className="stat-value">{stats.maintenance}</div>
              <div className="stat-label">In Maintenance</div>
            </div>
          </div>
          <div className="stat-card inactive">
            <div className="stat-icon">
              <i className="bi bi-dash-circle"></i>
            </div>
            <div className="stat-content">
              <div className="stat-value">{stats.inactive}</div>
              <div className="stat-label">Inactive</div>
            </div>
          </div>
          <div className="stat-card disposed">
            <div className="stat-icon">
              <i className="bi bi-trash"></i>
            </div>
            <div className="stat-content">
              <div className="stat-value">{stats.disposal}</div>
              <div className="stat-label">Disposed</div>
            </div>
          </div>
        </div>
      </div>

      <div className="lifecycle-controls">
        <div className="search-filter-container">
          <div className="search-container">
            <input 
              type="text" 
              placeholder="Search devices..." 
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="search-input"
            />
            
          </div>
          <div className="filter-container">
            <select 
              value={filter} 
              onChange={(e) => setFilter(e.target.value)}
              className="filter-select"
            >
              <option value="all">All Devices</option>
              <option value="active">Active Only</option>
              <option value="maintenance">In Maintenance</option>
              <option value="inactive">Inactive Only</option>
              <option value="disposal">Disposal Stage</option>
            </select>
          </div>
        </div>
        
        <div className="view-actions">
          <div className="view-toggle">
            <button 
              className={`view-btn ${viewMode === 'table' ? 'active' : ''}`}
              onClick={() => setViewMode('table')}
              title="Table View"
            >
              <i className="bi bi-table"></i>
            </button>
            <button 
              className={`view-btn ${viewMode === 'cards' ? 'active' : ''}`}
              onClick={() => setViewMode('cards')}
              title="Card View"
            >
              <i className="bi bi-grid-3x3-gap"></i>
            </button>
          </div>
          
          {user?.role === 'admin' && (
            <button 
              className="add-device-btn"
              onClick={() => navigate('/registerdevice')}
              title="Register New Device"
            >
              <i className="bi bi-plus-circle"></i> Add Device
            </button>
          )}
        </div>
      </div>

      {isLoading ? (
        <div className="loading-container">
          <div className="spinner"></div>
          <p>Loading device lifecycle data...</p>
        </div>
      ) : filteredDevices.length === 0 ? (
        <div className="empty-state">
          <i className="bi bi-laptop empty-icon"></i>
          <h3>No devices with lifecycle data found</h3>
          <p className="empty-description">
            {searchTerm || filter !== 'all' ? 
              'Try adjusting your search or filter criteria' : 
              'Start by adding lifecycle data to your devices'}
          </p>
          {user?.role === 'admin' && (
            <button 
              className="add-lifecycle-btn"
              onClick={() => navigate('/devices')}
            >
              <i className="bi bi-plus-circle"></i> Go to Devices
            </button>
          )}
        </div>
      ) : viewMode === 'table' ? (
        <div className="lifecycle-table-container">
          <table className="lifecycle-table">
            <thead>
              <tr>
                <th onClick={() => requestSort('name')}>
                  Device {getSortIndicator('name')}
                </th>
                <th>Details</th>
                <th onClick={() => requestSort('stage')}>
                  Lifecycle Stage {getSortIndicator('stage')}
                </th>
                <th>Issue</th>
                <th>Actions Taken</th>
                <th>Disposal Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {sortedDevices.map(device => {
                const lifecycle = getLifecycle(device.id);
                const stage = getLifecycleStage(device, lifecycle);
                const progress = getLifecycleProgress(device, lifecycle);
                
                return (
                  <tr key={device.id} className={`lifecycle-row ${getStageColorClass(stage)}-row`}>
                    <td>
                      <div className="device-name">
                        <i className={`bi bi-${device.type === 'Printer' ? 'printer' : 'laptop'} device-icon`}></i>
                        <span>{device.name}</span>
                      </div>
                    </td>
                    <td>
                      <div className="device-details">
                        <div><strong>ID:</strong> #{device.deviceNumber}</div>
                        <div><strong>IMEI:</strong> {device.imei}</div>
                        <div><strong>Type:</strong> {device.type}</div>
                        <div><strong>Office:</strong> {device.office}</div>
                      </div>
                    </td>
                    <td>
                      <div className="lifecycle-stage">
                        <span className={`stage-badge ${getStageColorClass(stage)}`}>{stage}</span>
                        <div className="progress-container">
                          <div 
                            className={`progress-bar ${getStageColorClass(stage)}-progress`}
                            style={{width: `${progress}%`}}
                          ></div>
                          <span className="progress-text">{progress}%</span>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div className="issue-cell">
                        {lifecycle.issue ? (
                          <>
                            <p className="issue-text">{lifecycle.issue}</p>
                            {lifecycle.reportedDate && (
                              <div className="reported-date">
                                <i className="bi bi-calendar3"></i> {lifecycle.reportedDate}
                              </div>
                            )}
                          </>
                        ) : (
                          <span className="no-data">No issues reported</span>
                        )}
                      </div>
                    </td>
                    <td>
                      <div className="actions-cell">
                        {lifecycle.actions ? (
                          <div className="actions-list">
                            {lifecycle.actions.split(';').map((action, index) => (
                              <div key={index} className="action-item">
                                <i className="bi bi-check-circle"></i> {action.trim()}
                              </div>
                            ))}
                          </div>
                        ) : (
                          <span className="no-data">No actions recorded</span>
                        )}
                      </div>
                    </td>
                    <td>
                      {lifecycle.disposal ? (
                        <span className={`disposal-badge ${lifecycle.disposal.toLowerCase().replace(/\s+/g, '-')}`}>
                          {lifecycle.disposal}
                        </span>
                      ) : (
                        <span className="no-data">Not disposed</span>
                      )}
                    </td>
                    <td>
                      <div className="action-buttons">
                        <button 
                          className="history-btn" 
                          onClick={() => handleViewHistory(device.id)}
                          title="View device history"
                        >
                          <i className="bi bi-clock-history"></i>
                        </button>
                        {user?.role === 'admin' && (
                          <button 
                            className="edit-btn" 
                            onClick={() => handleViewLifecycle(device.id)}
                            title="Edit lifecycle data"
                          >
                            <i className="bi bi-pencil"></i>
                          </button>
                        )}
                      </div>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      ) : (
        <div className="lifecycle-cards">
          {sortedDevices.map(device => {
            const lifecycle = getLifecycle(device.id);
            const stage = getLifecycleStage(device, lifecycle);
            const progress = getLifecycleProgress(device, lifecycle);
            
            return (
              <div key={device.id} className={`lifecycle-card ${getStageColorClass(stage)}-card`}>
                <div className="card-header">
                  <div className="device-name">
                    <i className={`bi bi-${device.type === 'Printer' ? 'printer' : 'laptop'} device-icon`}></i>
                    <h3>{device.name}</h3>
                  </div>
                  <span className={`stage-badge ${getStageColorClass(stage)}`}>{stage}</span>
                </div>
                
                <div className="card-body">
                  <div className="device-details">
                    <div><strong>ID:</strong> #{device.deviceNumber}</div>
                    <div><strong>IMEI:</strong> {device.imei}</div>
                    <div><strong>Type:</strong> {device.type}</div>
                    <div><strong>Office:</strong> {device.office}</div>
                  </div>
                  
                  <div className="lifecycle-progress">
                    <div className="progress-label">
                      <span>Lifecycle Progress</span>
                      <span>{progress}%</span>
                    </div>
                    <div className="progress-container">
                      <div 
                        className={`progress-bar ${getStageColorClass(stage)}-progress`}
                        style={{width: `${progress}%`}}
                      ></div>
                    </div>
                  </div>
                  
                  {lifecycle.issue && (
                    <div className="issue-section">
                      <h4><i className="bi bi-exclamation-triangle"></i> Reported Issue:</h4>
                      <p>{lifecycle.issue}</p>
                      {lifecycle.reportedDate && (
                        <div className="reported-date">
                          <i className="bi bi-calendar3"></i> Reported on: {lifecycle.reportedDate}
                        </div>
                      )}
                    </div>
                  )}
                  
                  {lifecycle.actions && (
                    <div className="actions-section">
                      <h4><i className="bi bi-tools"></i> Actions Taken:</h4>
                      <ul className="actions-list">
                        {lifecycle.actions.split(';').map((action, index) => (
                          <li key={index}>
                            <i className="bi bi-check-circle"></i> {action.trim()}
                          </li>
                        ))}
                      </ul>
                    </div>
                  )}
                  
                  {lifecycle.disposal && (
                    <div className="disposal-section">
                      <h4><i className="bi bi-trash"></i> Disposal Status:</h4>
                      <p className={`disposal-badge ${lifecycle.disposal.toLowerCase().replace(/\s+/g, '-')}`}>
                        {lifecycle.disposal}
                      </p>
                    </div>
                  )}
                </div>
                
                <div className="card-footer">
                  <button 
                    className="history-btn" 
                    onClick={() => handleViewHistory(device.id)}
                  >
                    <i className="bi bi-clock-history"></i> View History
                  </button>
                  {user?.role === 'admin' && (
                    <button 
                      className="edit-btn" 
                      onClick={() => handleViewLifecycle(device.id)}
                    >
                      <i className="bi bi-pencil"></i> Edit Lifecycle
                    </button>
                  )}
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}

export default DeviceLifecycle;
