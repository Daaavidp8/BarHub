import { ContenedorFoodcards } from "../../cards/ContenedorFoodcards";
import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";

// Componente que contiene la vista de las secciones desde el punto de vista del comensal

export function SecondScreen(props) {
    const [showConfirmMessage, setShowConfirmMessage] = useState(false);
    const [message, setMessage] = useState(false);
    const { codenumber } = useParams();
    const [sections, setSections] = useState(props.sections || []);

    useEffect(() => {
        console.log("Va a la vista de la mesa");
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
                <ContenedorFoodcards 
                    table={props.table} 
                    sections={sections}
                    owner={props.owner}
                    codenumber={codenumber}
                />
            </div>

            {/* Display sections with base64 images */}
            <div className="sectionsContainer">
                {sections && sections.length > 0 ? (
                    sections.map((section) => (
                        <div key={section.id_section} className="sectionCard">
                            <h3>{section.name}</h3>
                            {section.image && (
                                <img 
                                    src={section.image} 
                                    alt={`Sección ${section.name}`} 
                                    className="sectionImage"
                                />
                            )}
                        </div>
                    ))
                ) : (
                    <p>No hay secciones disponibles</p>
                )}
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
