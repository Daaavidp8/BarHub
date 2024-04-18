import React, { useEffect } from 'react';
import axios from 'axios';
import { DefaultTitle } from '../../titles/DefaultTitle';
import { ReactComponent as Flecha } from '../../../images/arrow-sm-left-svgrepo-com.svg';
import {useNavigate} from "react-router-dom";
import {BackButton} from "../../buttons/BackButton";
import "../../../styles/main/owners/SectionAdmin.css"

export function SectionAdminOwner() {
    const navigate = useNavigate();
    useEffect(() => {
        const requestSection = async () => {
            const response = await axios.post('http://172.17.0.2:8888/get_sesion', {
                username: localStorage.getItem('username'),
                password: localStorage.getItem('password'),
            });

            if (response.data.status){
                const responserestaurant = await axios.get('http://172.17.0.2:8888/get_owner/' + response.data.restaurant);
                let restaurantName = responserestaurant.data[0].name;
                const responseSections = await axios.get('http://172.17.0.2:8888/get_sections/' + response.data.restaurant);
                let sections = responseSections.data;
                let containerSections = document.getElementsByClassName('containerSections')[0];
                containerSections.innerHTML = "";
                sections.forEach((section) => {
                    let div = document.createElement('div');
                    div.classList.add('section');
                    div.innerHTML = "<p>" + section.name + "</p>";
                    div.addEventListener('click', () => navigate(`/${restaurantName}/admin/${section.name}`));
                    containerSections.appendChild(div);
                })
            }
        }
        requestSection().then();

    }, []);

    return (
        <>
            <DefaultTitle text="Administración de Secciones" logo={<BackButton value={<Flecha style={{ width: "50px", height: "auto", left: "10px" }} />}/>}/>
            <div className='containerSections'></div>
            <div className="background"></div>
        </>
    );
}
