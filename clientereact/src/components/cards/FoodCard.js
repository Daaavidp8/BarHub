import '../../styles/cards/foodCard.css';
import { useNavigate} from "react-router-dom";

// Componente que contiene una sección
export function FoodCard(props) {
    const navigate = useNavigate();
    return (
        <>
            <div className="card" onClick={() => navigate(props.section)}>
                <img src={props.path} alt={"Imagen de " + props.section} className="foodimg"/>
                <p>{props.section.charAt(0).toUpperCase() + props.section.slice(1)}</p>
            </div>
        </>
    );
}