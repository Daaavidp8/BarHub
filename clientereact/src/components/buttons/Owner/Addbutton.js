import { ReactComponent as Cross } from '../../../images/cross-23.svg';
import "../../../styles/buttons/owner/addButton.css";
import {useNavigate} from "react-router-dom";
import axios from "axios";

export function Addbutton(props) {
    const navigate = useNavigate();

    const goPath = () => {
        window.scrollTo(0, 0);
        navigate(props.path);
    }

    const addTable = async () => {
        const response = await axios.post('http://172.17.0.2:8888/get_sesion', {
            username: localStorage.getItem('username'),
            password: localStorage.getItem('password'),
        });

        if (response.data.roles.includes(2) && response.data.status){
            try {
                await axios.post('http://172.17.0.2:8888/add_table/' + props.restaurant)
                window.location.reload()
            }catch (e){
                console.error("Error al añadir mesa: " + e)
            }
        }else{
            navigate("/")
        }
    }

    return (
        <div className="addButton" onClick={props.path.split("_")[props.path.split("_").length - 1] === "table" ? addTable : goPath}><Cross className="crossAddButton"/>{props.text}</div>
    );
}