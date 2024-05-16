import '../../styles/cards/ContainerFoodCards.css';
import { FoodCard } from './FoodCard';
import { useEffect, useState } from "react";
import axios from "axios";
import {DetailsButton} from "../buttons/Dinner/DetailsButton";

export function ContenedorFoodcards(props) {
    const [owner, setOwner] = useState([]);
    const [sections, setSections] = useState([]);
    const [dataLoaded, setdataLoaded] = useState(false);

    useEffect(() => {
        console.log(props)
        const infoowner = async () => {
            const ownerResponse = await axios.get('http://172.17.0.2:8888/get_owner/' + props.table.id_restaurant);
            setOwner(ownerResponse.data[0]);
            const sectionResponse = await axios.get('http://172.17.0.2:8888/get_sections/' + props.table.id_restaurant);
            setSections(sectionResponse.data);
            setdataLoaded(true);
        };
        infoowner();
    }, []);

    return (
        <>
            {dataLoaded ? (
                <div className="containerFoodCards">
                    <h1 className="tituloSections">{owner.name}</h1>
                    <img src={`/images/owners/${owner.name}/img/logo.png`} alt={`Logo de ${owner.name}`} className="logoOwnerSections"/>
                    <div>
                        {sections.map((section) => (
                        <FoodCard section={section.name} path={`/images/owners/${owner.name}/img/sections/${section.name}.png`}/>
                        ))}

                        <DetailsButton text={`Ver Detalles del Pedido`}/>
                    </div>
                </div>
            ) : null}
        </>
    );
}
