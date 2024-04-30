import React, {useEffect, useState} from 'react';
import axios from 'axios';
import { DefaultTitle } from '../../titles/DefaultTitle';
import { ReactComponent as Flecha } from '../../../images/arrow-sm-left-svgrepo-com.svg';
import {useLocation, useNavigate} from "react-router-dom";
import {BackButton} from "../../buttons/BackButton";
import "../../../styles/main/owners/SectionAdmin.css"
import {Addbutton} from "../../buttons/Owner/Addbutton";
import {ModifyButton} from "../../buttons/ModifyButton";
import {DeleteButton} from "../../buttons/DeleteButton";

export function SectionAdminOwner(props) {
    const navigate = useNavigate();
    const location = useLocation();
    const currentPath = location.pathname;
    const basePath = currentPath.substring(0, currentPath.lastIndexOf('/'));
    const [dataLoaded,setDataLoaded] = useState(false);
    const [elements,setelements] = useState([]);

    useEffect(() => {
        console.log(props)
        const fetchData = async () => {
            setDataLoaded(false);
            setelements([]);

            try {
                switch (props.table) {
                    case "sections":
                        const sectionsResponse = await axios.get('http://172.17.0.2:8888/get_sections/' + props.restaurant.id_restaurant);
                        setelements(sectionsResponse.data);
                        break;
                    case "articles":
                        const articlesResponse = await axios.get('http://172.17.0.2:8888/get_articles/' + props.data.id_section);
                        setelements(articlesResponse.data);
                        break;
                    case "workers":
                        const workersResponse = await axios.get('http://172.17.0.2:8888/get_workers/' + props.restaurant.id_restaurant);
                        setelements(workersResponse.data);
                        break;
                    default:
                        break;
                }

                setDataLoaded(true);
            } catch (error) {
                console.error("Error fetching data:", error);
            }
        };

        fetchData();
    }, [props.table]);




    const showModalDelete = (element) => {
        try {
            window.scrollTo(0, 0);
            let id = null;

            switch (props.table) {
                case "sections":
                    id = element.id_section;
                    break;
                case "articles":
                    id = element.id_article;
                    break;
                case "workers":
                    id = element.id_worker;
                    break;
                default:
                    break;

            }



            let containerConfirmDelete = document.getElementsByClassName('containerConfirmDelete')[0];


            // Crear elementos

            let container = document.createElement("div");
            container.style.marginTop = "150px";

            let messageParagraph = document.createElement("p");
            messageParagraph.classList.add("messageConfirmDelete", "textConfirmdeletesection");
            messageParagraph.innerHTML = `¿Desea eliminar: <b>${element.name}</b>?`;

            let containerButtons = document.createElement("div");
            containerButtons.classList.add("containerConfirmButtons");

            let cancelButton = document.createElement("div");
            cancelButton.classList.add("confirmButtons", "openOwnerButton");
            cancelButton.textContent = "No";
            cancelButton.onclick = function () {
                containerConfirmDelete.style.display = 'none';
            };

            let confirmButton = document.createElement("div");
            confirmButton.classList.add("confirmButtons", "deleteConfirmButton");
            confirmButton.textContent = "Sí";
            confirmButton.onclick = function () {
                containerConfirmDelete.style.display = 'none';
                deleteElement(id).then();
            };

            // Limpiar contenido anterior y añadir elementos
            containerConfirmDelete.innerHTML = "";
            containerButtons.appendChild(cancelButton);
            containerButtons.appendChild(confirmButton);
            container.appendChild(messageParagraph);
            container.appendChild(containerButtons);
            containerConfirmDelete.appendChild(container);


            // Mostrar modal
            containerConfirmDelete.style.display = 'block';


        } catch (error) {
            console.error('Error al eliminar elemento:', error);
        }
    };


    const deleteElement = async (id) => {
        switch (props.table) {
            case "sections":
                await axios.delete('http://172.17.0.2:8888/delete_section/' + id);
                setelements(elements.filter(elements => elements.id_section !== id));
                break;
            case "articles":
                await axios.delete('http://172.17.0.2:8888/delete_article/' + id);
                setelements(elements.filter(elements => elements.id_article !== id));
                break;
            case "workers":
                await axios.delete('http://172.17.0.2:8888/delete_worker/' + id);
                setelements(elements.filter(elements => elements.id_worker !== id));
                break;
            default:
                break;

        }
        document.getElementsByClassName('containerConfirmDelete')[0].style.display = "none";
    };

    return (
                <>
                    {dataLoaded && (<>
                        <DefaultTitle
                            text={
                                <>
                                    {props.table === "articles" ? (
                                        <img src={`/images/owners/${props.restaurant.name}/img/sections/${props.title}.png`} alt={`Sin imagen`} style={{width: "40px",height: "40px", marginRight: "10px"}} />
                                    ) : null}
                                    <p>Administración de {props.title}</p>
                                </>
                            }
                            logo={<BackButton value={<Flecha style={{width: "50px", height: "auto", left: "10px"}}/>}/>}
                            className="titleOwnerAdmin"
                        />

                        {props.table === "articles" ? (
                            <Addbutton text={'Añadir ' + props.title.charAt(0).toUpperCase() + props.title.slice(1)} path={basePath + "/add_" +  props.data.name} />
                        ):(
                            <Addbutton text={'Añadir ' +  props.title.charAt(0).toUpperCase() + props.title.slice(1)} path={basePath + "/add_" +  props.table.substring(0, props.table.length - 1)} />
                        )}


                        <div className='containerElements'>
                            {elements.length > 0 ? (
                                <>
                                    {elements.map((element,index) => (
                                        <div key={index}>
                                            <div style={{width: "70%", padding: "10px"}}
                                                 onClick={() => props.table === "sections" && navigate(`/${props.restaurant.name}/admin/${element.name}`)}>
                                                {props.table === "articles" || props.table === "articles" ? <img
                                                    src={`/images/owners/${props.restaurant.name}/img/${props.table}/${element.name}.png`}
                                                    alt={element.name}
                                                    style={{height: "50px", width: "50px"}}
                                                /> : null}
                                                <p className="textElement"
                                                   style={{width: `${props.table === "articles" ? 100 : 170}px`}}>
                                                    {element.name.charAt(0).toUpperCase() + element.name.slice(1)}
                                                </p>
                                            </div>
                                            {element.price && (
                                                <div className="priceElement">
                                                    {element.price}€
                                                </div>
                                            )}
                                            <div className="containerActionElementButtons">
                                                <ModifyButton path={`${basePath}/modify_${props.table.substring(0, props.table.length - 1)}/${element.name}`}/>
                                                <DeleteButton onClick={() => {
                                                    showModalDelete(element)
                                                }}/>
                                            </div>
                                        </div>
                                    ))}
                                </>
                            ) : (
                                <div className="notElementsFound">Vaya! No hay elementos en esta sección</div>
                            )}
                        </div>
                        <div className="containerConfirmDelete deleteAdminOwner">
                            <div className="confirmDelete"></div>
                        </div>
                    </>)}

                </>
    );
}
