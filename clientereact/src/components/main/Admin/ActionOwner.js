import { BackButton } from "../../buttons/BackButton";
import { FormOwners } from "../../form/Admin/FormOwners";
import { ReactComponent as Arrow } from '../../../images/arrow-sm-left-svgrepo-com.svg';


// Componente que contiene la creación y modificación de restaurantes
export function ActionOwner({ action, data }) {
    const h1style = {
        textAlign: 'center'
    };

    return (
        <>
            <h1 style={h1style}>
                {action ? (
                    "Añadir Restaurante"
                ) : (
                    "Modificar Restaurante"
                )}
            </h1>
            <BackButton value={<Arrow className="flechaBack"/>}/>
            <FormOwners action={action} data={data} />
        </>
    );
}
