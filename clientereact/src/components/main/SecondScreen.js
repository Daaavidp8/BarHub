import {useParams} from "react-router-dom";
import {ContenedorFoodcards} from "../cards/ContenedorFoodcards";

export function SecondScreen() {
    const { id } = useParams();
    return (
        <>
            <h1>Codigo del pedido: {id}</h1>
            <ContenedorFoodcards/>
        </>
    );
}