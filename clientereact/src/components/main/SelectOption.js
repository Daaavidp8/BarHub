import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { useNavigate } from "react-router-dom";
import "../../styles/main/selectiOption.css";

export function SelectOption(props) {
    const navigate = useNavigate();

    useEffect(() => {
        const getSesion = async () => {
            try {
                const response = await axios.post('http://172.17.0.2:8888/get_sesion', {
                    username: localStorage.getItem('username'),
                    password: localStorage.getItem('password'),
                });

                if (response.data.status){
                    const owner = await axios.get('http://172.17.0.2:8888/get_owner/' + response.data.restaurant);
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
    }

    return (
        <>
            <img src={`/images/owners/${props.name}/img/logo.png`} alt={`Logo de ${props.name}`} className="logoOwner"/>
            <div className="contenedorPermisos"></div>
            <div onClick={closeSesion} className="logout">Cerrar Sesion</div>
        </>
    );
}
