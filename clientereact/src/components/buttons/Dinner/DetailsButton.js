import React, {useState, useEffect} from "react";
import {useLocation, useNavigate} from "react-router-dom";
import "../../../styles/buttons/dinner/detailsButton.css";
import axios from "axios";

export function DetailsButton(props) {
    const navigate = useNavigate();

    const [articles, setArticles] = useState([]);
    const owner = useLocation().pathname.split('/')[1];
    const codeNumber = useLocation().pathname.split('/')[3];

    const goPath = () => {
        window.scrollTo(0, 0);
        navigate(`/${owner}/pedido/${codeNumber}`);
        navigate(`/${owner}/pedido/${codeNumber}/order`);
    };

    const order = async () => {
        try {
            const resumeOrderResponse = await axios.get(`http://172.17.0.2:8888/resumeOrder/${props.idOwner.id_restaurant}/${props.table}`);
            setArticles(resumeOrderResponse.data);

            if (resumeOrderResponse.data.length > 0){
                await Promise.all(resumeOrderResponse.data.map(async (article, index) => {
                    await axios.post('http://172.17.0.2:8888/order_log', {
                        owner_name: props.idOwner.name,
                        article: article.name,
                        price: article.price,
                        number_table: props.table,
                        codetable: codeNumber,
                        quantity: props.numberArticles[index]
                    });
                }));
                await axios.delete(`http://172.17.0.2:8888/delete_basket/${props.idOwner.id_restaurant}/${props.table}`);
                navigate(`/${owner}/pedido/${codeNumber}`);
                localStorage.setItem('pedido','2');
            }else {
                navigate(`/${owner}/pedido/${codeNumber}`);
                localStorage.setItem('pedido','3');
            }
        } catch (e) {
            console.error("Error al realizar el pedido: " + e);
        }
    };

    return (
        <>
            <div className="detailsButton" onClick={props.articles ? order : goPath}>
                <p className="textButton">{props.text}</p>
                <div className="containerImageDinner">
                    <img src={`/images/owners/${owner}/img/logo.png`} alt={`Logo de ${owner}`} />
                </div>
            </div>
        </>
    );
}
