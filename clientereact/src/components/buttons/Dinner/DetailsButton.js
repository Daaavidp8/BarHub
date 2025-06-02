import React, { useState, useEffect } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import "../../../styles/buttons/dinner/detailsButton.css";
import axios from "axios";
import { ENDPOINTS,API_CONFIG } from '../../../utils/constants'; // Importing ENDPOINTS from constants

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
            const resumeOrderResponse = await axios.get(`${ENDPOINTS.RESUME_ORDER}/${props.idOwner.id_restaurant}/${props.table}`);

            if (resumeOrderResponse.data.length > 0) {
                await Promise.all(resumeOrderResponse.data.map(async (article, index) => {
                    const numberArticlesResponse = await axios.get(`${ENDPOINTS.GET_NUMBER_ARTICLES}/${props.idOwner.id_restaurant}/${props.table}/${article.id_article}`);
                    await axios.post(`${ENDPOINTS.ORDER_LOG}`, {
                        owner_name: props.idOwner.name,
                        article: article.name,
                        price: article.price,
                        number_table: props.table,
                        codetable: codeNumber,
                        quantity: numberArticlesResponse.data
                    });
                }));
                await axios.delete(`${ENDPOINTS.DELETE_BASKET}/${props.idOwner.id_restaurant}/${props.table}`);
                navigate(`/${owner}/pedido/${codeNumber}`);
                localStorage.setItem('pedido', '2');
            } else {
                navigate(`/${owner}/pedido/${codeNumber}`);
                localStorage.setItem('pedido', '3');
            }
        } catch (e) {
            console.error("Error al realizar el pedido: " + e);
        }
    };

    return (
        <>
            <div className="detailsButton" onClick={props.table ? order : goPath}>
                <p className="textButton">{props.text}</p>
                <div className="containerImageDinner">
                    <img src={`${API_CONFIG.BASE_URL}/owners/${owner}/img/logo.png`} alt={`Logo de ${owner}`} />
                </div>
            </div>
        </>
    );
}
