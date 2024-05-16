import "../../../styles/forms/formWorkers.css";
import {BackButton} from "../../buttons/BackButton";
import { ReactComponent as Arrow } from '../../../images/arrow-sm-left-svgrepo-com.svg';
import "../../../styles/forms/formElement.css"
import {useEffect, useState} from "react";
import axios from "axios";
import {useNavigate} from "react-router-dom";

export function FormWorkers(props) {
    const [name, setName] = useState('');
    const [roles, setRoles] = useState('');
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [dataloaded, setdataloaded] = useState(false);
    const navigate = useNavigate();

    useEffect(() => {
        setdataloaded(false)
        if (!props.action){
            setName(props.data.name)

        }
        setdataloaded(true)
    }, [props.action]);

    const handleChange = (e) => {
        const { name, value, checked } = e.target;
        switch (name) {
            case 'name':
                setName(value);
                break;
            case 'username':
                setUsername(value);
                break;
            case 'password':
                setPassword(value);
                break;
            case 'admin':
            case 'waiter':
                let roleslocal = roles.split(',').filter(Boolean);
                const roleValue = name === 'admin' ? '2' : '3';
                if (checked) {
                    if (!roleslocal.includes(roleValue)) {
                        roleslocal.push(roleValue);
                    }
                } else {
                    roleslocal = roleslocal.filter(r => r !== roleValue);
                }
                setRoles(roleslocal.join(','));
                break;
            default:
                break;
        }
    };



    const modifyElement = async () => {
        const response = await axios.post('http://172.17.0.2:8888/get_sesion', {
            username: localStorage.getItem('username'),
            password: localStorage.getItem('password'),
        });

        if (response.data.roles.includes(2)){
            if (validarCampos()){
                try {
                    await axios.put('http://172.17.0.2:8888/create_worker/' + props.restaurant.id_restaurant, {
                        worker_name: name,
                        worker_username: username,
                        worker_password: password,
                        worker_roles: roles
                    });
                    navigate("/" + props.restaurant.name + "/admin")
                    navigate("/" + props.restaurant.name + "/admin/workers")
                    window.location.reload();
                } catch (error) {
                    console.error('Error al añadir propietario:', error);
                }
            }
        }
    }

    const addElement = async () => {
        const response = await axios.post('http://172.17.0.2:8888/get_sesion', {
            username: localStorage.getItem('username'),
            password: localStorage.getItem('password'),
        });

        if (response.data.roles.includes(2)){
            if (validarCampos()){
                console.log("Nombre-> " + name)
                console.log("Usuario-> " + username)
                console.log("Contraseña-> " + password)
                console.log("Roles-> " + roles)
                try {
                    await axios.post('http://172.17.0.2:8888/create_worker/' + props.restaurant.id_restaurant, {
                        worker_name: name,
                        worker_username: username,
                        worker_password: password,
                        worker_roles: roles
                    });
                    navigate("/" + props.restaurant.name + "/admin")
                    navigate("/" + props.restaurant.name + "/admin/workers")
                    window.location.reload();
                } catch (error) {
                    console.error('Error al añadir propietario:', error);
                }
            }
        }

    };

    const validarCampos = () => {
        let errorMessage = "";

        if (errorMessage !== "") {
            document.getElementsByClassName("errorCard")[0].style.display = "block";
            document.getElementsByClassName("errorCard")[0].innerHTML = errorMessage;
            return false;
        }

        return true;
    }




    return (
        <>
            {dataloaded && (<>
                <h1 className="titleForm">
                    <BackButton value={<Arrow className="flechaVolver"/>}></BackButton>
                    {props.title}
                </h1>
                <form className="formWorkers">
                    <div>
                        <div>
                            <label htmlFor="name" className="labelFormElements">Nombre
                                del Trabajador:</label><br/>
                            <input
                                className="inputElement"
                                type="text"
                                id="name"
                                name="name"
                                placeholder="Introduce el nombre completo..."
                                value={name}
                                onChange={handleChange}
                                required
                            />
                        </div>
                        <div>
                            <label htmlFor="username" className="labelFormElements">Nombre de usuario:</label><br/>
                            <input
                                className="inputElement"
                                type="text"
                                id="username"
                                name="username"
                                placeholder="Introduce el nombre de usuario..."
                                value={username}
                                onChange={handleChange}
                                required
                            />
                        </div>
                        <div>
                            <label htmlFor="password" className="labelFormElements">Contraseña:</label><br/>
                            <input
                                className="inputElement"
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Introduce el nombre..."
                                value={password}
                                onChange={handleChange}
                                required
                            />
                        </div>
                        <div className="contenedorRoles">
                            <label htmlFor="roles" className="labelFormElements">Roles:</label><br/>
                            <div>
                                <div>
                                    <input
                                        type="checkbox"
                                        id="admin"
                                        name="admin"
                                        value="2"
                                        onChange={handleChange}
                                    />
                                    <label htmlFor="admin">Administrador</label>
                                </div>
                                <div>
                                    <input
                                        type="checkbox"
                                        id="waiter"
                                        name="waiter"
                                        value="3"
                                        onChange={handleChange}
                                    />
                                    <label htmlFor="waiter">Camarero</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div className="errorCard errorFormElement"></div>
                <div className="containerFormElementActionButton">
                    {props.action ? (
                        <button type="button"
                                className="openOwnerButton actionOwnerbutton buttonFormElement"
                                onClick={addElement}
                        >
                            Añadir
                        </button>
                    ) : (
                        <button type="button" className="actionOwnerbutton saveChangesButton buttonFormElement"
                                onClick={modifyElement}
                        >Guardar
                            Cambios</button>
                    )}
                </div>

            </>)}

        </>
    );
}