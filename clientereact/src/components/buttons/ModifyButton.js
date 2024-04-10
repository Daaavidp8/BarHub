import {useNavigate} from "react-router-dom";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faPencil } from '@fortawesome/free-solid-svg-icons';
import "../../styles/buttons/actionButton.css";

export function ModifyButton({path}) {
    const navigate = useNavigate();

    const goPath = () => {
        window.scrollTo(0, 0);
        navigate(path.join('/'));
    }

    return (
        <div className="actionButton modifyButton" onClick={goPath}>
            <FontAwesomeIcon icon={faPencil}/>
        </div>
    );
}
