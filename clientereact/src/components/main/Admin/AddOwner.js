import {BackButton} from "../../buttons/BackButton";
import {FormOwners} from "../../form/Admin/FormOwners";
import { ReactComponent as Arrow } from '../../../images/arrow-sm-left-svgrepo-com.svg';

export function AddOwner() {
    const h1style = {
        textAlign: 'center'
    };

    return (
        <>
            <h1 style={h1style}>Añadir Propietario</h1>
            <BackButton value={<Arrow className="flechaBack"/>}/>
            <FormOwners action={true}/>
        </>
    );
}