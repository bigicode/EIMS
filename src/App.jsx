import './App.css';
import { BrowserRouter as Router, Navigate, Route, Routes, useLocation } from 'react-router-dom';
import Navbar from './components/Navbar';
import { GiHamburgerMenu } from 'react-icons/gi';
import { useEffect, useRef, useState } from 'react';
import Header from './components/Header';
import Userprofile from './components/pages/Userprofile';
import Signin from './components/pages/Signin';
import Notification from './components/pages/Notification';
import Home from './components/pages/Home';
import Help from './components/pages/Help';
import Devices from './components/pages/Devices';
import RegisterDevicePage from './components/pages/RegisterDevicePage';
import DeviceLifecycleForm from './components/pages/DeviceLifecycleForm';
import ReportIssuePage from './components/pages/ReportIssuePage';
import 'bootstrap-icons/font/bootstrap-icons.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import { DevicesProvider } from './contexts/DevicesContext';
import { UserProvider } from './contexts/UserContext';
import { NotificationsProvider } from './contexts/NotificationsContext';
import ViewDevice from './components/pages/ViewDevice';
import DeviceTypeList from './components/pages/DeviceTypeList';
import DeviceHistory from './components/pages/DeviceHistory';
import DeviceLifecycle from './components/pages/DeviceLifecycle';
import EditDevice from './components/pages/EditDevice';
import ProtectedRoute from './components/common/ProtectedRoute';
import ToastContainer from './components/common/ToastContainer';

function AppLayout() {
  const [showNav, setShowNav] = useState(false);
  const sidebarRef = useRef(null);
  const location = useLocation();
  const hideNavigation = location.pathname === '/signin';

  useEffect(() => {
    const handleClickOutside = (e) => {
      if (
        sidebarRef.current &&
        !sidebarRef.current.contains(e.target) &&
        !e.target.closest('.hamburger-toggle')
      ) {
        setShowNav(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  return (
    <>
      {!hideNavigation && (
        <header>
          <GiHamburgerMenu className="hamburger-toggle" onClick={() => setShowNav(!showNav)} />
          <Header />
        </header>
      )}

      {!hideNavigation && (
        <div ref={sidebarRef}>
          <Navbar show={showNav} closeSidebar={() => setShowNav(false)} />
        </div>
      )}

      <div className={`main ${!hideNavigation && showNav ? 'shifted' : ''}`}>
        <Routes>
          <Route path="/" element={<Navigate to="/dashboard" replace />} />
          <Route path="/signin" element={<Signin />} />
          <Route
            path="/dashboard"
            element={
              <ProtectedRoute>
                <Home />
              </ProtectedRoute>
            }
          />
          <Route
            path="/notification"
            element={
              <ProtectedRoute>
                <Notification />
              </ProtectedRoute>
            }
          />
          <Route
            path="/registerdevice"
            element={
              <ProtectedRoute requiredRole="admin">
                <RegisterDevicePage />
              </ProtectedRoute>
            }
          />
          <Route
            path="/report"
            element={
              <ProtectedRoute>
                <ReportIssuePage />
              </ProtectedRoute>
            }
          />
          <Route
            path="/devices"
            element={
              <ProtectedRoute>
                <Devices />
              </ProtectedRoute>
            }
          />
          <Route
            path="/devices/view/:id"
            element={
              <ProtectedRoute>
                <ViewDevice />
              </ProtectedRoute>
            }
          />
          <Route
            path="/devices/:type"
            element={
              <ProtectedRoute>
                <DeviceTypeList />
              </ProtectedRoute>
            }
          />
          <Route
            path="/device/:id/edit"
            element={
              <ProtectedRoute requiredRole="admin">
                <EditDevice />
              </ProtectedRoute>
            }
          />
          <Route
            path="/devices/history/:id"
            element={
              <ProtectedRoute>
                <DeviceHistory />
              </ProtectedRoute>
            }
          />
          <Route
            path="/deviceslifecycle"
            element={
              <ProtectedRoute>
                <DeviceLifecycle />
              </ProtectedRoute>
            }
          />
          <Route
            path="/userprofile"
            element={
              <ProtectedRoute>
                <Userprofile />
              </ProtectedRoute>
            }
          />
          <Route
            path="/help"
            element={
              <ProtectedRoute>
                <Help />
              </ProtectedRoute>
            }
          />
          <Route
            path="/devicelifecycleform"
            element={
              <ProtectedRoute requiredRole="admin">
                <DeviceLifecycleForm />
              </ProtectedRoute>
            }
          />
          <Route
            path="/devices/lifecycle/:deviceId"
            element={
              <ProtectedRoute requiredRole="admin">
                <DeviceLifecycleForm />
              </ProtectedRoute>
            }
          />
          <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
      </div>
      <ToastContainer />
    </>
  );
}

function App() {
  return (
    <UserProvider>
      <DevicesProvider>
        <NotificationsProvider>
          <Router>
            <AppLayout />
          </Router>
        </NotificationsProvider>
      </DevicesProvider>
    </UserProvider>
  );
}

export default App;
