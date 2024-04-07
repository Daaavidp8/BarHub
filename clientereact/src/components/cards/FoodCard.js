import '../../styles/cards/foodCard.css';
import {SelectFoodCard} from "../buttons/SelectFoodCard";
export function FoodCard(props) {
    return (
        <>
            <div className="card">
                <img alt={"Imagen de " + props.section} className="foodimg"/>
                <p>{props.section}</p>
                <SelectFoodCard onClick={() => console.log("Boton Pulsado")} value={"Seleccionar " + props.section}/>
            </div>
        </>
    );
}