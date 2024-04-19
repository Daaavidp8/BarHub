import React from 'react';
import { useEffect, useState } from 'react';
import { BrowserRouter as Router, Route, Routes, Navigate } from 'react-router-dom';
import { Login } from './components/main/Login';
import { SecondScreen } from './components/main/SecondScreen';
import { Admin } from "./components/main/Admin/Admin";
import { ActionOwner } from "./components/main/Admin/ActionOwner";
import axios from "axios";
import { library } from '@fortawesome/fontawesome-svg-core';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { SelectOption } from "./components/main/SelectOption";
import { SectionAdminOwner } from "./components/main/Owner/SectionAdminOwner";

library.add(fas);

function App() {
    const [loggedIn, setLoggedIn] = useState(() => {
        const storedLoggedIn = localStorage.getItem('loggedIn');
        return storedLoggedIn === 'true';
    });
    const [userRole, setUserRole] = useState([]);
    const [userRestaurant, setuserRestaurant] = useState([]);
    const [owners, setOwners] = useState([]);
    const [sections, setSections] = useState([]);
    const [dataLoaded, setDataLoaded] = useState(false);

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

                    const userRestaurant = await axios.get('http://172.17.0.2:8888/get_owner/' + response.data.restaurant);

                    setuserRestaurant(userRestaurant.data[0]);
                    const ownersResponse = await axios.get('http://172.17.0.2:8888/get_owners');
                    setOwners(ownersResponse.data);

                    const sectionsResponse = await axios.get('http://172.17.0.2:8888/get_sections/' + response.data.restaurant);
                    setSections(sectionsResponse.data);

                    setDataLoaded(true)

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

    return (<>
        <Router>
            <Routes>
                <Route path="/login" element={<Login onLogin={handleLogin} />} />
                {!loggedIn || localStorage.getItem('username') === '' ? (
                    <Route path="/" element={<Navigate to="/login" />} />
                ) : null}
            </Routes>
        </Router>
        {dataLoaded && (
            <Router>
                <Routes>
                    <Route path="/login" element={<Login onLogin={handleLogin} />} />
                    {!loggedIn || localStorage.getItem('username') === '' ? (
                        <Route path="/" element={<Navigate to="/login" />} />
                    ) : null}

                    {loggedIn && userRole.includes(1) ? (
                        <>
                            <Route path="/admin" element={<Admin logout={handleLogout} owners={owners}/>} />
                            <Route path="/" element={<Navigate to="/admin" />} />
                        </>
                    ) : loggedIn && !userRole.includes(1) ? (
                        <>
                            <React.Fragment key={userRestaurant.id_restaurant}>
                                <>
                                    <Route
                                        path={`/${userRestaurant.name}/admin`}
                                        element={<SelectOption logout={handleLogout} name={userRestaurant.name} restaurant={userRestaurant.id_restaurant} />}
                                    />
                                    {userRole.includes(2) && (
                                        <>
                                            <Route
                                                path={`/${userRestaurant.name}/admin/sections`}
                                                element={<SectionAdminOwner elements={sections} restaurant={userRestaurant.name} title={'Secciones'} table={"sections"}/>}
                                            />
                                            <Route
                                                path={`/${userRestaurant.name}/admin/add_section`}
                                                element={null}
                                            />
                                            {sections.map(section => (
                                                <Route
                                                    path={`/${userRestaurant.name}/admin/${section.name}`}
                                                    element={null}
                                                />
                                            ))}
                                            <Route
                                                path={`/${userRestaurant.name}/admin/workers`}
                                                element={<SelectOption logout={handleLogout} name={userRestaurant.name} restaurant={userRestaurant.id_restaurant} />}
                                            />
                                        </>
                                    )}
                                    {userRole.includes(3) && (
                                        <Route
                                            path={`/${userRestaurant.name}/admin/tables`}
                                            element={<SelectOption logout={handleLogout} name={userRestaurant.name} restaurant={userRestaurant.id_restaurant} />}
                                        />
                                    )}
                                    <Route path="/" element={<Navigate to={`/${userRestaurant.name}/admin`} />} />
                                </>
                            </React.Fragment>
                        </>
                    ) : null}

                    <Route path="/admin/add_owner" element={loggedIn ? <ActionOwner action={true} /> : <Navigate to="/login" />} />
                    <Route path="/pedido/:id" element={loggedIn ? <SecondScreen /> : <Navigate to="/login" />} />

                    {owners.map((owner) => (
                        <Route
                            key={owner.id_restaurant}
                            path={`/admin/modify_owner/${owner.id_restaurant}`}
                            element={loggedIn ? <ActionOwner action={false} data={owner} /> : <Navigate to="/login" />}
                        />
                    ))}
                </Routes>
            </Router>
        )}</>
    );
}

export default App;
