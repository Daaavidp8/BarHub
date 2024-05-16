import { ContenedorFoodcards } from "../../cards/ContenedorFoodcards";
import { useEffect, useState } from "react";

export function SecondScreen(props) {
    const [showConfirmMessage, setShowConfirmMessage] = useState(false);
    const [message, setMessage] = useState(false);

    useEffect(() => {
        const isPedido = localStorage.getItem('pedido') !== 'false';
        setShowConfirmMessage(isPedido);
        if (isPedido) {
            setMessage(localStorage.getItem('pedido'))
            setTimeout(() => {
                setShowConfirmMessage(false);
                localStorage.setItem('pedido', 'false');
            }, 4000);
        }
    }, []);

    return (
        <>
            <div className="containerCodeScreen">
                <ContenedorFoodcards table={props.table}/>
            </div>

            {showConfirmMessage && (
                <div className={"message " + (message == 2 ? "confirmMessage" : "errorMessage")}>
                    {message == 2 ?
                        "Pedido Realizado Correctamente!"
                        :
                        "Ha ocurrido un error. Quizás Alguien de tu mesa ya ha realizado el pedido."}
                </div>
            )}
        </>
    );
}
