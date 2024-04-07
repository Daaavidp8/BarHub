import '../../styles/cards/ContainerFoodCards.css';
import { FoodCard } from './FoodCard';
export function ContenedorFoodcards() {
    return (
        <>
            <div className="containerFoodCards">
                <FoodCard section="Bebidas"/>
                <FoodCard section="Tapas"/>
                <FoodCard section="Pizzas"/>
                <FoodCard section="Bocadillos"/>
                <FoodCard section="Postres"/>
                <FoodCard section="Menús"/>
                <FoodCard section="Ofertas"/>
            </div>
        </>
    );
}