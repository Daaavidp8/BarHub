import { useEffect, useState } from 'react';
import { BrowserRouter as Router, Route, Routes, Navigate } from 'react-router-dom';
import { Login } from './components/main/Login';
import { SecondScreen } from './components/main/SecondScreen';
import { Admin } from "./components/main/Admin/Admin";
import { ActionOwner } from "./components/main/Admin/ActionOwner";
import axios from "axios";
import { library } from '@fortawesome/fontawesome-svg-core';
import { fas } from '@fortawesome/free-solid-svg-icons';

library.add(fas);

function App() {
    const [loggedIn, setLoggedIn] = useState(() => {
        // Al cargar la aplicación, comprobamos si hay un estado de inicio de sesión almacenado en localStorage
        const storedLoggedIn = localStorage.getItem('loggedIn');
        return storedLoggedIn === 'true';
    });
    const [owners, setOwners] = useState([]);

    useEffect(() => {
        if (loggedIn) {
            const fetchOwners = async () => {
                try {
                    const response = await axios.get('http://172.17.0.2:8888/get_owners');
                    setOwners(response.data);
                } catch (error) {
                    console.error('Error al obtener propietarios:', error);
                }
            };
            fetchOwners();
        }
    }, [loggedIn]);

    const handleLogin = () => {
        localStorage.setItem('loggedIn', 'true');
        setLoggedIn(true);
    };

    const handleLogout = () => {
        localStorage.setItem('loggedIn', 'false');
        setLoggedIn(false);
    };

    return (
        <Router>
            <Routes>
                <Route path="/login" element={<Login onLogin={handleLogin} />} />
                <Route path="/" element={<Navigate to="/login" />} />
                <Route path="/admin" element={loggedIn ? <Admin logout={handleLogout}/> : <Navigate to="/login" />} />
                <Route path="/admin/add_owner" element={loggedIn ? <ActionOwner action={true}/> : <Navigate to="/login" />} />
                <Route path="/pedido/:id" element={loggedIn ? <SecondScreen /> : <Navigate to="/login" />} />
                {owners.map((owner) => (
                    <Route
                        key={owner.id_restaurant}
                        path={`/admin/modify_owner/${owner.id_restaurant}`}
                        element={loggedIn ? <ActionOwner action={false} data={owner}/> : <Navigate to="/login" />}
                    />
                ))}

                {owners.map((owner) => (
                    <Route
                        key={owner.id_restaurant}
                        path={`/${owner.name}/admin`}
                        element={loggedIn ? <Admin owners={owners} /> : <Navigate to="/login" />}
                    />
                ))}
            </Routes>
        </Router>
    );
}

export default App;
