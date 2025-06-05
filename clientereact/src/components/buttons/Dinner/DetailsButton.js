import React, { useState, useEffect } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import "../../../styles/buttons/dinner/detailsButton.css";
import axios from "axios";
import { ENDPOINTS,API_CONFIG } from '../../../utils/constants'; // Importing ENDPOINTS from constants
import axiosInstance from '../../../utils/axiosConfig';

export function DetailsButton(props) {
    const navigate = useNavigate();

    const owner = useLocation().pathname.split('/')[1];
    const codeNumber = useLocation().pathname.split('/')[3];

    const goPath = () => {
        window.scrollTo(0, 0);
        navigate(`/${owner}/pedido/${codeNumber}`);
        navigate(`/${owner}/pedido/${codeNumber}/order`);
    };

    const order = async () => {
        try {
            console.log(props.isOrderSummary)
            console.log(props.idOrder)
            await axiosInstance.put(`${ENDPOINTS.ORDER}/${props.idOrder}`);
            localStorage.setItem('pedido', 'true');
            navigate(`/${owner}/pedido/${codeNumber}`);
        } catch (e) {
            console.error("Error al realizar el pedido: " + e);
        }
    };

    return (
        <>
            <div className="detailsButton" onClick={props.isOrderSummary ? order : goPath}>
                <p className="textButton">{props.text}</p>
                <div className="containerImageDinner">
                    <img src={`${API_CONFIG.BASE_URL}/owners/${owner}/img/logo.png`} alt={`Logo de ${owner}`} />
                </div>
            </div>
        </>
    );
}
