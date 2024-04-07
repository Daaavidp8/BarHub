import "../../styles/main/admin/admin.css"
import React from 'react';

export function DefaultTitle({logo,text}) {
    return (
        <h1 className="defaultTitle">
            {React.cloneElement(logo, { className: "logoAdmin " + logo.props.className })}
            {text}
        </h1>
    );
}