import React, { useEffect, useState } from 'react';
import { useNavigate } from "react-router-dom";
import axiosInstance from '../../utils/axiosConfig';
import { API_CONFIG, ENDPOINTS, STORAGE_KEYS } from '../../utils/constants';
import "../../styles/main/selectiOption.css";

// Componente de elección de administración, recibe listas con las mesas,secciones y productos

export function SelectOption(props) {
    const navigate = useNavigate();

    useEffect(() => {
        const getSesion = async () => {
            try {
                const response = await axiosInstance.post(ENDPOINTS.LOGIN, {
                    username: localStorage.getItem(STORAGE_KEYS.USERNAME),
                    password: localStorage.getItem(STORAGE_KEYS.PASSWORD),
                });

                if (response.data.status){
                    const owner = await axiosInstance.get(`${ENDPOINTS.GET_OWNER}/${response.data.restaurant}`);
                    let container = document.getElementsByClassName('contenedorPermisos')[0]
                    let ownerdata = owner.data[0]
                    container.innerHTML = "";
                    if (response.data.roles.includes(2)) {
                        const sectionLink = document.createElement('div');
                        sectionLink.textContent = 'Administración de secciones';
                        sectionLink.addEventListener('click', () => navigate(`/${ownerdata.name}/admin/sections`));
                        container.appendChild(sectionLink);

                        const workersLink = document.createElement('div');
                        workersLink.textContent = 'Administración de trabajadores';
                        workersLink.addEventListener('click', () => navigate(`/${ownerdata.name}/admin/workers`));
                        container.appendChild(workersLink);
                    }

                    if (response.data.roles.includes(3)) {
                        const workersLink = document.createElement('div');
                        workersLink.textContent = 'Administración de Mesas';
                        workersLink.addEventListener('click', () => navigate(`/${ownerdata.name}/admin/tables`));
                        container.appendChild(workersLink);
                    }
                }else{
                    navigate("/")
                }

            } catch (error) {
                console.error("Error en la comprobación de usuario:", error);
                throw error;
            }
        };

        getSesion().then();
    }, []);

    const closeSesion = () => {
        props.logout()
        navigate("/login");
        window.location.reload();
    }

    return (
        <>
            <img src={`/images/owners/${props.name}/img/logo.png`} alt={`Logo de ${props.name}`} className="logoOwner"/>
            <div className="contenedorPermisos"></div>
            <div onClick={closeSesion} className="logout">Cerrar Sesion</div>
        </>
    );
}
