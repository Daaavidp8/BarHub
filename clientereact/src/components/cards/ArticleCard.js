import axios from "axios";

// Componente que contiene los articulos, se utiliza para el resumen del pedido y para mostrar los articulos de una sección

export function ArticleCard(props) {

    const addArticleBasket = async (idArticle) => {
        await axios.post('http://172.17.0.2:8888/create_row_basket/' + props.owner.id_restaurant, {
            id_article: idArticle,
            number_table: props.table,
        });
    }

    const deleteArticleBasket = async (idArticle) => {
        try {
            await axios.delete(`http://172.17.0.2:8888/delete_article_basket/${props.owner.id_restaurant}/${props.table}/${idArticle}`);
        }catch (e){
            console.error("Error al eliminar el articulo de la lista: " + e)
        }
    }

    return (
        <>
            <div className="containerArticles">
                {props.articles.map((article,index) => (
                    <div className="article" key={article.id_article}>
                        <div className="containerImageAndText">
                            <div className="containerImgArticle">
                                <img
                                    src={`/images/owners/${props.owner.name}/img/articles/${article.name}.png`}
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
                                <div className="quantityButton" id={index}>{props.numberArticles[index]}</div>
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