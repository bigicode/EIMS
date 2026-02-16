import { useParams } from 'react-router-dom';

function EditDevice() {
  const { id } = useParams();

  return (
    <div className="container mt-4">
      <h3>Edit Device</h3>
      <p>Editing device with ID: {id}</p>
      {/* Add your form here */}
    </div>
  );
}

export default EditDevice;
