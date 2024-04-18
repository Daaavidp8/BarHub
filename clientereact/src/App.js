import React from 'react';
import { useEffect, useState } from 'react';
import {BrowserRouter as Router, Route, Routes, Navigate, useNavigate} from 'react-router-dom';
import { Login } from './components/main/Login';
import { SecondScreen } from './components/main/SecondScreen';
import { Admin } from "./components/main/Admin/Admin";
import { ActionOwner } from "./components/main/Admin/ActionOwner";
import axios from "axios";
import { library } from '@fortawesome/fontawesome-svg-core';
import { fas } from '@fortawesome/free-solid-svg-icons';
import {SelectOption} from "./components/main/SelectOption";
import {SectionAdminOwner} from "./components/main/Owner/SectionAdminOwner";

library.add(fas);

function App() {
    const [loggedIn, setLoggedIn] = useState(() => {
        const storedLoggedIn = localStorage.getItem('loggedIn');
        return storedLoggedIn === 'true';
    });
    const [userRole, setUserRole] = useState(null);
    const [userRestaurant, setuserRestaurant] = useState(null);
    const [owners, setOwners] = useState([]);

    useEffect(() => {
        if (loggedIn && localStorage.getItem('username') !== '') {
            const fetchData = async () => {
                try {
                    const response = await axios.post('http://172.17.0.2:8888/get_sesion', {
                        username: localStorage.getItem('username'),
                        password: localStorage.getItem('password'),
                    });
                    let roles = [];

                    response.data.roles.forEach((role) => {
                        roles.push(role)
                    })

                    setUserRole(roles);

                    setuserRestaurant( response.data.restaurant);
                    const ownersResponse = await axios.get('http://172.17.0.2:8888/get_owners');
                    setOwners(ownersResponse.data);

                } catch (error) {
                    console.error('Error al obtener datos:', error);
                }
            };
            fetchData();
        }
    }, [loggedIn]);

    const handleLogin = () => {
        localStorage.setItem('loggedIn', 'true');
        setLoggedIn(true);
    };

    const handleLogout = () => {
        localStorage.setItem('loggedIn', 'false');
        localStorage.setItem('password', '');
        localStorage.setItem('username', '');
        setLoggedIn(false);
    };

    return (
        <Router>
            <Routes>
                <Route path="/login" element={<Login onLogin={handleLogin} />} />

                { !loggedIn || localStorage.getItem('username') === '' ? (
                <Route path="/" element={<Navigate to="/login" />} />
                ) : null}

                {loggedIn && userRole == "1" ? (
                    <>
                        <Route path="/admin" element={<Admin logout={handleLogout} />} />
                        <Route path="/" element={<Navigate to="/admin" />} />
                    </>
                ) : loggedIn && userRole != "1" ? (
                    <>
                        {owners.map((owner) => (
                            <React.Fragment key={owner.id_restaurant}>
                                {owner.id_restaurant === userRestaurant && (
                                    <>
                                        <Route
                                            path={`/${owner.name}/admin`}
                                            element={<SelectOption logout={handleLogout} name={owner.name} restaurant={owner.id_restaurant}/>}
                                        />
                                        {userRole.includes(2) && (
                                            <><Route
                                                path={`/${owner.name}/admin/sections`}
                                                element={<SectionAdminOwner/>}
                                            />
                                                <Route
                                                    path={`/${owner.name}/admin/workers`}
                                                    element={<SelectOption logout={handleLogout} name={owner.name} restaurant={owner.id_restaurant}/>}
                                                />


                                            </>

                                        )}
                                        {userRole.includes(3) && (
                                            <Route
                                                path={`/${owner.name}/admin/tables`}
                                                element={<SelectOption logout={handleLogout} name={owner.name} restaurant={owner.id_restaurant}/>}
                                            />
                                        )}
                                        <Route path="/" element={<Navigate to={`/${owner.name}/admin`} />} />
                                    </>
                                )}

                            </React.Fragment>
                        ))}

                    </>
                ) : null}


                {console.log(localStorage.getItem('username') === '')}

                <Route path="/admin/add_owner" element={loggedIn ? <ActionOwner action={true}/> : <Navigate to="/login" />} />
                <Route path="/pedido/:id" element={loggedIn ? <SecondScreen /> : <Navigate to="/login" />} />

                {owners.map((owner) => (
                    <Route
                        key={owner.id_restaurant}
                        path={`/admin/modify_owner/${owner.id_restaurant}`}
                        element={loggedIn ? <ActionOwner action={false} data={owner}/> : <Navigate to="/login" />}
                    />
                ))}

            </Routes>
        </Router>
    );
}

export default App;
