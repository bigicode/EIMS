import React, { useEffect, useState } from 'react';
import { useNotifications } from '../../hooks/useNotifications';
import { useUser } from '../../hooks/useUser';
import './Notification.css'; // We'll create this custom CSS file

const Notification = () => {
  const { notifications, updateNotification, deleteNotification, loading, error } = useNotifications();
  const { user } = useUser();
  const [filteredNotifications, setFilteredNotifications] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [filterStatus, setFilterStatus] = useState('all');
  const [filterPriority, setFilterPriority] = useState('all');
  const [sortConfig, setSortConfig] = useState({ key: 'dateReported', direction: 'descending' });

  // Apply filters and sorting
  useEffect(() => {
    let result = [...notifications];
    
    // Apply search filter
    if (searchTerm) {
      result = result.filter(notification => 
        notification.title?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        notification.description?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        notification.imei?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        notification.deviceNumber?.toLowerCase().includes(searchTerm.toLowerCase())
      );
    }
    
    // Apply status filter
    if (filterStatus !== 'all') {
      result = result.filter(notification => notification.status === filterStatus);
    }
    
    // Apply priority filter
    if (filterPriority !== 'all') {
      result = result.filter(notification => notification.priority === filterPriority);
    }
    
    // Apply sorting
    if (sortConfig.key) {
      result.sort((a, b) => {
        if (a[sortConfig.key] < b[sortConfig.key]) {
          return sortConfig.direction === 'ascending' ? -1 : 1;
        }
        if (a[sortConfig.key] > b[sortConfig.key]) {
          return sortConfig.direction === 'ascending' ? 1 : -1;
        }
        return 0;
      });
    }
    
    setFilteredNotifications(result);
  }, [notifications, searchTerm, filterStatus, filterPriority, sortConfig]);

  const handleSort = (key) => {
    let direction = 'ascending';
    if (sortConfig.key === key && sortConfig.direction === 'ascending') {
      direction = 'descending';
    }
    setSortConfig({ key, direction });
  };

  const handleStatusChange = (id, newStatus) => {
    const notification = notifications.find(n => n.id === id);
    if (notification) {
      updateNotification({ ...notification, status: newStatus });
    }
  };

  const handleDeleteNotification = (id) => {
    if (window.confirm('Are you sure you want to delete this notification?')) {
      deleteNotification(id);
    }
  };

  // Get unique statuses and priorities for filter dropdowns
  const statuses = ['all', ...new Set(notifications.map(n => n.status))];
  const priorities = ['all', ...new Set(notifications.map(n => n.priority))];

  if (loading) {
    return (
      <div className="container mt-4">
        <div className="alert alert-info">Loading notifications...</div>
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
    <div className='notification-container'>
      <div className='notification-header'>
        <h2>Notifications & Reported Issues</h2>
        <div className='notification-stats'>
          <div className='stat-card'>
            <span className='stat-value'>{notifications.length}</span>
            <span className='stat-label'>Total</span>
          </div>
          <div className='stat-card urgent'>
            <span className='stat-value'>
              {notifications.filter(n => String(n.priority).toLowerCase() === 'high').length}
            </span>
            <span className='stat-label'>Urgent</span>
          </div>
          <div className='stat-card pending'>
            <span className='stat-value'>
              {notifications.filter(n => n.status === 'unresolved').length}
            </span>
            <span className='stat-label'>Pending</span>
          </div>
          <div className='stat-card resolved'>
            <span className='stat-value'>
              {notifications.filter(n => n.status === 'resolved').length}
            </span>
            <span className='stat-label'>Resolved</span>
          </div>
        </div>
      </div>

      <div className='notification-filters'>
        <div className='search-box'>
          <input
            type='text'
            placeholder='Search notifications...'
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
          <i className='bi bi-search'></i>
        </div>
        
        <div className='filter-controls'>
          <select 
            value={filterStatus} 
            onChange={(e) => setFilterStatus(e.target.value)}
            className='filter-select'
          >
            {statuses.map(status => (
              <option key={status} value={status}>
                {status === 'all' ? 'All Statuses' : status.charAt(0).toUpperCase() + status.slice(1)}
              </option>
            ))}
          </select>
          
          <select 
            value={filterPriority} 
            onChange={(e) => setFilterPriority(e.target.value)}
            className='filter-select'
          >
            {priorities.map(priority => (
              <option key={priority} value={priority}>
                {priority === 'all' ? 'All Priorities' : priority.charAt(0).toUpperCase() + priority.slice(1)}
              </option>
            ))}
          </select>
        </div>
      </div>

      {filteredNotifications.length === 0 ? (
        <div className='empty-state'>
          <i className='bi bi-bell-slash'></i>
          <p>No notifications found</p>
          {searchTerm || filterStatus !== 'all' || filterPriority !== 'all' ? (
            <button onClick={() => {
              setSearchTerm('');
              setFilterStatus('all');
              setFilterPriority('all');
            }} className='reset-filters-btn'>
              Reset Filters
            </button>
          ) : null}
        </div>
      ) : (
        <div className='notification-table-container'>
          <table className="notification-table">
            <thead>
              <tr>
                <th onClick={() => handleSort('title')} className='sortable'>
                  Device Name {sortConfig.key === 'title' && (
                    <i className={`bi bi-arrow-${sortConfig.direction === 'ascending' ? 'up' : 'down'}`}></i>
                  )}
                </th>
                <th>Description</th>
                <th>IMEI</th>
                <th>Device Number</th>
                <th onClick={() => handleSort('status')} className='sortable'>
                  Status {sortConfig.key === 'status' && (
                    <i className={`bi bi-arrow-${sortConfig.direction === 'ascending' ? 'up' : 'down'}`}></i>
                  )}
                </th>
                <th onClick={() => handleSort('dateReported')} className='sortable'>
                  Date Reported {sortConfig.key === 'dateReported' && (
                    <i className={`bi bi-arrow-${sortConfig.direction === 'ascending' ? 'up' : 'down'}`}></i>
                  )}
                </th>
                <th>Type</th>
                <th>Action</th>
                <th onClick={() => handleSort('priority')} className='sortable'>
                  Priority {sortConfig.key === 'priority' && (
                    <i className={`bi bi-arrow-${sortConfig.direction === 'ascending' ? 'up' : 'down'}`}></i>
                  )}
                </th>
                {user?.role === 'admin' && <th>Manage</th>}
              </tr>
            </thead>
            <tbody>
              {filteredNotifications.map((notification) => (
                <tr key={notification.id} className={`priority-${notification.priority}`}>
                  <td>{notification.title}</td>
                  <td className='description-cell'>
                    <div className='description-content'>{notification.description}</div>
                  </td>
                  <td>{notification.imei}</td>
                  <td>{notification.deviceNumber}</td>
                  <td>
                    <span className={`status-badge ${notification.status}`}>
                      {notification.status}
                    </span>
                  </td>
                  <td>{notification.dateReported}</td>
                  <td>{notification.type}</td>
                  <td>{notification.action}</td>
                  <td>
                    <span className={`priority-badge ${notification.priority}`}>
                      {notification.priority}
                    </span>
                  </td>
                  {user?.role === 'admin' && (
                    <td className='action-buttons'>
                      {notification.status !== 'resolved' ? (
                        <button 
                          onClick={() => handleStatusChange(notification.id, 'resolved')} 
                          className='resolve-btn'
                          title="Mark as resolved"
                        >
                          <i className='bi bi-check-circle'></i>
                        </button>
                      ) : (
                        <button 
                          onClick={() => handleStatusChange(notification.id, 'unresolved')} 
                          className='unresolve-btn'
                          title="Mark as unresolved"
                        >
                          <i className='bi bi-arrow-counterclockwise'></i>
                        </button>
                      )}
                      <button 
                        onClick={() => handleDeleteNotification(notification.id)} 
                        className='delete-btn'
                        title="Delete notification"
                      >
                        <i className='bi bi-trash'></i>
                      </button>
                    </td>
                  )}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
};

export default Notification;
