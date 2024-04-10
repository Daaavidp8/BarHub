import "../../../styles/buttons/addOwnerButton.css"
import { ReactComponent as Cross } from '../../../images/cross-23.svg';
import {useNavigate} from "react-router-dom";

export function OpenOwnerButton() {
    const navigate = useNavigate();

    const goPath = () => {
        window.scrollTo(0, 0);
        navigate("/admin/add_owner")
    }

    return (
        <div className="openOwnerButton" onClick={goPath}><Cross className="crossAddOwnerButton"/></div>
    );
}