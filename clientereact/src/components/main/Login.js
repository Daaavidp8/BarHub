import { LoginForm } from '../form/LoginForm';
import { ReactComponent as Logo } from '../../images/logosvg.svg';

export function Login({ onLogin }) {
    return (
        <>
            <div className="container">
                <Logo className="logo" />
                <LoginForm onLogin={onLogin} />
            </div>
        </>
    );
}
