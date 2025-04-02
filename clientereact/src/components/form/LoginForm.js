import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Loginbutton } from "../buttons/LoginButton";
import '../../styles/forms/loginForm.css';
import axiosInstance from '../../utils/axiosConfig';
import { ENDPOINTS, STORAGE_KEYS, ROLES } from '../../utils/constants';

export function LoginForm({ onLogin }) {
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [errorMessage, setErrorMessage] = useState('');
    const navigate = useNavigate();

    const handleSubmit = async (event) => {
        event.preventDefault();
        try {
            const response = await axiosInstance.post(ENDPOINTS.LOGIN, {
                username: username,
                password: password,
            }, {
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            if (response.data.status) {
                localStorage.setItem(STORAGE_KEYS.TOKEN, response.data.token);
                onLogin();
                localStorage.setItem(STORAGE_KEYS.USER_ID, response.data.id);
                localStorage.setItem(STORAGE_KEYS.USERNAME, username);
                localStorage.setItem(STORAGE_KEYS.PASSWORD, password);

                if (response.data.roles.includes(ROLES.ADMIN)) {
                    navigate("/admin");
                } else {
                    const responseowner = await axiosInstance.get(`${ENDPOINTS.GET_OWNER}/${response.data.restaurant}`);
                    let restaurantName = responseowner.data[0].name;
                    navigate(`/${restaurantName}/admin`);
                }
            } else {
                setErrorMessage("Usuario o Contraseña incorrecto");
            }
        } catch (error) {
            console.error(error);
            setErrorMessage("Ocurrió un error. Intente nuevamente.");
        }
    };

    return (
        <div className="loginForm">
            <div>
                <label htmlFor="username" className="labelloginform">Usuario: </label>
                <input
                    value={username}
                    type="text"
                    name="username"
                    id="username"
                    placeholder="Introduce el nombre de usuario..."
                    onChange={(event) => setUsername(event.target.value)}
                />
            </div>
            <div>
                <label htmlFor="password" className="labelloginform">Contraseña: </label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Introduce la contraseña..."
                    onChange={(event) => setPassword(event.target.value)}
                />
            </div>
            {errorMessage && (
                <div id="loginError">{errorMessage}</div>
            )}
            <Loginbutton onClick={handleSubmit} value={"Acceder"} />
        </div>
    );
}

