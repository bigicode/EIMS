import logo from './TMD logo.png';
import { Link } from 'react-router-dom';
import { MdOutlineLogin } from "react-icons/md";
import { IoIosNotifications } from "react-icons/io";
import { BiSolidDevices } from "react-icons/bi";
import { FaUser, FaBoxArchive } from "react-icons/fa6";
import { AiFillDashboard } from "react-icons/ai";
import { TbDeviceIpadDown } from "react-icons/tb";
import { IoHelp } from "react-icons/io5";
import { MdOutlineReport } from "react-icons/md";
import { BsClockHistory } from "react-icons/bs";

const Navbar = ({ show, closeSidebar }) => {
  return (
    <div className={show ? "sidenav active" : "sidenav"}>
      <img src={logo} alt="logo" className="logo"/>
      <ul>
        <li><Link to="/dashboard" onClick={closeSidebar}><AiFillDashboard /> Dashboard</Link></li>
        <li><Link to="/signin" onClick={closeSidebar}><MdOutlineLogin /> Login/Signin</Link></li>
        <li><Link to="/notification" onClick={closeSidebar}><IoIosNotifications /> Notification</Link></li>
        <li><Link to="/registerdevice" onClick={closeSidebar}><TbDeviceIpadDown /> Register Device</Link></li>
        <li><Link to="/report" onClick={closeSidebar}><MdOutlineReport />Report Device</Link></li>
        <li><Link to="/devices" onClick={closeSidebar}><BiSolidDevices /> Devices</Link></li>
        <li><Link to="/deviceslifecycle" onClick={closeSidebar}><BsClockHistory /> Devices Lifecycle</Link></li>
        <li><Link to="/userprofile" onClick={closeSidebar}><FaUser /> User Profile</Link></li>
        <li><Link to="/help" onClick={closeSidebar}><IoHelp /> Help</Link></li>
        
      </ul>
    </div>
  );
};

export default Navbar;
