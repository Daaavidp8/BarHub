import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import '../../../styles/main/dinner/articleSection.css';

export function ArticlesSection(props) {
    const { sectionName, codenumber } = useParams();
    const navigate = useNavigate();
    const [articles, setArticles] = useState([]);
    const [selectedArticles, setSelectedArticles] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        // Filter articles by section name
        if (props.articles && props.articles.length > 0) {
            const sectionArticles = props.articles.filter(article => {
                return article.section_name === sectionName;
            });
            setArticles(sectionArticles);
            setLoading(false);
        }
    }, [props.articles, sectionName]);

    const handleAddToOrder = (article) => {
        setSelectedArticles(prev => {
            // Check if article is already in the order
            const existingIndex = prev.findIndex(item => item.id_article === article.id_article);
            
            if (existingIndex >= 0) {
                // Article exists, increase quantity
                const newOrder = [...prev];
                newOrder[existingIndex] = {
                    ...newOrder[existingIndex],
                    quantity: newOrder[existingIndex].quantity + 1
                };
                return newOrder;
            } else {
                // Add new article with quantity 1
                return [...prev, { ...article, quantity: 1 }];
            }
        });
    };

    const handleViewOrder = () => {
        // Save selected articles to localStorage or pass via state
        localStorage.setItem('currentOrder', JSON.stringify(selectedArticles));
        navigate(`/${props.owner?.name}/pedido/${codenumber}/order`);
    };

    return (
        <div className="articleSectionContainer">
            <h2>{sectionName}</h2>
            
            {loading ? (
                <div className="loading">Cargando artículos...</div>
            ) : articles.length > 0 ? (
                <div className="articlesGrid">
                    {articles.map(article => (
                        <div key={article.id_article} className="articleCard">
                            <div className="articleImageContainer">
                                {article.image ? (
                                    <img 
                                        src={article.image} 
                                        alt={article.name} 
                                        className="articleImage"
                                    />
                                ) : (
                                    <div className="noArticleImage">Sin imagen</div>
                                )}
                            </div>
                            <div className="articleInfo">
                                <h3>{article.name}</h3>
                                <p className="articleDescription">{article.description}</p>
                                <p className="articlePrice">{article.price}€</p>
                                <button 
                                    className="addToOrderButton"
                                    onClick={() => handleAddToOrder(article)}
                                >
                                    Añadir al pedido
                                </button>
                            </div>
                        </div>
                    ))}
                </div>
            ) : (
                <p className="noArticles">No hay artículos disponibles en esta sección</p>
            )}
            
            {selectedArticles.length > 0 && (
                <div className="orderSummary">
                    <h3>Resumen del pedido ({selectedArticles.reduce((total, item) => total + item.quantity, 0)} artículos)</h3>
                    <button className="viewOrderButton" onClick={handleViewOrder}>
                        Ver pedido completo
                    </button>
                </div>
            )}
            
            <button 
                className="backButton"
                onClick={() => navigate(`/${props.owner?.name}/pedido/${codenumber}`)}
            >
                Volver a secciones
            </button>
        </div>
    );
}