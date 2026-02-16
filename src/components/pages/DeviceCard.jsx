import React from 'react';
import { useNavigate } from 'react-router-dom';

function DeviceCard({ type, count, icon, bgColor }) {
  const navigate = useNavigate();

  const handleClick = () => {
    navigate(`/devices/${type.toLowerCase()}`); // e.g., /devices/printer
  };

  return (
    <div className="col-md-3 mb-3">
      <div className={`card text-white ${bgColor} cursor-pointer`} onClick={handleClick} style={{ cursor: 'pointer' }}>
        <div className="card-body d-flex justify-content-between align-items-center">
          <div>
            <h6 className="card-title">{type}</h6>
            <h4>{count}</h4>
          </div>
          <i className={`fs-1 ${icon}`}></i>
        </div>
      </div>
    </div>
  );
}

export default DeviceCard;
