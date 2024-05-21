import { DefaultTitle } from "../../titles/DefaultTitle";
import { BackButton } from "../../buttons/BackButton";
import { ReactComponent as Mesa } from '../../../images/mesa.svg';
import { ReactComponent as Flecha } from '../../../images/arrow-sm-left-svgrepo-com.svg';
import "../../../styles/main/waiter/showTable.css"
import { useEffect, useState } from "react";
import axios from "axios";
import {useNavigate} from "react-router-dom";

// Muestra una ventana con el código QR y el código de la mesa

export function ShowTable(props) {
    const [qr, setQr] = useState('');
    const [dataloaded, setDataloaded] = useState(false);
    const navigate = useNavigate();
    useEffect(() => {
        const qrgen = async () => {
            try {
                const response = await axios.post('http://172.17.0.2:8888/get_sesion', {
                    username: localStorage.getItem('username'),
                    password: localStorage.getItem('password'),
                });
                if (response.data.roles.includes(3) && response.data.status) {
                    const responseqr = await axios.post('http://172.17.0.2:8888/get_qrTable/' + props.restaurant.id_restaurant, {
                        number_table: props.table,
                    });
                    setQr("data:image/png;base64," + responseqr.data.image)
                }else{
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
