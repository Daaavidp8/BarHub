import { useState, useEffect } from 'react';
import axiosInstance from '../utils/axiosConfig';
import { ENDPOINTS, STORAGE_KEYS } from '../utils/constants';

export const useFetchData = (loggedIn) => {
    const [data, setData] = useState({
        userRole: [],
        workers: [],
        userRestaurant: [],
        owners: [],
        allSections: [],
        sections: [],
        articles: [],
        codes: [],
        tables: [],
        dataLoaded: false,
    });

    useEffect(() => {
        const fetchData = async () => {
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
                        setData((prevData) => ({ ...prevData, dataLoaded: false }));
                        return;
                    }
                }

                if (loggedIn && localStorage.getItem(STORAGE_KEYS.USERNAME) !== '') {
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

                        const userRestaurantresponse = await axiosInstance.get(`${ENDPOINTS.GET_OWNER}/${response.data.restaurant}`);
                        const sectionsResponse = await axiosInstance.get(`${ENDPOINTS.GET_SECTIONS}/${response.data.restaurant}`);
                        const sectionsData = sectionsResponse.data;

                        const sectionsPromises = sectionsData.map(async (section) => {
                            const responseArticles = await axiosInstance.get(`${ENDPOINTS.GET_ARTICLES}/${section.id_section}`);
                            return responseArticles.data;
                        });

                        const articlesData = await Promise.all(sectionsPromises);
                        const allArticles = articlesData.flat();

                        const tableResponse = await axiosInstance.get(`${ENDPOINTS.GET_TABLES}/${response.data.restaurant}`);
                        const workersResponse = await axiosInstance.get(`${ENDPOINTS.GET_WORKERS}/${response.data.restaurant}`);

                        setData({
                            userRole: roles,
                            userRestaurant: userRestaurantresponse.data[0],
                            sections: sectionsData,
                            articles: allArticles,
                            tables: tableResponse.data,
                            workers: workersResponse.data,
                            dataLoaded: true,
                        });
                    }
                }
            } catch (error) {
                console.error('Error al obtener datos:', error);
            } finally {
                const ownersResponse = await axiosInstance.get(ENDPOINTS.GET_OWNERS);
                const codesPromises = ownersResponse.data.map(async (owner) => {
                    const codesResponse = await axiosInstance.get(`${ENDPOINTS.GET_TABLES}/${owner.id_restaurant}`);
                    return codesResponse.data || [];
                });

                const allCodes = await Promise.all(codesPromises);

                const allSectionsPromises = ownersResponse.data.map(async (owner) => {
                    const sectionsResponse = await axiosInstance.get(`${ENDPOINTS.GET_SECTIONS}/${owner.id_restaurant}`);
                    return sectionsResponse.data || [];
                });

                const allSectionsData = await Promise.all(allSectionsPromises);

                setData((prevData) => ({
                    ...prevData,
                    owners: ownersResponse.data,
                    codes: allCodes,
                    allSections: allSectionsData,
                }));
            }
        };

        fetchData();
    }, [loggedIn]);

    return data;
};