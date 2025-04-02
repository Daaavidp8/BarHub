import React, { useState } from 'react';
import { DefaultTitle } from '../../titles/DefaultTitle';
import { ReactComponent as Logo } from '../../../images/logosvg.svg';
import { OpenOwnerButton } from '../../buttons/Admin/OpenOwnerButton';
import { ModifyButton } from '../../buttons/ModifyButton';
import { DeleteButton } from '../../buttons/DeleteButton';
import { useLocation, useNavigate } from "react-router-dom";
import axiosInstance from '../../../utils/axiosConfig';
import { ENDPOINTS } from '../../../utils/constants';
import "../../../styles/main/admin/admin.css"



// Componente que muestra al administrador los restaurantes existentes
export function Admin(props) {
    const navigate = useNavigate();
    const [owners, setOwners] = useState(props.owners);
    const location = useLocation();
    const currentPath = location.pathname;
    const basePath = currentPath.substring(0, currentPath.lastIndexOf('/'));

    const showModalDelete = (id,name) => {
        try {
            let div = document.getElementsByClassName('confirmDelete')[0];

            var containerActions = document.createElement("div");
            containerActions.classList.add("containerConfirmButtons","containerActionButtons");

            var cancelButton = document.createElement("div");
            cancelButton.classList.add("openOwnerButton","confirmButtons");
            cancelButton.textContent = "No";
            cancelButton.onclick = function() {
                document.getElementsByClassName('containerConfirmDelete')[0].style.display = 'none';
            };

            var confirmButton = document.createElement("div");
            confirmButton.classList.add("confirmButtons","deleteConfirmButton");
            confirmButton.textContent = "Sí";
            confirmButton.onclick = function() {
                document.getElementsByClassName('containerConfirmDelete')[0].style.display = 'none';
                handleDeleteOwner(id).then();
            };

            var messageParagraph = document.createElement("p");
            messageParagraph.classList.add("messageConfirmDelete")
            messageParagraph.innerHTML = "¿Desea eliminar el propietario: <b>" + name + "</b>?";

            div.innerHTML = "";

            div.appendChild(messageParagraph);
            containerActions.appendChild(cancelButton);
            containerActions.appendChild(confirmButton);
            div.appendChild(containerActions);

            var containerConfirmDelete = document.getElementsByClassName('containerConfirmDelete')[0];
            containerConfirmDelete.style.display = 'flex';
            containerConfirmDelete.onclick = function() {
                this.style.display = "none";
            };

            containerConfirmDelete.style.position = "fixed";
            containerConfirmDelete.style.top = "50%";
            containerConfirmDelete.style.left = "50%";
            containerConfirmDelete.style.transform = "translate(-50%, -50%)";
            containerConfirmDelete.style.backgroundColor = "#ffffff";
            containerConfirmDelete.style.border = "1px solid #ccc";
            containerConfirmDelete.style.padding = "20px";
            containerConfirmDelete.style.borderRadius = "5px";


        } catch (error) {
            console.error('Error al eliminar propietario:', error);
        }
    };

    const handleDeleteOwner = async (id) => {
        try {
            await axiosInstance.delete(`${ENDPOINTS.DELETE_OWNER}/${id}`);
            setOwners(owners.filter(owner => owner.id_restaurant !== id));
        } catch (error) {
            console.error('Error al eliminar propietario:', error);
        }
    };



    return (
        <>
            <DefaultTitle logo={<Logo className="logoAdmin" />} text={<p className="tituloAdmin">Administración de Propietarios</p>}/>
            <div className="ownersContainer">
                <OpenOwnerButton />
                {owners.map((owner) => (
                    <div className="owner">
                        <div className="boximageowner">
                            <img
                                src={`/images/owners/${owner.name}/img/logo.png`}
                                alt={`Imagen no subida`}
                                style={{ width: '100%', height: '100%' }}
                            />
                        </div>
                        <p className="restaurantName">{owner.name}</p>
                        <div className="containerActionButtons">
                            <ModifyButton path={basePath + "modify_owner/" + owner.id_restaurant} />
                            <DeleteButton onClick={() => showModalDelete(owner.id_restaurant, owner.name)} />
                        </div>
                    </div>
                ))}
            </div>
            <div className="containerConfirmDelete">
                <div className="confirmDelete"></div>
            </div>
            <div className="logout" onClick={() => {
                props.logout();
                navigate("/login");
                window.location.reload();
            }}>Cerrar Sesión
            </div>
        </>
    );
}
