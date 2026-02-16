import { Navigate, useLocation } from 'react-router-dom';
import { USER_ROLES } from '../../constants/deviceOptions';
import { useUser } from '../../hooks/useUser';

function ProtectedRoute({ children, requiredRole }) {
  const { user, loading } = useUser();
  const location = useLocation();

  if (loading) {
    return (
      <div className="container mt-4">
        <div className="alert alert-info">Checking user session...</div>
      </div>
    );
  }

  if (!user) {
    return <Navigate to="/signin" replace state={{ from: location.pathname }} />;
  }

  if (requiredRole && user.role !== requiredRole) {
    const fallback = user.role === USER_ROLES.ADMIN ? '/dashboard' : '/devices';
    return <Navigate to={fallback} replace />;
  }

  return children;
}

export default ProtectedRoute;
