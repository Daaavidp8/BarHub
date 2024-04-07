import '../../styles/buttons/selectFoodCard.css';

export function SelectFoodCard({ onClick,value }) {
    return (
        <button className="button-59" onClick={onClick}>{value}</button>
    );
}