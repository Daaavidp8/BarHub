import '../../styles/buttons/loginButton.css';

export function Loginbutton({ onClick,value }) {
    return (
        <button className="loginbutton" onClick={onClick}>{value}</button>
    );
}