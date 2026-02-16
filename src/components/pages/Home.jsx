import { useNavigate } from 'react-router-dom';
import { useDevices } from '../../hooks/useDevices';
import { useNotifications } from '../../hooks/useNotifications';
import { useUser } from '../../hooks/useUser';

const Home = () => {
  const navigate = useNavigate();
  const { user } = useUser();
  const { devices, loading: devicesLoading } = useDevices();
  const { notifications, loading: notificationsLoading } = useNotifications();

  if (devicesLoading || notificationsLoading) {
    return (
      <div className="container mt-4">
        <div className="alert alert-info">Loading dashboard...</div>
      </div>
    );
  }

  const activeDevices = devices.filter((device) => device.active).length;
  const unresolvedIssues = notifications.filter((item) => item.status === 'unresolved').length;

  return (
    <div className="container mt-4">
      <div className="mb-4">
        <h2>Dashboard</h2>
        <p className="text-muted">Welcome {user?.name || user?.email || 'User'}.</p>
      </div>

      <div className="row g-3">
        <div className="col-md-4">
          <div className="card shadow-sm">
            <div className="card-body">
              <h5 className="card-title">Total Devices</h5>
              <h3>{devices.length}</h3>
            </div>
          </div>
        </div>
        <div className="col-md-4">
          <div className="card shadow-sm">
            <div className="card-body">
              <h5 className="card-title">Active Devices</h5>
              <h3>{activeDevices}</h3>
            </div>
          </div>
        </div>
        <div className="col-md-4">
          <div className="card shadow-sm">
            <div className="card-body">
              <h5 className="card-title">Open Issues</h5>
              <h3>{unresolvedIssues}</h3>
            </div>
          </div>
        </div>
      </div>

      <div className="d-flex flex-wrap gap-2 mt-4">
        <button className="btn btn-primary" onClick={() => navigate('/devices')}>
          View Devices
        </button>
        <button className="btn btn-outline-primary" onClick={() => navigate('/report')}>
          Report Issue
        </button>
        <button className="btn btn-outline-secondary" onClick={() => navigate('/notification')}>
          Notifications
        </button>
      </div>
    </div>
  );
};

export default Home;
