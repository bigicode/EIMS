import { useNotifications } from '../../hooks/useNotifications';

function ToastContainer() {
  const { toasts, removeToast } = useNotifications();

  if (!toasts.length) {
    return null;
  }

  return (
    <div className="toast-container position-fixed top-0 end-0 p-3" style={{ zIndex: 1080 }}>
      {toasts.map((toast) => (
        <div key={toast.id} className={`toast show text-bg-${toast.variant || 'info'} border-0 mb-2`}>
          <div className="d-flex">
            <div className="toast-body">{toast.message}</div>
            <button
              type="button"
              className="btn-close btn-close-white me-2 m-auto"
              aria-label="Close"
              onClick={() => removeToast(toast.id)}
            />
          </div>
        </div>
      ))}
    </div>
  );
}

export default ToastContainer;
