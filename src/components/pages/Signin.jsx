import React, { useState } from 'react';
import { MdOutlineLogin } from 'react-icons/md';
import { useLocation, useNavigate } from 'react-router-dom';
import { USER_ROLES } from '../../constants/deviceOptions';
import { useNotifications } from '../../hooks/useNotifications';
import { useUser } from '../../hooks/useUser';

const Signin = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const { login, loading, error } = useUser();
  const { addToast } = useNotifications();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [userType, setUserType] = useState(USER_ROLES.USER);
  const [formError, setFormError] = useState('');

  const handleLoginSubmit = async (e) => {
    e.preventDefault();
    setFormError('');

    try {
      await login({
        email,
        password,
        userType,
      });

      addToast('Signed in successfully.', 'success');
      navigate(location.state?.from || '/dashboard', { replace: true });
    } catch (loginError) {
      setFormError(loginError.message || 'Unable to sign in');
    }
  };

  return (
    <div className='formdashboard'>
      <h2>Login</h2>
      <form onSubmit={handleLoginSubmit} className="formfield">
        {(formError || error) && <div className="alert alert-danger">{formError || error}</div>}
        <input
          type="email"
          placeholder="Email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          aria-label="Email"
          required
        />
        <input
          type="password"
          placeholder="Password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          aria-label="Password"
          required
        />
        <div style={{ margin: '0.5em 0' }} className='fogot'>
          <a href="#" onClick={(e) => e.preventDefault()}>
            Forgot your password?
          </a>
        </div>
        <div className='admn'>
          <label>
            <input
              type="radio"
              name="userType"
              value={USER_ROLES.ADMIN}
              checked={userType === USER_ROLES.ADMIN}
              onChange={() => setUserType(USER_ROLES.ADMIN)}
            />
            Admin (HOD)
          </label>
          <label style={{ marginLeft: '1em' }}>
            <input
              type="radio"
              name="userType"
              value={USER_ROLES.USER}
              checked={userType === USER_ROLES.USER}
              onChange={() => setUserType(USER_ROLES.USER)}
            />
            Normal User
          </label>
        </div>
        <button type="submit" style={{ marginTop: '1em' }} className='button' disabled={loading}>
          <MdOutlineLogin />
          {loading ? ' Signing In...' : ' Log In'}
        </button>
      </form>
      <hr className='hori' />
    </div>
  );
};

export default Signin;
