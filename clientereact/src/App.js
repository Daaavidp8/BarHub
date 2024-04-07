import { useEffect, useState } from 'react';
import {BrowserRouter as Router, Route, Routes, Navigate} from 'react-router-dom';
import { Login } from './components/main/Login';
import { SecondScreen } from './components/main/SecondScreen';
import { Admin } from "./components/main/Admin/Admin";
import { AddOwner } from "./components/main/Admin/AddOwner";
import axios from "axios";

function App() {
    const [loggedIn, setLoggedIn] = useState(false);
    const [owners, setOwners] = useState([]);

    useEffect(() => {
        const fetchOwners = async () => {
            if (loggedIn) {
                try {
                    const response = await axios.get('http://172.17.0.2:8888/get_owners');
                    setOwners(response.data);
                } catch (error) {
                    console.error('Error al obtener propietarios:', error);
                }
            }
        };
        fetchOwners();
    }, [loggedIn]);

    const handleLogin = () => {
        setLoggedIn(true);
    };

    return (
        <Router>
            <Routes>
                <Route path="/login" element={<Login onLogin={handleLogin} />} />
                <Route path="/" element={<Navigate to="/login" />} />
                <Route path="/admin" element={loggedIn ? <Admin/> : <Navigate to="/login" />} />
                <Route path="/admin/add_owner" element={loggedIn ? <AddOwner /> : <Navigate to="/login" />} />
                <Route path="/pedido/:id" element={loggedIn ? <SecondScreen /> : <Navigate to="/login" />} />
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
