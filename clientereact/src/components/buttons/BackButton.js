import {useNavigate} from "react-router-dom";

export function BackButton({value}) {
    const navigate = useNavigate();

    const goBack = () => {
        navigate(-1)
    }

    return (
        <div className="" onClick={goBack}>{value}</div>
    );
}