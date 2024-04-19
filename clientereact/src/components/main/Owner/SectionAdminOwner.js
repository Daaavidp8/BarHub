import React, {useEffect, useState} from 'react';
import axios from 'axios';
import { DefaultTitle } from '../../titles/DefaultTitle';
import { ReactComponent as Flecha } from '../../../images/arrow-sm-left-svgrepo-com.svg';
import {useNavigate} from "react-router-dom";
import {BackButton} from "../../buttons/BackButton";
import "../../../styles/main/owners/SectionAdmin.css"
import {Addbutton} from "../../buttons/Owner/Addbutton";
import {ModifyButton} from "../../buttons/ModifyButton";
import {DeleteButton} from "../../buttons/DeleteButton";

export function SectionAdminOwner(props) {
    const navigate = useNavigate();
    const [elements,setelements] = useState(props.elements);

    const showModalDelete = () => {

    };

    const deleteElement = async (id) => {
        if (props.text === "Secciones"){
            // TODO Cambiar el nombre de la ruta y quitar la S en el sevidor
            await axios.delete(`http://172.17.0.2:8888/delete_${props.table}/${id}`);
        }
        setelements(elements.filter(element => element[0] !== id));
    };

    return (
        <>
            <DefaultTitle text={`Administración de ${props.text}`}
                          logo={<BackButton value={<Flecha style={{width: "50px", height: "auto", left: "10px"}}/>}/>}/>
            <Addbutton text={'Añadir ' + props.title}/>
            <div className='containerElements'>
                {elements.map((element,index) => (
                    <div>
                        <div>
                            <img src={`/images/owners/${props.restaurant}/img/${props.table}/${index}.png`}/>
                            <p>{element.name}</p>
                        </div>
                        <div><ModifyButton path={["modify_" + props.table]}/>
                            <DeleteButton onClick={() => showModalDelete(element[0], element)}/></div>
                    </div>
                ))}


            </div>
            <div className="containerConfirmDelete">
                <div className="confirmDelete"></div>
            </div>
        </>
    );
}
