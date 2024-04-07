import '../../styles/buttons/mainButton.css';

export function Mainbutton({ onClick }) { // Aquí cambia de onclick a onClick
    return (
        <button className="btn-class-name" onClick={onClick}> {/* Cambia onclick a onClick */}
            <span className="back"></span>
            <span className="front"></span>
        </button>
    );
}
