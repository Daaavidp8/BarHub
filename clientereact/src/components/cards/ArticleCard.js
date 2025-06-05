import axios from "axios";
import { ENDPOINTS,API_CONFIG } from '../../utils/constants'; // Importing ENDPOINTS from constants
import axiosInstance from '../../utils/axiosConfig';
import { useEffect } from "react";

// Componente que contiene los articulos, se utiliza para el resumen del pedido y para mostrar los articulos de una sección

export function ArticleCard(props) {



    const addArticleBasket = async (idArticle) => {
        try {
            console.log("Sending data:", {
                id_article: idArticle,
                number_table: props.table,
                owner_id: props.owner.id_restaurant
            });
            
            // Make sure we're sending the data in the correct format
            const response = await axiosInstance.post(`${ENDPOINTS.CREATE_ROW_BASKET}/${props.owner.id_restaurant}`, {
                id_article: idArticle,
                number_table: props.table
            }, {
                headers: {
                     'Content-Type': 'multipart/form-data'
                }
            });
            
            console.log("Response:", response.data);
            return response.data;
        } catch (error) {
            console.error("Error adding article to basket:", error);
            console.error("Error details:", error.response ? error.response.data : "No response data");
            throw error;
        }
    }

    const deleteArticleBasket = async (idArticle) => {
        try {
            await axiosInstance.delete(`${ENDPOINTS.DELETE_ARTICLE}/${props.order}/${idArticle}`);
        } catch (e) {
            console.error("Error al eliminar el articulo de la lista: " + e)
        }
    }

    return (
        <>
            <div className="containerArticles">
                {props.articles.map((article, index) => (
                    <div className="article" key={article.id_article}>
                        <div className="containerImageAndText">
                            <div className="containerImgArticle">
                                <img
                                    src={
                                        article.image
                                          ? article.image
                                          : `${API_CONFIG.BASE_URL}/owners/${props.owner.name}/img/articles/${article.name}.png`
                                      }
                                    alt={`Imagen de ${article.name}`}
                                    className="articleImage"
                                />
                            </div>
                            <p className="articleDetails">{article.name.charAt(0).toUpperCase() + article.name.slice(1)}</p>
                        </div>
                        <div className="containerPriceAndButtons">
                            <p className="articleDetails">{article.price}€</p>
                            <div className="actionButtons">
                                <div className="minusButton" onClick={() => {
                                    deleteArticleBasket(article.id_article).then(() => {
                                        const element = document.getElementById(index);
                                        if (element && parseInt(element.innerText) > 0) {
                                            element.innerText = parseInt(element.innerText, 10) - 1;
                                        }
                                    }).catch(error => {
                                        console.error('Error al eliminar el producto de la lista:', error);
                                    });
                                }}>-</div>
                                <div className="quantityButton" id={index}>{article.quantity}</div>
                                <div className="plusButton" onClick={() => {
                                    addArticleBasket(article.id_article).then(() => {
                                        const element = document.getElementById(index);
                                        if (element) {
                                            element.innerText = parseInt(element.innerText, 10) + 1;
                                        }
                                    }).catch(error => {
                                        console.error('Error al añadir el producto a la lista:', error);
                                    });
                                }}>+
                                </div>

                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </>
    );
}