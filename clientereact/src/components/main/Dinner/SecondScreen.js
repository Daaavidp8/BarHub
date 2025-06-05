import { ContenedorFoodcards } from "../../cards/ContenedorFoodcards";
import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { DetailsButton } from "../../buttons/Dinner/DetailsButton";

// Componente que contiene la vista de las secciones desde el punto de vista del comensal

export function SecondScreen(props) {
    const [showConfirmMessage, setShowConfirmMessage] = useState(false);
    const [message, setMessage] = useState(false);
    const { codenumber } = useParams();
    const [sections, setSections] = useState(props.sections || []);

    useEffect(() => {
        console.log("Va a la vista de la mesa");
        const isPedido = localStorage.getItem('pedido') === 'true';
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
                {sections && sections.length > 0 ? (
            <div className="containerCodeScreen">
                        <ContenedorFoodcards 
                        table={props.table} 
                        sections={sections}
                        owner={props.owner}
                        codenumber={codenumber}
                    />
                    
            </div>
                    )
                 : (
                    <p>No hay secciones disponibles</p>
                )}

            {/* <div className="sectionsContainer">
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
            </div> */}

            {showConfirmMessage && (
                <div className="message confirmMessage">
                    "Pedido Realizado Correctamente!"
                </div>
            )}

            <DetailsButton 
                text="Ver Pedido Actual"
                idOwner={props.owner}
                table={props.table ? props.table.table_number : null}
            />
        </>
    );
}
