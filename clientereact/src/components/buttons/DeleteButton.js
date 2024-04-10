import React from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faTrash } from '@fortawesome/free-solid-svg-icons';
import "../../styles/buttons/actionButton.css";

export function DeleteButton({ onClick }) {
    return (
        <div className="actionButton deleteButton" onClick={onClick}>
            <FontAwesomeIcon icon={faTrash}/>
        </div>
    );
}
