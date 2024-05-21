import React from 'react';
import { useEffect, useState } from 'react';
import { BrowserRouter as Router, Route, Routes, Navigate } from 'react-router-dom';
import { Login } from './components/main/Login';
import { SecondScreen } from './components/main/Dinner/SecondScreen';
import { Admin } from "./components/main/Admin/Admin";
import { ActionOwner } from "./components/main/Admin/ActionOwner";
import axios, { all } from "axios";
import { library } from '@fortawesome/fontawesome-svg-core';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { SelectOption } from "./components/main/SelectOption";
import { SectionAdminOwner } from "./components/main/Owner/SectionAdminOwner";
import { FormElement } from "./components/form/Owner/FormElement";
import { FormWorkers } from "./components/form/Owner/FormWorkers";
import { ShowTable } from "./components/main/Waiter/ShowTable";
import { FirstScreen } from "./components/main/Dinner/FirstScreen";
import { ArticlesSection } from "./components/main/Dinner/ArticlesSection";

library.add(fas);

// Fichero de creación de rutas

function App() {
    const [loggedIn, setLoggedIn] = useState(() => {
        const storedLoggedIn = localStorage.getItem('loggedIn');
        return storedLoggedIn === 'true';
    });
    const [userRole, setUserRole] = useState([]);
    const [workers, setWorkers] = useState([]);
    const [userRestaurant, setuserRestaurant] = useState([]);
    const [owners, setOwners] = useState([]);
    const [allsections, setAllSections] = useState([]);
    const [sections, setSections] = useState([]);
    const [articles, setArticles] = useState([]);
    const [codes, setCodes] = useState([]);
    const [tables, setTables] = useState([]);
    const [dataLoaded, setDataLoaded] = useState(false);

    // Carga los datos necesarios para crear las rutas
    useEffect(() => {
        const fetchData = async () => {
            try {
                if (loggedIn && localStorage.getItem('username') !== '') {
                    const response = await axios.post('http://172.17.0.2:8888/get_sesion', {
                        username: localStorage.getItem('username'),
                        password: localStorage.getItem('password'),
                    });

                    if (response.data.status){
                        let roles = [];

                        response.data.roles.forEach((role) => {
                            roles.push(role)
                        })

                        setUserRole(roles);
                        const userRestaurantresponse = await axios.get('http://172.17.0.2:8888/get_owner/' + response.data.restaurant);
                        setuserRestaurant(userRestaurantresponse.data[0]);

                        const sectionsResponse = await axios.get('http://172.17.0.2:8888/get_sections/' + response.data.restaurant);
                        setSections(sectionsResponse.data);

                        const sectionsPromises = sectionsResponse.data.map(async (section) => {
                            const responseArticles = await axios.get('http://172.17.0.2:8888/get_articles/' + section.id_section);
                            return responseArticles.data;
                        });

                        const articlesData = await Promise.all(sectionsPromises);
                        const allArticles = articlesData.flat();
                        setArticles(allArticles);

                        const tableResponse = await axios.get('http://172.17.0.2:8888/get_tables/' + response.data.restaurant);
                        setTables(tableResponse.data);

                        const workersResponse = await axios.get('http://172.17.0.2:8888/get_workers/' + response.data.restaurant);
                        setWorkers(workersResponse.data);
                    }

                }

            } catch (error) {
                console.error('Error al obtener datos:', error);
            } finally {
                const ownersResponse = await axios.get('http://172.17.0.2:8888/get_owners');
                setOwners(ownersResponse.data);

                const codesPromises = ownersResponse.data.map(async (owner) => {
                    const codesResponse = await axios.get('http://172.17.0.2:8888/get_tables/' + owner.id_restaurant);
                    return codesResponse.data;
                });

                const allCodes = await Promise.all(codesPromises);
                setCodes(allCodes);

                const allSectionsPromises = ownersResponse.data.map(async (owner) => {
                    const sectionsResponse = await axios.get('http://172.17.0.2:8888/get_sections/' + owner.id_restaurant);
                    return sectionsResponse.data;
                });

                const allSectionsData = await Promise.all(allSectionsPromises);
                setAllSections(allSectionsData);


                setDataLoaded(true);
            }
        };

        fetchData();
    }, [loggedIn]);

    // Funciones para realizar el login
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
        <>
            {dataLoaded && (
                <Router>
                    <Routes>
                        <Route path="/login" element={<Login onLogin={handleLogin} />} />
                        {!loggedIn || localStorage.getItem('username') === '' ? (
                            <Route path="/" element={<Navigate to="/login" />} />
                        ) : null}

                        // Si el usuario tiene el rol de admin, le cargarán las rutas para administrar restaurantes
                        {loggedIn && userRole.includes(1) ? (
                            <>
                                <Route path="/admin" element={<Admin logout={handleLogout} owners={owners} />} />
                                <Route path="/" element={<Navigate to="/admin" />} />
                                {owners.map((owner, index) => (
                                    <Route
                                        key={`${owner.id_restaurant}_${index}_owners`}
                                        path={`/admin/modify_owner/${owner.id_restaurant}`}
                                        element={loggedIn ? <ActionOwner action={false} data={owner} /> : <Navigate to="/login" />}
                                    />
                                ))}
                                <Route path="/admin/add_owner" element={loggedIn ? <ActionOwner action={true} /> : <Navigate to="/login" />} />
                            </>
                        ) : loggedIn && !userRole.includes(1) ? (
                            <>
                                <React.Fragment>
                                    <>
                                        <Route
                                            path={`/${userRestaurant.name}/admin`}
                                            element={<SelectOption logout={handleLogout} name={userRestaurant.name} restaurant={userRestaurant.id_restaurant} />}
                                        />
                                        // Si el usuario tiene el rol de propietario le cargaran las rutas para administrar sus secciones y personal
                                        {userRole.includes(2) && (
                                            <>
                                                <Route
                                                    path={`/${userRestaurant.name}/admin/sections`}
                                                    element={<SectionAdminOwner restaurant={userRestaurant} title={'Secciones'} table={"sections"} />}
                                                />
                                                <Route
                                                    path={`/${userRestaurant.name}/admin/add_section`}
                                                    element={<FormElement title={"Añadir Sección"} action={true} element={"sections"} restaurant={userRestaurant} />}
                                                />
                                                {sections.map((section, index) => (
                                                    <>
                                                        <Route
                                                            key={`modify_section_${section.name}_${section.id_section}`}
                                                            path={`/${userRestaurant.name}/admin/modify_section/${section.name}`}
                                                            element={<FormElement title={"Modificar Sección"} action={false} element={"sections"} restaurant={userRestaurant} data={section} />}
                                                        />
                                                        <Route
                                                            key={`section_${section.id_section}`}
                                                            path={`/${userRestaurant.name}/admin/${section.name}`}
                                                            element={<SectionAdminOwner restaurant={userRestaurant} title={section.name} table={"articles"} data={section} />}
                                                        />
                                                        <Route
                                                            key={`add_article_${section.name}_${section.id_section}`}
                                                            path={`/${userRestaurant.name}/admin/add_${section.name}`}
                                                            element={<FormElement title={"Añadir " + (section.name.endsWith('s') ? section.name.substring(0, section.name.length - 1) : section.name)} action={true} element={"articles"} restaurant={userRestaurant} data={section} />}
                                                        />
                                                    </>
                                                ))}
                                                {articles.map((article) => (
                                                    <Route
                                                        key={`modify_article_${article.id_article}`}
                                                        path={`/${userRestaurant.name}/admin/modify_article/${article.name}`}
                                                        element={<FormElement title={"Modificar Articulo"} action={false} element={"articles"} restaurant={userRestaurant} data={article} />}
                                                    />
                                                ))}

                                                <Route
                                                    path={`/${userRestaurant.name}/admin/workers`}
                                                    element={<SectionAdminOwner restaurant={userRestaurant} title={'Trabajadores'} table={"workers"} />}
                                                />
                                                <Route
                                                    path={`/${userRestaurant.name}/admin/add_worker`}
                                                    element={<FormWorkers title={"Añadir Trabajador"} action={true} restaurant={userRestaurant} />}
                                                />

                                                {
                                                    workers.map((worker) => (
                                                        <Route
                                                            key={`${worker.id_user}_trabajador`}
                                                            path={`/${userRestaurant.name}/admin/modify_worker/${worker.id_user}`}
                                                            element={<FormWorkers title={"Añadir Trabajador"} action={false} restaurant={userRestaurant} worker={worker.id_user}/>}
                                                        />
                                                    ))
                                                }
                                            </>
                                        )}
                                        // Si el usuario tiene el rol de camarer le cargaran las rutas para administrar mesas
                                        {userRole.includes(3) && (
                                            <>
                                                <Route
                                                    path={`/${userRestaurant.name}/admin/tables`}
                                                    element={<SectionAdminOwner restaurant={userRestaurant} title={'Mesas'} table={"tables"} />}
                                                />
                                                {tables.map((table) => (
                                                    <Route
                                                        key={userRestaurant.id_restaurant + "_Mesa" + table.table_number}
                                                        path={`/${userRestaurant.name}/admin/table/${table.table_number}`}
                                                        element={<ShowTable table={table.table_number} restaurant={userRestaurant} />}
                                                    />
                                                ))}
                                            </>
                                        )}
                                        <Route path="/" element={<Navigate to={`/${userRestaurant.name}/admin`} />} />
                                    </>
                                </React.Fragment>
                            </>
                        ) : null}

                        // Rutas de los codigos de cada mesa de cada restaurante
                        {owners.map((owner, index) => (
                            <>
                                <Route
                                    key={`restaurante_${owner.id_restaurant}_${owner.name}`}
                                    path={`/${owner.name}`}
                                    element={<FirstScreen restaurant={owner} />}
                                />
                                {codes[index].map((codigo) => (
                                    <>
                                        <Route
                                            key={`${owner.name}_${codigo.table_number}_${codigo.codenumber}_Pedido`}
                                            path={`/${owner.name}/pedido/${codigo.codenumber}`}
                                            element={<SecondScreen table={codigo} />}
                                        />
                                        <Route
                                            key={`${owner.restaurant}_${codigo.table_number}_${codigo.codenumber}_resumeOrder`}
                                            path={`/${owner.name}/pedido/${codigo.codenumber}/order`}
                                            element={<ArticlesSection owner={owner} table={codigo.table_number}/>}
                                        />
                                        {allsections[index].map((section) => (
                                            <Route
                                                key={`${owner.restaurant}_${codigo.table_number}_${codigo.codenumber}_${section.name}_AñadirProducto`}
                                                path={`/${owner.name}/pedido/${codigo.codenumber}/${section.name}`}
                                                element={<ArticlesSection section={section} owner={owner} table={codigo.table_number}/>}
                                            />
                                        ))}
                                    </>
                                ))}
                            </>
                        ))}

                    </Routes>
                </Router>
            )}
        </>
    );
}

export default App;
