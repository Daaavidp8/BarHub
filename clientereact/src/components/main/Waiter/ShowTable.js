import { DefaultTitle } from "../../titles/DefaultTitle";
import { BackButton } from "../../buttons/BackButton";
import { ReactComponent as Mesa } from '../../../images/mesa.svg';
import { ReactComponent as Flecha } from '../../../images/arrow-sm-left-svgrepo-com.svg';
import "../../../styles/main/waiter/showTable.css"
import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import axiosInstance from '../../../utils/axiosConfig';
import { ENDPOINTS, STORAGE_KEYS, ROLES } from '../../../utils/constants';

// Muestra una ventana con el código QR y el código de la mesa

export function ShowTable(props) {
    const [qr, setQr] = useState('');
    const [dataloaded, setDataloaded] = useState(false);
    const navigate = useNavigate();
    useEffect(() => {
        const qrgen = async () => {
            try {
                const response = await axiosInstance.post(ENDPOINTS.LOGIN, {
                    username: localStorage.getItem(STORAGE_KEYS.USERNAME),
                    password: localStorage.getItem(STORAGE_KEYS.PASSWORD),
                });
                if (response.data.roles.includes(ROLES.WAITER) && response.data.status) {
                    const responseqr = await axiosInstance.post(`${ENDPOINTS.GET_QR_TABLE}/${props.restaurant.id_restaurant}`, {
                        number_table: props.table,
                    });
                    setQr("data:image/png;base64," + responseqr.data.image)
                } else {
                    navigate("/")
                }
                setDataloaded(true)
            } catch (e) {
                console.error(e)
            }
        }
        qrgen();
    }, []);
    return (
        <div>
            {dataloaded ? (
                <div id="conatinerMesa">
                    <DefaultTitle logo={<Mesa className="mesa"/>}
                                  text={<p className="tituloMesa">Mesa {props.table}</p>}></DefaultTitle>
                    <BackButton value={<Flecha style={{width: "70px", height: "auto"}} className="flechaMesa"/>}/>
                    <div className="containerQR">
                        <img src={qr} alt="Código QR" className="qrCode"/>
                    </div>
                    <div className="logout resetMesa" onClick={() => {
                       // Todo llamar a la funcion de hacer que pase el pedido a la tabla pedidos y se resetee el codigo (servidor)
                    }}>Sacar Cuenta
                    </div>
                </div>
            ) : null}
        </div>
    );
}
