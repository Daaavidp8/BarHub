import { Mainbutton } from "../buttons/Mainbutton";
import { useState } from "react";
import '../../styles/forms/form.css';
import {useNavigate} from "react-router-dom";

export function CodeForm() {
    const [codigo, setCodigo] = useState('');
    const navigate = useNavigate();
    const ComprobarCodigo = () => {
        if (codigo.length >= 3 && !isNaN(parseFloat(codigo))){
            navigate('/pedido/' + codigo)
        }else {
            setCodigo('')
            document.getElementById('codigoError').innerHTML = "Código Introducido Invalido"
            document.getElementById('codigoError').style.display = "flex"
        }
    };

    return (
        <div className="contenedorForm">
            <h1>Bienvenido a BarHub</h1>
            <img alt="Logo" />
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
        </div>
    );
}
