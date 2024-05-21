import "../../../styles/forms/formWorkers.css";
import {BackButton} from "../../buttons/BackButton";
import { ReactComponent as Arrow } from '../../../images/arrow-sm-left-svgrepo-com.svg';
import "../../../styles/forms/formElement.css"
import {useEffect, useState} from "react";
import axios from "axios";
import {useNavigate} from "react-router-dom";


// Componente para modificar y crear nuevos trabajadores

export function FormWorkers(props) {
    const [name, setName] = useState('');
    const [roles, setRoles] = useState('');
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [dataloaded, setdataloaded] = useState(false);
    const navigate = useNavigate();

    useEffect(() => {
        setdataloaded(false)
        const workerresponse = async () => {
            let roles = [];
            const responseworker = await axios.get('http://172.17.0.2:8888/get_worker/' + props.worker)
            setName(responseworker.data.userData[0].name)
            setUsername(responseworker.data.userData[0].username)
            roles.push(JSON.stringify(responseworker.data.roles).includes(2) ? "2" : null)
            roles.push(JSON.stringify(responseworker.data.roles).includes(3) ? "3" : null)
            setRoles(roles.join(","))
        }
        if (!props.action){
            workerresponse();
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

        if (response.data.roles.includes(2) && response.data.status){
            if (validarCampos()){
                try {
                    await axios.put('http://172.17.0.2:8888/update_worker/' + props.worker, {
                        worker_name: name,
                        worker_username: username,
                        worker_password: password,
                        worker_roles: roles
                    });
                    if (props.worker == localStorage.getItem('id')){
                        localStorage.setItem('username', username)
                        localStorage.setItem('password', password)
                    }
                    navigate("/" + props.restaurant.name + "/admin")
                    navigate("/" + props.restaurant.name + "/admin/workers")
                    window.location.reload();
                } catch (error) {
                    console.error('Error al modificar propietario:', error);
                }
            }
        }else{
            navigate("/")
        }
    }

    const addElement = async () => {
        const response = await axios.post('http://172.17.0.2:8888/get_sesion', {
            username: localStorage.getItem('username'),
            password: localStorage.getItem('password'),
        });

        if (response.data.roles.includes(2) && response.data.status){
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
        }else{
            navigate("/")
        }

    };

    const validatePassword = (password) => {
        let errorMessage = "";
        if (password.length < 8) {
            errorMessage += "-La contraseña debe contener al menos 8 caracteres<br>";
        } else {
            let upperCaseLetters = /[A-Z]/g;
            let numbers = /[0-9]/g;
            let lowerCaseLetters = /[a-z]/g;

            if (!password.match(upperCaseLetters)) {
                errorMessage += "-La contraseña debe contener al menos una mayúscula<br>";
            }
            if (!password.match(lowerCaseLetters)) {
                errorMessage += "-La contraseña debe contener al menos una minúscula<br>";
            }
            if (!password.match(numbers)) {
                errorMessage += "-La contraseña debe contener al menos un número<br>";
            }
        }
        return errorMessage;
    };

    const validarCampos = () => {
        let errorMessage = "";

        if (name.length <= 0){
            errorMessage += "-El nombre está vacio<br>"
        }

        if (username.length <= 0){
            errorMessage += "-El nombre de usuario está vacio<br>"
        }

        if (roles.length <= 0){
            errorMessage += "-El usuario no puede no tener roles<br>"
        }

        if (props.action) {
            errorMessage += validatePassword(password);
        } else {
            if (password.length !== 0) {
                errorMessage += validatePassword(password);
            }
        }


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
                                        checked={roles.includes(2)}
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
                                        checked={roles.includes(3)}
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