import {DefaultTitle} from "../../titles/DefaultTitle";
import { ReactComponent as Logo } from '../../../images/logosvg.svg';
import {OpenOwnerButton} from "../../buttons/Admin/OpenOwnerButton";
import {useEffect, useState} from "react";
import axios from "axios";

export function Admin() {
    const [owners, setOwners] = useState([]);

    useEffect(() => {
        const getOwners = async () => {
            try {
                const response = await axios.get('http://172.17.0.2:8888/get_owners');
                setOwners(response.data);
            } catch (error) {
                console.error('Error al obtener propietarios:', error);
            }
        }
        getOwners().then();
    }, []);

    return (
        <>
            <DefaultTitle logo={<Logo className="logoAdmin" />} text={"Administración de Propietarios"}></DefaultTitle>
            <div className="ownersContainer">
                <OpenOwnerButton />
                {owners.map((owner) => (
                    <div key={owner.id_restaurant} className="owner">{ owner.name }</div>
                ))}
            </div>
        </>
    );
}