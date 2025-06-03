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

    useEffect(() => {
        const fetchOrderSummary = async () => {
            try {
                setLoading(true);
                const res = await axios.get(
                    `${API_CONFIG.BASE_URL}${ENDPOINTS.RESUME_ORDER}/${props.owner.id_restaurant}/${props.table}`,
                    { headers: API_CONFIG.HEADERS }
                );
                setArticles(res.data || []);
            } catch (err) {
                setArticles([]);
            } finally {
                setLoading(false);
            }
        };

        if (isOrderSummary) {
            fetchOrderSummary();
        } else if (props.section && props.section.articles && props.section.articles.length > 0) {
            if (sectionName) {
                const sectionArticles = props.section.articles.filter(article => {
                    return article.section_name === sectionName;
                });
                setArticles(sectionArticles);
            } else {
                setArticles(props.section.articles);
            }
            setLoading(false);
        }
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
                 <ClipLoader color="#4B5563" size={40} />
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
                text="Ver Pedido Actual"
                idOwner={props.owner}
                table={props.table ? props.table.table_number : null}
            />
        </div>
    );
}
