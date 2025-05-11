import React, { useEffect, useState, useCallback } from 'react';
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
import { ArticlesSection } from "./components/main/Dinner/ArticleSection";
import axiosInstance from './utils/axiosConfig';
import { ENDPOINTS, STORAGE_KEYS } from './utils/constants';
import { useFetchData } from './hooks/useFetchData';

// Removed unused imports axios and all
// library.add(fas);

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


    const fetchData = useCallback(async () => {
        try {
            const token = localStorage.getItem(STORAGE_KEYS.TOKEN);
            
            if (!token) {
                const response = await axiosInstance.post(ENDPOINTS.LOGIN, {
                    username: 'diner',
                    password: 'diner',
                });

                if (response.data.status) {
                    localStorage.setItem(STORAGE_KEYS.TOKEN, response.data.token);
                } else {
                    setLoggedIn(false);
                    return;
                }
            }

            if (loggedIn && localStorage.getItem(STORAGE_KEYS.USERNAME) !== '') {
                try {
                    const response = await axiosInstance.post(ENDPOINTS.LOGIN, {
                        username: localStorage.getItem(STORAGE_KEYS.USERNAME),
                        password: localStorage.getItem(STORAGE_KEYS.PASSWORD),
                    });

                    if (response.data.status) {
                        localStorage.setItem(STORAGE_KEYS.TOKEN, response.data.token);

                        let roles = [];
                        response.data.roles.forEach((role) => {
                            roles.push(role);
                        });

                        setUserRole((prevRoles) => [...prevRoles, ...roles]);
                        
                        // Add error handling for restaurant fetch
                        try {
                            const userRestaurantresponse = await axiosInstance.get(`${ENDPOINTS.GET_OWNER}/${response.data.restaurant}`);
                            if (userRestaurantresponse.data && userRestaurantresponse.data[0]) {
                                setuserRestaurant((prevRestaurant) => [userRestaurantresponse.data[0]]);
                            } else {
                                console.error('Invalid restaurant data received:', userRestaurantresponse.data);
                            }
                        } catch (restaurantError) {
                            console.error('Error fetching restaurant data:', restaurantError);
                        }

                        // Continue with other data fetching
                        const sectionsResponse = await axiosInstance.get(`${ENDPOINTS.GET_SECTIONS}/${response.data.restaurant}`);
                        setSections((prevSections) => [...prevSections, ...sectionsResponse.data]);

                        const sectionsPromises = sectionsResponse.data.map(async (section) => {
                            const responseArticles = await axiosInstance.get(`${ENDPOINTS.GET_ARTICLES}/${section.id_section}`);
                            return responseArticles.data;
                        });

                        const articlesData = await Promise.all(sectionsPromises);
                        const allArticles = articlesData.flat();
                        setArticles((prevArticles) => [...prevArticles, ...allArticles]);

                        // Try to fetch tables with a minimal headers approach
                                        try {
                                            const minimalAxios = axios.create({
                                                baseURL: axiosInstance.defaults.baseURL,
                                                headers: {
                                                    'Authorization': `Bearer ${localStorage.getItem(STORAGE_KEYS.TOKEN)}`,
                                                    'Accept': 'application/json'
                                                }
                                            });
                                            
                                            const tableResponse = await minimalAxios.get(`${ENDPOINTS.GET_TABLES}/${response.data.restaurant}`);
                                            setTables((prevTables) => [...prevTables, ...tableResponse.data]);
                                        } catch (tableError) {
                                            console.error('Error fetching tables:', tableError);
                                        }

                        const workersResponse = await axiosInstance.get(`${ENDPOINTS.GET_WORKERS}/${response.data.restaurant}`);
                        setWorkers((prevWorkers) => [...prevWorkers, ...workersResponse.data]);
                    }
                } catch (loginError) {
                    console.error('Error during login:', loginError);
                }
            }
        } catch (error) {
            console.error('Error al obtener datos:', error);
        } finally {
            try {
                // Fetch owners with error handling
                const ownersResponse = await axiosInstance.get(ENDPOINTS.GET_OWNERS);
                const ownersData = ownersResponse.data || [];
                console.log('Owners:', ownersData);
                
                // Clear previous data to avoid duplicates
                setOwners(ownersData);
                
                // Process codes with error handling
                const codesPromises = ownersData.map(async (owner) => {
                    try {
                        // Create a new axios instance with minimal headers for this specific request
                        const minimalAxios = axios.create({
                            baseURL: axiosInstance.defaults.baseURL,
                            headers: {
                                'Authorization': `Bearer ${localStorage.getItem(STORAGE_KEYS.TOKEN)}`,
                                'Accept': 'application/json'
                            }
                        });
                        
                        const codesResponse = await minimalAxios.get(`${ENDPOINTS.GET_TABLES}/${owner.id_restaurant}`);
                        return codesResponse.data || [];
                    } catch (error) {
                        console.error(`Error fetching tables for restaurant ${owner.id_restaurant}:`, error);
                        return [];
                    }
                });
                
                const allCodes = await Promise.all(codesPromises);
                console.log('Codes:', allCodes);
                setCodes(allCodes); // Set directly instead of appending
                
                // Process sections with error handling
                const allSectionsPromises = ownersData.map(async (owner) => {
                    try {
                        const sectionsResponse = await axiosInstance.get(`${ENDPOINTS.GET_SECTIONS}/${owner.id_restaurant}`);
                        return sectionsResponse.data || [];
                    } catch (error) {
                        console.error(`Error fetching sections for restaurant ${owner.id_restaurant}:`, error);
                        return [];
                    }
                });
                
                const allSectionsData = await Promise.all(allSectionsPromises);
                console.log('All Sections:', allSectionsData);
                setAllSections(allSectionsData); // Set directly instead of appending
                
                setDataLoaded(true);
            } catch (finalError) {
                console.error('Error in final data fetching:', finalError);
                setDataLoaded(true); // Still set dataLoaded to true to prevent infinite loading
            }
        }
    }, [loggedIn]);

    useEffect(() => {
        fetchData();
    }, [fetchData]);

    // Funciones para realizar el login
    const handleLogin = () => {
        localStorage.setItem('loggedIn', 'true');
        setLoggedIn(true);
    };

    const handleLogout = () => {
        localStorage.setItem('loggedIn', 'false');
        localStorage.removeItem(STORAGE_KEYS.TOKEN); // Remove JWT token on logout
        localStorage.setItem(STORAGE_KEYS.PASSWORD, '');
        localStorage.setItem(STORAGE_KEYS.USERNAME, '');
        setLoggedIn(false);
    };

    return (
        <>
            {dataLoaded && (
                <Router>
                    <Routes>
                        <Route path="/login" element={<Login onLogin={handleLogin} />} />
                        {!loggedIn || localStorage.getItem(STORAGE_KEYS.USERNAME) === '' ? (
                            // Routes for non-logged in users
                            <>
                                {/* Public routes */ }
                                {owners.length > 0 && owners.map((owner, index) => (
                                    // Restaurant routes for public access
                                    <React.Fragment key={`restaurant-fragment-${owner.id_restaurant}`}>
                                        <Route
                                            key={`restaurante_${owner.id_restaurant}_${owner.name}`}
                                            path={`/${owner.name}`}
                                            element={<FirstScreen restaurant={owner} />}
                                        />
                                        {/* Add a generic route for any code */}
                                        <Route
                                            key={`${owner.name}_generic_pedido`}
                                            path={`/${owner.name}/pedido/:codenumber`}
                                            element={<SecondScreen 
                                                owner={owner} 
                                                sections={allsections && allsections[index] ? allsections[index] : []}
                                            />}
                                        />
                                        {codes && codes[index] && codes[index].length > 0 && codes[index].map((codigo) => (
                                            <React.Fragment key={`code-fragment-${codigo.codenumber}`}>
                                                <Route
                                                    key={`${owner.id_restaurant}_${codigo.table_number}_${codigo.codenumber}_resumeOrder`}
                                                    path={`/${owner.name}/pedido/${codigo.codenumber}/order`}
                                                    element={<ArticlesSection owner={owner} table={codigo.table_number}/>}
                                                />
                                                {allsections && allsections[index] && allsections[index].length > 0 && allsections[index].map((section) => (
                                                    <Route
                                                        key={`${owner.id_restaurant}_${codigo.table_number}_${codigo.codenumber}_${section.name}_AñadirProducto`}
                                                        path={`/${owner.name}/pedido/${codigo.codenumber}/${section.name}`}
                                                        element={<ArticlesSection section={section} owner={owner} table={codigo.table_number}/>}
                                                    />
                                                ))}
                                            </React.Fragment>
                                        ))}
                                    </React.Fragment>
                                ))}
                                <Route path="*" element={<Navigate to="/login" />} />
                            </>
                        ) : (
                            // Routes for logged in users
                            <>
                                {console.log("Checking roles - loggedIn:", loggedIn, "userRole:", userRole)}
                                
                                {/* Admin routes */}
                                <Route path="/admin" element={<Admin logout={handleLogout} owners={owners} />} />
                                <Route path="/" element={<Navigate to="/admin" />} />
                                {owners.map((owner, index) => (
                                    <Route
                                        key={`${owner.id_restaurant}_${index}_owners`}
                                        path={`/admin/modify_owner/${owner.id_restaurant}`}
                                        element={<ActionOwner action={false} data={owner} />}
                                    />
                                ))}
                                <Route path="/admin/add_owner" element={<ActionOwner action={true} />} />
                                
                                {/* Restaurant owner routes */}
                                {userRestaurant && userRestaurant.length > 0 && (
                                    <>
                                        <Route
                                            path={`/${userRestaurant[0].name}/admin`}
                                            element={<SelectOption logout={handleLogout} name={userRestaurant[0].name} restaurant={userRestaurant[0].id_restaurant} />}
                                        />
                                        
                                        {/* Owner specific routes */}
                                        {userRole.includes(2) && (
                                            <>
                                                <Route
                                                    path={`/${userRestaurant[0].name}/admin/sections`}
                                                    element={<SectionAdminOwner restaurant={userRestaurant[0]} title={'Secciones'} table={"sections"} />}
                                                />
                                                {/* Other owner routes... */}
                                            </>
                                        )}
                                        
                                        {/* Waiter specific routes */}
                                        {userRole.includes(3) && (
                                            <>
                                                <Route
                                                    path={`/${userRestaurant[0].name}/admin/tables`}
                                                    element={<SectionAdminOwner restaurant={userRestaurant[0]} title={'Mesas'} table={"tables"} />}
                                                />
                                                {/* Other waiter routes... */}
                                            </>
                                        )}
                                    </>
                                )}
                                
                                {/* Public restaurant routes should also be accessible when logged in */}
                                {owners.length > 0 && owners.map((owner, index) => (
                                    <React.Fragment key={`restaurant-fragment-${owner.id_restaurant}`}>
                                        <Route
                                            key={`restaurante_${owner.id_restaurant}_${owner.name}`}
                                            path={`/${owner.name}`}
                                            element={<FirstScreen restaurant={owner} />}
                                        />
                                        {/* Create specific routes for each code instead of using a parameter */}
                                        {codes && codes[index] && codes[index].map((codigo) => (
                                            <React.Fragment key={`code-fragment-${codigo.codenumber}`}>
                                                <Route
                                                    key={`${owner.name}_${codigo.table_number}_${codigo.codenumber}_Pedido`}
                                                    path={`/${owner.name}/pedido/${codigo.codenumber}`}
                                                    element={<SecondScreen table={codigo} />}
                                                />
                                                <Route
                                                    key={`${owner.id_restaurant}_${codigo.table_number}_${codigo.codenumber}_resumeOrder`}
                                                    path={`/${owner.name}/pedido/${codigo.codenumber}/order`}
                                                    element={<ArticlesSection owner={owner} table={codigo.table_number}/>}
                                                />
                                                {allsections && allsections[index] && allsections[index].length > 0 && allsections[index].map((section) => (
                                                    <Route
                                                        key={`${owner.id_restaurant}_${codigo.table_number}_${codigo.codenumber}_${section.name}_AñadirProducto`}
                                                        path={`/${owner.name}/pedido/${codigo.codenumber}/${section.name}`}
                                                        element={<ArticlesSection section={section} owner={owner} table={codigo.table_number}/>}
                                                    />
                                                ))}
                                            </React.Fragment>
                                        ))}
                                    </React.Fragment>
                                ))}
                            </>
                        )}
                    </Routes>
                </Router>
            )}
        </>
    );
}

export default App;
