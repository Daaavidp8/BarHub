import '../../styles/cards/ContainerFoodCards.css';
import { FoodCard } from './FoodCard';
import { useEffect, useState } from "react";
import axios from "axios";
import { DetailsButton } from "../buttons/Dinner/DetailsButton";
import { ENDPOINTS } from '../../utils/constants'; // Importing ENDPOINTS from constants
import axiosInstance from '../../utils/axiosConfig';

// Componente que contiene la lista de todas las secciones del restaurante

export function ContenedorFoodcards(props) {
    const [owner, setOwner] = useState(props.table.id_restaurant);
    const [sections, setSections] = useState([]);
    const [dataLoaded, setdataLoaded] = useState(false);

    useEffect(() => {
        const infoowner = async () => {
            try {
                console.log("Pilla las secciones");
                try {
                    const response = await axiosInstance.get(`${ENDPOINTS.GET_SECTIONS}/${props.table.id_restaurant}`);
                    setSections(response.data);
                    setdataLoaded(true); 
                } catch (sectionsError) {
                    console.error("Error fetching sections, using fallback:", sectionsError);
                }
                console.log("LAs pilla bien");
            } catch (error) {
                console.error("Error in main process:", error);
            }
        };
        infoowner();
    }, [props.table.id_restaurant]);

    return (
        <>
            {dataLoaded ? (
                <div className="containerFoodCards">
                    <h1 className="tituloSections">{owner.name}</h1>
                    <img src={`/images/owners/${owner.name}/img/logo.png`} alt={`Logo de ${owner.name}`} className="logoOwnerSections"/>
                    <div>
                        {sections.map((section, index) => (
                            <FoodCard key={`Section_${section}_${index}`} section={section.name} path={`/images/owners/${owner.name}/img/sections/${section.name}.png`} />
                        ))}

                        <DetailsButton text={`Ver Detalles del Pedido`} />
                    </div>
                </div>
            ) : null}
        </>
    );
}
