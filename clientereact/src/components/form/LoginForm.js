import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Loginbutton } from "../buttons/LoginButton";
import '../../styles/forms/loginForm.css';
import axios from 'axios';

export function LoginForm({ onLogin }) {
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const navigate = useNavigate();

    const handleSubmit = async (event) => {
        event.preventDefault();
        try {
            const response = await axios.post('http://172.17.0.2:8888/get_sesion', {
                username: username,
                password: password,
            });

            if (response.data.status) {
                onLogin();
                if (response.data.roles == 1){
                    navigate("/admin");
                }else {
                    const responseowner = await axios.get('http://172.17.0.2:8888/get_owner/' + response.data.restaurant);
                    let restaurantName = responseowner.data[0].name;
                    navigate("/" + restaurantName + '/admin');
                }
            } else {
                document.getElementById('loginError').innerHTML = "Usuario o Contraseña incorrecto";
                document.getElementById('loginError').style.display = "block";
            }
        } catch (error) {
            console.error(error);
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
            <div id="loginError"></div>
            <Loginbutton onClick={handleSubmit} value={"Acceder"} />
        </div>
    );
}
