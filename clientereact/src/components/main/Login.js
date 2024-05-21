import { LoginForm } from '../form/LoginForm';
import { ReactComponent as Logo } from '../../images/logosvg.svg';

// Componente que contiene lo necesario para realizar el login
export function Login({ onLogin }) {
    return (
        <div className="container">
            <div>
                <Logo className="logo" />
                <LoginForm onLogin={onLogin} />
            </div>
        </div>
    );
}
