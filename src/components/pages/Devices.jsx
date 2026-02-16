import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useDevices } from '../../hooks/useDevices';
import { useNotifications } from '../../hooks/useNotifications';
import { useUser } from '../../hooks/useUser';
import './Devices.css'; // Import custom CSS instead of Bootstrap
import { IoAddCircleOutline } from "react-icons/io5";

function Devices() {
  const navigate = useNavigate();
  const { devices, deleteDevice, loading, error } = useDevices();
  const { user } = useUser();
  const { notifications, addToast } = useNotifications();
  const [searchTerm, setSearchTerm] = useState('');
  const [filterType, setFilterType] = useState('All');
  const [filterStatus, setFilterStatus] = useState('All');
  const [showStats, setShowStats] = useState(true);
  
  // Calculate device statistics
  const typeCounts = devices.reduce((acc, device) => {
    acc[device.type] = (acc[device.type] || 0) + 1;
    return acc;
  }, {});

  const statusCounts = devices.reduce((acc, device) => {
    const status = device.active ? 'Active' : 'Inactive';
    acc[status] = (acc[status] || 0) + 1;
    return acc;
  }, {});
  
  const issueCount = notifications.filter(n => n.status === 'unresolved').length;
  const maintenanceNeeded = devices.filter(d => !d.active).length;
  
  // Calculate devices requiring maintenance soon (simulation)
  const maintenanceSoon = Math.floor(devices.length * 0.15);
  
  // Card data for device types
  const cardData = [
    { type: 'Printer', count: typeCounts['Printer'] || 0, icon: 'bi-printer', bgColor: 'card-primary' },
    { type: 'MonitorScreen', count: typeCounts['MonitorScreen'] || 0, icon: 'bi-display', bgColor: 'card-info' },
    { type: 'Desktop', count: typeCounts['Desktop'] || 0, icon: 'bi-pc', bgColor: 'card-success' },
    { type: 'Scanner', count: typeCounts['Scanner'] || 0, icon: 'bi-scanner', bgColor: 'card-warning' },
    { type: 'Switches', count: typeCounts['Switches'] || 0, icon: 'bi-toggle-on', bgColor: 'card-success' },
    { type: 'Router', count: typeCounts['Router'] || 0, icon: 'bi-router', bgColor: 'card-danger' },
    { type: 'PhotocopyMachines', count: typeCounts['PhotocopyMachines'] || 0, icon: 'bi-copy', bgColor: 'card-secondary' },
    { type: 'Projector', count: typeCounts['Projector'] || 0, icon: 'bi-camera-video', bgColor: 'card-dark' },
  ];

  // Summary cards for admin dashboard
  const summaryCards = [
    { title: 'Total Devices', count: devices.length, icon: 'bi-devices', bgColor: 'card-primary' },
    { title: 'Active Devices', count: statusCounts['Active'] || 0, icon: 'bi-check-circle', bgColor: 'card-success' },
    { title: 'Inactive Devices', count: statusCounts['Inactive'] || 0, icon: 'bi-x-circle', bgColor: 'card-danger' },
    { title: 'Reported Issues', count: issueCount, icon: 'bi-exclamation-triangle', bgColor: 'card-warning' },
    { title: 'Maintenance Needed', count: maintenanceNeeded, icon: 'bi-tools', bgColor: 'card-info' },
    { title: 'Maintenance Soon', count: maintenanceSoon, icon: 'bi-clock-history', bgColor: 'card-secondary' },
  ];

  // Filter devices based on search term and filters
  const filteredDevices = devices.filter(device => {
    const matchesSearch = 
      device.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      device.imei.toLowerCase().includes(searchTerm.toLowerCase()) ||
      device.deviceNumber.toLowerCase().includes(searchTerm.toLowerCase()) ||
      device.office.toLowerCase().includes(searchTerm.toLowerCase());
    
    const matchesType = filterType === 'All' || device.type === filterType;
    const matchesStatus = filterStatus === 'All' || 
      (filterStatus === 'Active' && device.active) || 
      (filterStatus === 'Inactive' && !device.active);
    
    return matchesSearch && matchesType && matchesStatus;
  });

  const handleCardClick = (type) => {
    setFilterType(type);
    setShowStats(false);
  };

  const handleView = (device) => {
    navigate(`/devices/view/${device.id}`);
  };

  const handleEdit = (device) => {
    navigate(`/device/${device.id}/edit`);
  };

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this device?')) {
      await deleteDevice(id);
      addToast('Device deleted successfully.', 'success');
    }
  };

  const isAdmin = user && user.role === 'admin';

  if (loading) {
    return (
      <div className="container mt-4">
        <div className="alert alert-info">Loading devices...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="container mt-4">
        <div className="alert alert-danger">{error}</div>
      </div>
    );
  }

  return (
    <div className="devices-dashboard">
      {/* Admin Header with Welcome Message */}
      <div className="dashboard-header">
        <div className="welcome-section">
          <h2>Electronic Integration Management System</h2>
          <p>Welcome, {user ? user.name || 'Admin' : 'Guest'}! {isAdmin ? '(Administrator)' : ''}</p>
        </div>
        <div className="date-section">
          <p>{new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
        </div>
      </div>

      {/* Dashboard Controls */}
      <div className="dashboard-controls">
        <div className="search-filter-section">
          <input 
            type="text" 
            placeholder="Search devices..." 
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="search-input"
          />
          <select 
            value={filterType} 
            onChange={(e) => setFilterType(e.target.value)}
            className="filter-select"
          >
            <option value="All">All Types</option>
            {Object.keys(typeCounts).map(type => (
              <option key={type} value={type}>{type}</option>
            ))}
          </select>
          <select 
            value={filterStatus} 
            onChange={(e) => setFilterStatus(e.target.value)}
            className="filter-select"
          >
            <option value="All">All Status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
          <button 
            className="view-toggle-btn"
            onClick={() => setShowStats(!showStats)}
          >
            {showStats ? 'Hide Statistics' : 'Show Statistics'}
          </button>
        </div>
        <div className="action-buttons">
          <div className='add-batan'>
          <button 
            className="btnadd"
            onClick={() => navigate('/registerdevice')}
          >
            <i ><IoAddCircleOutline /></i> 
          </button>
          </div>
          {isAdmin && (
            <button 
              className="action-btn export-btn"
              onClick={() => alert('Export functionality would be implemented here')}
            >
              <i className="bi bi-download"></i> Export Data
            </button>
          )}
        </div>
      </div>

      {/* Dashboard Summary Cards - Only visible to admin */}
      {isAdmin && showStats && (
        <div className="summary-section">
          <h3>System Overview</h3>
          <div className="summary-cards">
            {summaryCards.map((card, index) => (
              <div className={`summary-card ${card.bgColor}`} key={index}>
                <div className="card-content">
                  <div className="card-info">
                    <h4>{card.title}</h4>
                    <h2>{card.count}</h2>
                  </div>
                  <div className="card-icon">
                    <i className={`bi ${card.icon}`}></i>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Device Type Cards */}
      <div className="device-categories">
        <h3>Device Categories</h3>
        <div className="device-cards">
          {cardData.map((card, index) => (
            <div 
              className={`device-card ${card.bgColor}`} 
              key={index}
              onClick={() => handleCardClick(card.type)}
            >
              <div className="card-content">
                <div className="card-info">
                  <h4>{card.type}</h4>
                  <h2 className='count'>{card.count}</h2>
                </div>
                <div className="card-icon">
                  <i className={`bi ${card.icon}`}></i>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Device Table */}
      <div className="device-table-section">
        <h3>Device Inventory {filterType !== 'All' ? `- ${filterType}` : ''}</h3>
        <div className="table-container">
          <table className="device-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>IMEI</th>
                <th>Device No.</th>
                <th>Office</th>
                <th>Date Registered</th>
                <th>Type</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {filteredDevices.length > 0 ? (
                filteredDevices.map((device) => (
                  <tr key={device.id}>
                    <td>{device.name}</td>
                    <td>{device.imei}</td>
                    <td>{device.deviceNumber}</td>
                    <td>{device.office}</td>
                    <td>{device.dateRegistered}</td>
                    <td>{device.type}</td>
                    <td>
                      <span className={`status-badge ${device.active ? 'status-active' : 'status-inactive'}`}>
                        {device.active ? 'Active' : 'Inactive'}
                      </span>
                    </td>
                    <td className="action-cell">
                      <button className="table-btn view-btn" onClick={() => handleView(device)}>
                        <i className="bi bi-eye"></i>
                      </button>
                      {isAdmin && (
                        <>
                          <button className="table-btn edit-btn" onClick={() => handleEdit(device)}>
                            <i className="bi bi-pencil"></i>
                          </button>
                          <button className="table-btn delete-btn" onClick={() => handleDelete(device.id)}>
                            <i className="bi bi-trash"></i>
                          </button>
                        </>
                      )}
                      <button 
                        className="table-btn history-btn" 
                        title="View History" 
                        onClick={() => navigate(`/devices/history/${device.id}`)}
                      >
                        <i className="bi bi-clock-history"></i>
                      </button>
                      {isAdmin && (
                        <button 
                          className="table-btn lifecycle-btn" 
                          title="Manage Lifecycle" 
                          onClick={() => navigate(`/devices/lifecycle/${device.id}`)}
                        >
                          <i className="bi bi-arrow-repeat"></i>
                        </button>
                      )}
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="8" className="no-data">No devices found matching your criteria.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Maintenance Prediction Section - Admin Only */}
      {isAdmin && (
        <div className="maintenance-prediction">
          <h3>Predictive Maintenance</h3>
          <div className="prediction-cards">
            <div className="prediction-card">
              <h4>Devices Requiring Attention</h4>
              <div className="prediction-content">
                <div className="prediction-chart">
                  {/* Simplified chart representation */}
                  <div className="chart-bar" style={{height: `${devices.length ? (maintenanceNeeded/devices.length)*100 : 0}%`}}></div>
                  <div className="chart-bar warning" style={{height: `${devices.length ? (maintenanceSoon/devices.length)*100 : 0}%`}}></div>
                  <div className="chart-bar good" style={{height: `${devices.length ? ((devices.length-maintenanceNeeded-maintenanceSoon)/devices.length)*100 : 0}%`}}></div>
                </div>
                <div className="prediction-stats">
                  <div className="stat-item">
                    <span className="stat-label">Immediate Attention:</span>
                    <span className="stat-value">{maintenanceNeeded}</span>
                  </div>
                  <div className="stat-item">
                    <span className="stat-label">Maintenance Soon:</span>
                    <span className="stat-value">{maintenanceSoon}</span>
                  </div>
                  <div className="stat-item">
                    <span className="stat-label">Healthy:</span>
                    <span className="stat-value">{devices.length - maintenanceNeeded - maintenanceSoon}</span>
                  </div>
                </div>
              </div>
              <button 
                className="view-all-btn"
                onClick={() => navigate('/devices?filter=maintenance')}
              >
                View All Maintenance
              </button>
            </div>

            <div className="prediction-card">
              <h4>Recent Issues</h4>
              <ul className="issues-list">
                {notifications.slice(0, 3).map(notification => (
                  <li key={notification.id} className="issue-item">
                    <div className="issue-header">
                      <span className={`priority-indicator priority-${notification.priority || 'medium'}`}></span>
                      <span className="issue-title">{notification.title}</span>
                    </div>
                    <p className="issue-desc">{notification.description}</p>
                    <div className="issue-meta">
                      <span>Device: {notification.deviceNumber}</span>
                      <span>Reported: {notification.dateReported}</span>
                    </div>
                  </li>
                ))}
                {notifications.length === 0 && (
                  <li className="no-issues">No issues reported</li>
                )}
              </ul>
              <button 
                className="view-all-btn"
                onClick={() => navigate('/notification')}
              >
                View All Issues
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default Devices;
