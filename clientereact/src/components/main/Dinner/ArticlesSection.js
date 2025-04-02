import { useEffect, useState } from "react";
import axios from "axios";
import { DefaultTitle } from "../../titles/DefaultTitle";
import { ReactComponent as Flecha } from '../../../images/arrow-sm-left-svgrepo-com.svg';
import { ReactComponent as Resumen } from '../../../images/resumen.svg';
import { BackButton } from "../../buttons/BackButton";
import "../../../styles/main/dinner/articleSection.css";
import { ArticleCard } from "../../cards/ArticleCard";
import { DetailsButton } from "../../buttons/Dinner/DetailsButton";
import { ENDPOINTS } from '../../../utils/constants'; // Importing ENDPOINTS from constants
import axiosInstance from '../../../utils/axiosConfig';

export function ArticlesSection(props) {
    const [articles, setArticles] = useState([]);
    const [numberArticles, setNumberArticles] = useState([]);
    const [total, setTotal] = useState(0.0);
    const [dataLoaded, setDataLoaded] = useState(false);

    // Obtiene la cantidad de articulos de cada articulo que hay en la cesta
    const numArticlesPromises = async (farticles) => {
        let totalCost = 0.0;
        let cantidad = [];
        await Promise.all(farticles.map(async (article) => {
            const numberArticlesResponse = await axiosInstance.get(`${ENDPOINTS.GET_NUMBER_ARTICLES}/${props.owner.id_restaurant}/${props.table}/${article.id_article}`);
            totalCost += (article.price * numberArticlesResponse.data);
            cantidad.push(numberArticlesResponse.data);
        }));
        setTotal(totalCost);
        setNumberArticles(cantidad);
    };

    // Obtiene los productos que hay en la cesta
    const getResumeOrder = async () => {
        try {
            console.log("Pilla los articulos");
            
            const response = await axiosInstance.get(`${ENDPOINTS.RESUME_ORDER}/${props.owner.id_restaurant}/${props.table}`);
            setArticles(response.data);
            await numArticlesPromises(response.data);
            setDataLoaded(true);
            console.log("Los pilla perfectamente");
        } catch (e) {
            console.error(e);
            // Set empty articles and mark as loaded to avoid UI being stuck
            setArticles([]);
            setDataLoaded(true);
        }
    };

    // Obtiene todos los articulos de una sección
    const getArticles = async () => {
        try {
            const articlesResponse = await axiosInstance.get(`${ENDPOINTS.GET_ARTICLES}/${props.section.id_section}`);
            setArticles(articlesResponse.data);
            await numArticlesPromises(articlesResponse.data);
            setDataLoaded(true);
        } catch (e) {
            console.error(e);
        }
    };

    // Muestra los articulos de la cesta o de una sección determinada
    useEffect(() => {
        setDataLoaded(false);
        setArticles([]);
        let basketInterval;
        let articlesInterval;

        const startBasketInterval = () => {
            getResumeOrder();
            clearInterval(articlesInterval);
            basketInterval = setInterval(getResumeOrder, 1000);
        };

        const startArticlesInterval = () => {
            getArticles(); 
            clearInterval(basketInterval);
            articlesInterval = setInterval(getArticles, 1000);
        };

        if (props.section) {
            startArticlesInterval();
        } else {
            startBasketInterval();
        }

        return () => {
            clearInterval(basketInterval);
            clearInterval(articlesInterval);
        };
    }, [props.section, props.owner.id_restaurant, props.table]);



    return (<>
            {dataLoaded ? (
                <>
                    <div className="background">
                        {props.section ? (<DefaultTitle
                                logo={<BackButton value={<Flecha style={{width: "50px", height: "auto", left: "10px"}}/>}/>}
                                text={
                                    <div className="tituloSectionDinner">
                                        <div className="containerImgArticle">
                                            <img
                                                src={`/images/owners/${props.owner.name}/img/sections/${props.section.name}.png`}
                                                alt={`Sección de ${props.section.name}`}
                                                style={{maxWidth: "100%", maxHeight: "100%", left: "10px"}}
                                            />
                                        </div>
                                        {props.section.name.charAt(0).toUpperCase() + props.section.name.slice(1)}
                                    </div>
                                }
                            />) :
                            (
                                <DefaultTitle
                                    logo={<BackButton
                                        value={<Flecha style={{width: "50px", height: "auto", left: "10px"}}/>}/>}
                                    text={
                                        <div className="tituloSectionDinner">
                                            <div className="containerImgArticle">
                                                <Resumen></Resumen>
                                            </div>
                                            Resumen del Pedido
                                        </div>
                                    }
                                />
                            )}
                        {articles.length > 0 ? (
                            <ArticleCard articles={articles} owner={props.owner} numberArticles={numberArticles}
                                         table={props.table}/>
                        ) : (
                            <div className="container-error-message">
                                <div className="error-message">
                                    {props.section ? "Ups! Parece que al propietario se le ha olvidado añadir artículos a esta sección." :
                                        "Vaya! Aun no hay articulos añadidos a tu pedido."}
                                </div>
                            </div>
                        )}

                        <div className="containerDetailsButton" style={props.section ? null : ({height: "100px"})}>
                            <div>
                                {props.section ? (
                                    <DetailsButton text={`Ver Detalles del Pedido`}/>
                                ) : (
                                    articles.length > 0 ? (<>
                                            <div className="totalPrice">Total: {total}€</div>
                                            <DetailsButton
                                                text={`Realizar Pedido`}
                                                table={props.table}
                                                idOwner={props.owner}
                                            /></>
                                    ) : null
                                )}
                            </div>

                        </div>


                    </div>
                </>
            ) : null}
        </>
    );
}
