import { Mainbutton } from "../buttons/Mainbutton";
import { useState } from "react";
import '../../styles/forms/form.css';
import {useNavigate} from "react-router-dom";
import axios from "axios";

export function CodeForm(props) {
    const [codigo, setCodigo] = useState('');
    const navigate = useNavigate();

    const revisarExistenciaCodigo = async () => {
        const tablesResponse = await axios.get('http://172.17.0.2:8888/get_tables/' + props.restaurant.id_restaurant);
        let codigos = [];
        tablesResponse.data.map(elements => {
            codigos.push(elements.codenumber)
        })
        console.log(codigos.includes(codigo))
        return codigos.includes(parseInt(codigo))
    }

    const ComprobarCodigo = async () => {
        if (codigo.length >= 3 && !isNaN(parseFloat(codigo)) && (await revisarExistenciaCodigo())){
            navigate(`/${props.restaurant.name}/pedido/${codigo}`)
        }else {
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
