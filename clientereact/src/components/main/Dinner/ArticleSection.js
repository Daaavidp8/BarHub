import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArticleCard } from '../../cards/ArticleCard';
import { ReactComponent as Arrow } from '../../../images/arrow-sm-left-svgrepo-com.svg'; // Asegúrate de que la ruta es correcta
import '../../../styles/main/dinner/articleSection.css';
import { DefaultTitle } from '../../titles/DefaultTitle';
import { BackButton } from '../../buttons/BackButton';
import { DetailsButton } from "../../buttons/Dinner/DetailsButton";

export function ArticlesSection(props) {
    const { sectionName, codenumber } = useParams();
    const navigate = useNavigate();

    const [articles, setArticles] = useState(props.section.articles || []);
    const [selectedArticles, setSelectedArticles] = useState([]);
    const [loading, setLoading] = useState(!props.section.articles);

    useEffect(() => {
        if (props.section.articles && props.section.articles.length > 0) {
            if (sectionName) {
                const sectionArticles = props.articles.filter(article => {
                    return article.section_name === sectionName;
                });
                setArticles(sectionArticles);
            } else {
                setArticles(props.section.articles);
            }
            setLoading(false);
        }
        console.log(props.section.name);
    }, [props.section.articles, sectionName]);

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
            {loading ? (
                <div className="loading">Cargando artículos...</div>
            ) : articles.length > 0 ? (
                <>
                    <DefaultTitle
                        logo={<BackButton value={<Arrow style={{ width: "50px", height: "auto", left: "10px" }} />} />}
                        text={props.section.name}
                    />
                    <ArticleCard
                        owner={props.owner}
                        table={props.table}
                        articles={articles}
                        numberArticles={numberArticles}
                    />
                </>
            ) : (
                <p className="noArticles">No hay artículos disponibles en esta sección</p>
            )}

            {selectedArticles.length > 0 && (
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
