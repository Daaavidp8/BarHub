import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { DefaultTitle } from '../../titles/DefaultTitle';
import { ReactComponent as Logo } from '../../../images/logosvg.svg';
import { OpenOwnerButton } from '../../buttons/Admin/OpenOwnerButton';
import { ModifyButton } from '../../buttons/ModifyButton';
import { DeleteButton } from '../../buttons/DeleteButton';
import {useNavigate} from "react-router-dom";

export function Admin({logout}) {
    const navigate = useNavigate();
    const [owners, setOwners] = useState([]);

    useEffect(() => {
        const getOwners = async () => {
            try {
                const response = await axios.get('http://172.17.0.2:8888/get_owners');
                setOwners(response.data);
            } catch (error) {
                console.error('Error al obtener propietarios:', error);
            }
        };
        getOwners();
    }, []);

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
            await axios.delete(`http://172.17.0.2:8888/delete_owner/${id}`);
            setOwners(owners.filter(owner => owner.id_restaurant !== id));
        } catch (error) {
            console.error('Error al eliminar propietario:', error);
        }
    };

    return (
        <>
            <DefaultTitle logo={<Logo className="logoAdmin"/>} text="Administración de Propietarios" />
            <div className="ownersContainer">
                <OpenOwnerButton />
                {owners.map((owner) => (
                    <div key={owner.id_restaurant} className="owner">
                        <div className="boximageowner">
                            <img className="logoOwners" src={`../../../images/owners/${owner.name}/img/logo.png`} alt="Imagen No Subida" style={{ width: '80px', height: '84px' }} />
                        </div>
                        <p className="restaurantName">{owner.name}</p>
                        <div className="containerActionButtons">
                            <ModifyButton path={["modify_owner", owner.id_restaurant]} />
                            <DeleteButton onClick={() => showModalDelete(owner.id_restaurant,owner.name)} />
                        </div>
                    </div>
                ))}
            </div>
            <div className="containerConfirmDelete">
                <div className="confirmDelete">

                </div>
            </div>
            <div onClick={() => {
                logout();
                navigate("/");
            }}>Cerrar Sesión
            </div>

        </>
    );
}
