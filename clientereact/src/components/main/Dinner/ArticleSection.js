import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, useLocation } from 'react-router-dom';
import { ArticleCard } from '../../cards/ArticleCard';
import { ReactComponent as Arrow } from '../../../images/arrow-sm-left-svgrepo-com.svg';
import '../../../styles/main/dinner/articleSection.css';
import { DefaultTitle } from '../../titles/DefaultTitle';
import { BackButton } from '../../buttons/BackButton';
import { DetailsButton } from "../../buttons/Dinner/DetailsButton";
import axios from "axios";
import { ENDPOINTS, API_CONFIG } from '../../../utils/constants';
import ClipLoader from "react-spinners/ClipLoader";

export function ArticlesSection(props) {
    const { sectionName, codenumber } = useParams();
    const navigate = useNavigate();
    const location = useLocation();

    // Detect if we are on the order summary route
    const isOrderSummary = location.pathname.endsWith('/order');

    const [articles, setArticles] = useState([]);
    const [selectedArticles, setSelectedArticles] = useState([]);
    const [loading, setLoading] = useState(true);
    const [order, setOrder] = useState(true);

    useEffect(() => {
        const fetchOrderSummary = async () => {
            try {
                const response = await axios.get(
                    `${API_CONFIG.BASE_URL}${ENDPOINTS.RESUME_ORDER}/${props.owner.id_restaurant}/${props.table}`,
                    { headers: API_CONFIG.HEADERS }
                );
                console.log(response.data);
                return response.data;
            } catch (err) {
                console.error(err);
                return []; // Fallback in case of error
            }
        };
    
        const loadArticles = async () => {
            const data = await fetchOrderSummary();
            console.log(data);
            if (data.length != 0) {
                console.log("Id Order = " + data[0].id_order);
                setOrder(data[0].id_order);
            }
        
            if (isOrderSummary) {
                setArticles(data);
            } else if (props.section && props.section.articles && props.section.articles.length > 0) {
                // Creamos un mapa con los id_article y sus quantities desde data
                const quantityMap = new Map(
                    data.map(article => [article.id_article, article.quantity])
                );
        
                // Añadimos quantity a los artículos de props.section.articles
                const enrichedArticles = props.section.articles.map(article => ({
                    ...article,
                    quantity: quantityMap.get(article.id_article) || 0,
                }));
        
                if (sectionName) {
                    const sectionArticles = enrichedArticles.filter(
                        article => article.section_name === sectionName
                    );
                    setArticles(sectionArticles);
                    console.log(sectionArticles);
                } else {
                    setArticles(enrichedArticles);
                    console.log(enrichedArticles);
                }
        
            }
            
            setLoading(false);
        };
        
    
        loadArticles();
    }, [isOrderSummary, props.section, sectionName, props.owner, props.table]);
    

    // Cantidad actual de cada artículo
    const numberArticles = articles.map(article => {
        const found = selectedArticles.find(item => item.id_article === article.id_article);
        return found ? found.quantity : 0;
    });

    const handleViewOrder = () => {
        localStorage.setItem('currentOrder', JSON.stringify(selectedArticles));
        navigate(`/${props.owner?.name}/pedido/${codenumber}/order`);
    };

    return (
        <div className="articleSectionContainer">
            
            <DefaultTitle
                        logo={<BackButton value={<Arrow style={{ width: "50px", height: "auto", left: "10px" }} />} />}
                        text={isOrderSummary ? "Resumen del pedido" : (props.section?.name || sectionName)}
                    />
            {loading ? (
                 <div
                 style={{
                   display: 'flex',
                   flexDirection: 'column',
                   alignItems: 'center',  // ← centra horizontalmente
                   justifyContent: 'center',
                   width: '100%',
                   paddingTop: '24px',
                   paddingBottom: '24px',
                   color: '#4B5563',
                   fontSize: '1.125rem', // 18px (equiv. a text-lg)
                   fontWeight: 500,       // equiv. a font-medium
                 }}
               >
                 <ClipLoader color="#FFFFFF" size={40} />
                 <div style={{ marginTop: '1rem', animation: 'pulse 2s infinite' }}>
                   Cargando artículos...
                 </div>
               </div>
               
               
            ) : articles.length > 0 ? (
                <>
                    <ArticleCard
                        owner={props.owner}
                        table={props.table}
                        articles={articles}
                        numberArticles={numberArticles}
                        order={order}
                    />
                </>
            ) : (
                <p className="noArticles">
                    {isOrderSummary
                        ? "No hay artículos en el pedido actual"
                        : "No hay artículos disponibles en esta sección"}
                </p>
            )}

            {!isOrderSummary && selectedArticles.length > 0 && (
                <div className="orderSummary">
                    <h3>
                        Resumen del pedido (
                        {selectedArticles.reduce((total, item) => total + item.quantity, 0)} artículos)
                    </h3>
                    <button className="viewOrderButton" onClick={handleViewOrder}>
                        Ver pedido completo
                    </button>
                </div>
            )}

            <DetailsButton 
                text={isOrderSummary? "Finalizar pedido" : "Ver pedido"}
                idOwner={props.owner}
                table={props.table ? props.table.table_number : null}
                isOrderSummary={isOrderSummary}
                idOrder={order}
            />
        </div>
    );
}
