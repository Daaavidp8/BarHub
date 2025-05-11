import {CodeForm} from '../../form/CodeForm';
import "../../../styles/main/dinner/codeScreen.css"

// Componente utilizado para intoducir el código para iniciar el pedido

export function FirstScreen(props) {
    return (
        <>
            <div style={{height: "100vh"}}>
                <div className="containerCodeScreen">
                    <img src={`/owners/${props.restaurant.name}/img/logo.png`}
                         alt={`Logo de ${props.restaurant.name}`} className="logoOwnerDinners"/>
                    <h1 className="titleDinner">Bienvenido a {props.restaurant.name}</h1>
                    <CodeForm restaurant={props.restaurant}/>
                </div>
            </div>
        </>
    );
}