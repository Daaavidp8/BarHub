import { ContenedorFoodcards } from "../../cards/ContenedorFoodcards";
import { useEffect, useState } from "react";

export function SecondScreen(props) {

    return (
        <>
            <div className="containerCodeScreen">
                <ContenedorFoodcards table={props.table}/>
            </div>
        </>
    );
}
