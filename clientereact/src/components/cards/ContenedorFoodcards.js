import React from "react";
import { Link } from "react-router-dom";
import "../../styles/cards/foodcards.css";
import { DetailsButton } from "../buttons/Dinner/DetailsButton";

export function ContenedorFoodcards(props) {
    const { table, sections, owner, codenumber } = props;
    
    return (
        <div className="foodCardsContainer">
            <h2 style={{ color: 'white' }}>Menú de {owner?.name || "Restaurante"}</h2>
            
            {/* Display table information if available */}
            {table && (
                <div className="tableInfo">
                    <h3>Mesa: {table.table_number}</h3>
                    <p>Código: {table.codenumber || codenumber}</p>
                </div>
            )}
            
            {/* Display sections with their base64 images */}
            <div className="sectionsGrid">
                {sections && sections.length > 0 ? (
                    sections.map((section) => (
                        <Link 
                            key={section.id_section} 
                            to={`/${owner.name}/pedido/${codenumber || (table && table.codenumber)}/${section.name}`}
                            className="sectionCard"
                        >
                            <div className="sectionImageContainer">
                                {section.image ? (
                                    <img 
                                        src={section.image} 
                                        alt={`Sección ${section.name}`} 
                                        className="sectionImage"
                                    />
                                ) : (
                                    <div className="noImage">Sin imagen</div>
                                )}
                            </div>
                            <h3 className="sectionName">{section.name}</h3>
                        </Link>
                    ))
                ) : (
                    <p className="noSections">No hay secciones disponibles</p>
                )}
            </div>
            
            {/* Link to view current order */}
            <DetailsButton 
                text="Ver Pedido Actual"
                idOwner={owner}
                table={table ? table.table_number : null}
            />
        </div>
    );
}
