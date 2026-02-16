import React, { useContext, useState, useEffect } from 'react';
import { UserContext } from '../../contexts/UserContext';
import { useNavigate } from 'react-router-dom';
import { NotificationsContext } from '../../contexts/NotificationsContext';
import './Userprofile.css';

const Userprofile = () => {
  const { user, updateUser, logout } = useContext(UserContext);
  const { addNotification } = useContext(NotificationsContext);
  const navigate = useNavigate();
  
  // Redirect if not logged in
  useEffect(() => {
    if (!user) {
      navigate('/signin');
    }
  }, [user, navigate]);

  const [formData, setFormData] = useState({
    email: user?.email || '',
    username: user?.username || user?.email || '',
    role: user?.role || user?.userType || '',
    password: '',
    confirmPassword: ''
  });
  
  const [profilePic, setProfilePic] = useState(user?.profilePic || null);
  const [isEditing, setIsEditing] = useState(false);
  const [errors, setErrors] = useState({});
  const [successMessage, setSuccessMessage] = useState('');

  // Mock data for activity summary
  const lastLogin = new Date().toLocaleString();
  const activitySummary = {
    devicesRegistered: 12,
    issuesReported: 5,
    lastActivity: '2 hours ago'
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
    
    // Clear error when user types
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }));
    }
  };

  const handlePicChange = (e) => {
    const file = e.target.files[0];
    if (file) {
      if (file.size > 5000000) { // 5MB limit
        setErrors(prev => ({ ...prev, profilePic: 'Image size should be less than 5MB' }));
        return;
      }
      
      const reader = new FileReader();
      reader.onloadend = () => {
        setProfilePic(reader.result);
        setErrors(prev => ({ ...prev, profilePic: '' }));
      };
      reader.readAsDataURL(file);
    }
  };

  const validateForm = () => {
    const newErrors = {};
    
    if (!formData.email) {
      newErrors.email = 'Email is required';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Email is invalid';
    }
    
    if (!formData.username) {
      newErrors.username = 'Username is required';
    }
    
    if (formData.password && formData.password.length < 6) {
      newErrors.password = 'Password must be at least 6 characters';
    }
    
    if (formData.password && formData.password !== formData.confirmPassword) {
      newErrors.confirmPassword = 'Passwords do not match';
    }
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSave = () => {
    if (!validateForm()) return;
    
    const updatedUser = {
      ...user,
      email: formData.email,
      username: formData.username,
      role: formData.role,
      userType: formData.role,
      profilePic: profilePic
    };
    
    // Update password if provided
    if (formData.password) {
      updatedUser.password = formData.password;
    }
    
    updateUser(updatedUser);
    setIsEditing(false);
    setSuccessMessage('Profile updated successfully');
    
    // Add notification
    addNotification({
      title: 'Profile Updated',
      description: 'Your profile information has been updated successfully.',
      type: 'info',
      dateCreated: new Date().toISOString()
    });
    
    // Clear success message after 3 seconds
    setTimeout(() => {
      setSuccessMessage('');
    }, 3000);
  };

  const handleLogout = () => {
    logout();
    navigate('/signin');
  };

  return (
    <div className="profile-container">
      <h2 className="profile-title">User Profile</h2>
      
      {successMessage && (
        <div className="success-message">
          {successMessage}
        </div>
      )}
      
      <div className="profile-content">
        <div className="profile-sidebar">
          <div className="profile-image-container">
            <img 
              src={profilePic || 'https://via.placeholder.com/150?text=User'} 
              alt="Profile Avatar" 
              className="profile-image"
              onClick={() => document.getElementById('profilePicInput').click()}
            />
            <div className="image-upload-text">
              Click to change picture
            </div>
            <input
              type="file"
              id="profilePicInput"
              className="image-upload"
              accept="image/*"
              onChange={handlePicChange}
            />
            {errors.profilePic && <div className="error-message">{errors.profilePic}</div>}
          </div>
          
          <div className="user-stats">
            <h3>Activity Summary</h3>
            <div className="stat-item">
              <span className="stat-label">Last Login:</span>
              <span className="stat-value">{lastLogin}</span>
            </div>
            <div className="stat-item">
              <span className="stat-label">Devices Registered:</span>
              <span className="stat-value">{activitySummary.devicesRegistered}</span>
            </div>
            <div className="stat-item">
              <span className="stat-label">Issues Reported:</span>
              <span className="stat-value">{activitySummary.issuesReported}</span>
            </div>
            <div className="stat-item">
              <span className="stat-label">Last Activity:</span>
              <span className="stat-value">{activitySummary.lastActivity}</span>
            </div>
          </div>
        </div>
        
        <div className="profile-form">
          <div className="form-header">
            <h3>Personal Information</h3>
            <button 
              type="button" 
              className="edit-button"
              onClick={() => setIsEditing(!isEditing)}
            >
              {isEditing ? 'Cancel' : 'Edit'}
            </button>
          </div>
          
          <div className="form-group">
            <label className="form-label">Email</label>
            <input
              type="email"
              className={`form-control ${errors.email ? 'error' : ''}`}
              name="email"
              value={formData.email}
              onChange={handleChange}
              disabled={!isEditing}
            />
            {errors.email && <div className="error-message">{errors.email}</div>}
          </div>
          
          <div className="form-group">
            <label className="form-label">Username</label>
            <input
              type="text"
              className={`form-control ${errors.username ? 'error' : ''}`}
              name="username"
              value={formData.username}
              onChange={handleChange}
              disabled={!isEditing}
            />
            {errors.username && <div className="error-message">{errors.username}</div>}
          </div>
          
          <div className="form-group">
            <label className="form-label">Role</label>
            <input
              type="text"
              className="form-control"
              name="role"
              value={formData.role}
              disabled
            />
            <div className="form-hint">Role cannot be changed</div>
          </div>
          
          {isEditing && (
            <div className="password-section">
              <h4>Change Password (Optional)</h4>
              <div className="form-group">
                <label className="form-label">New Password</label>
                <input
                  type="password"
                  className={`form-control ${errors.password ? 'error' : ''}`}
                  name="password"
                  value={formData.password}
                  onChange={handleChange}
                  placeholder="Leave blank to keep current password"
                />
                {errors.password && <div className="error-message">{errors.password}</div>}
              </div>
              
              <div className="form-group">
                <label className="form-label">Confirm New Password</label>
                <input
                  type="password"
                  className={`form-control ${errors.confirmPassword ? 'error' : ''}`}
                  name="confirmPassword"
                  value={formData.confirmPassword}
                  onChange={handleChange}
                />
                {errors.confirmPassword && <div className="error-message">{errors.confirmPassword}</div>}
              </div>
            </div>
          )}
          
          <div className="button-group">
            {isEditing && (
              <button
                type="button"
                className="submit-button"
                onClick={handleSave}
              >
                Save Changes
              </button>
            )}
            
            <button
              type="button"
              className="logout-button"
              onClick={handleLogout}
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Userprofile;
