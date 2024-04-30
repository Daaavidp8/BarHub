import { ReactComponent as Cross } from '../../../images/cross-23.svg';
import "../../../styles/buttons/owner/addButton.css";
import {useNavigate} from "react-router-dom";

export function Addbutton(props) {
    const navigate = useNavigate();

    const goPath = () => {
        window.scrollTo(0, 0);
        navigate(props.path);
    }

    return (
        <div className="addButton" onClick={goPath}><Cross className="crossAddButton"/>{props.text}</div>
    );
}