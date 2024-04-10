import React from 'react';
import axios from "axios";
import { useNavigate } from "react-router-dom";

export function DeleteOwner({ id }) {
    const navigate = useNavigate();

    const handleDeleteOwner = async () => {
        try {
            await axios.delete(`http://172.17.0.2:8888/delete_owner/${id}`);
            navigate("/admin");
        } catch (error) {
            console.error('Error al eliminar propietario:', error);
        }
    };

    return (
        <div className="actionButton" onClick={handleDeleteOwner}>Eliminar</div>
    );
}
