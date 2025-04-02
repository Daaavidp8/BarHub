import { Mainbutton } from "../buttons/Mainbutton";
import { useState } from "react";
import '../../styles/forms/form.css';
import { useNavigate } from "react-router-dom";
import axios from "axios";
import { ENDPOINTS, API_CONFIG } from '../../utils/constants'; // Importing ENDPOINTS and API_CONFIG from constants

// Comprobador del código de la mesa
export function CodeForm(props) {
    const [codigo, setCodigo] = useState('');
    const navigate = useNavigate();

    const revisarExistenciaCodigo = async () => {
        console.log("Hace esto")
        try {
            // Get the token from localStorage if available
            const token = localStorage.getItem('token');
            
            // Use fetch with proper authorization headers
            const response = await fetch(`${API_CONFIG.BASE_URL}${ENDPOINTS.GET_TABLES}/${props.restaurant.id_restaurant}`, {
                method: 'GET',
                headers: {
                    // Only include essential headers
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                },
                // Include credentials for CORS
                credentials: 'include'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log("Lo hace bien")
            
            let codigos = [];
            data.map(elements => {
                codigos.push(elements.codenumber)
            })
            console.log("Pilla los codigos chill")
            return codigos.includes(parseInt(codigo))
        } catch (error) {
            console.error("Error fetching tables:", error);
            document.getElementById('codigoError').innerHTML = "Error al verificar el código"
            document.getElementById('codigoError').style.display = "flex"
            return false;
        }
    }

    const ComprobarCodigo = async () => {
        if (codigo.length >= 3 && !isNaN(parseFloat(codigo)) && (await revisarExistenciaCodigo())){
            navigate(`/${props.restaurant.name}/pedido/${codigo}`)
        } else {
            setCodigo('')
            document.getElementById('codigoError').innerHTML = "Código Introducido Invalido"
            document.getElementById('codigoError').style.display = "flex"
        }
    };

    return (
        <>
            <form className="contenedorForm">
                <label htmlFor="code" className="labelcodeform">Introducir Código Proporcionado por el camarero:</label>
                <input
                    value={codigo}
                    type="text"
                    name="code"
                    id="code"
                    className="input"
                    placeholder="Introduce el código"
                    onChange={(event) => setCodigo(event.target.value)}
                />
                <div className="error" id="codigoError"></div>
                <Mainbutton onClick={ComprobarCodigo} />
            </form>
        </>
    );
}
