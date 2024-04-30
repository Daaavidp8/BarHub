import React, { useEffect, useRef, useState } from 'react';
import axios from "axios";
import { useNavigate } from "react-router-dom";
import "../../../styles/forms/formOwners.css";

export function FormOwners(props) {
    const navigate = useNavigate();

    const [name, setName] = useState('');
    const [cif, setCif] = useState('');
    const [email, setEmail] = useState('');
    const [phone, setPhone] = useState('');
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [logoPreview, setLogoPreview] = useState(null);
    const inputFileRef = useRef(null);

    const addOwner = async () => {
        const response = await axios.post('http://172.17.0.2:8888/get_sesion', {
            username: localStorage.getItem('username'),
            password: localStorage.getItem('password'),
        });

        if (response.data.roles == 1){
            if (validarCampos()){

                const formData = new FormData();
                formData.append('owner_logo', inputFileRef.current.files[0]);
                formData.append('owner_name', name);
                formData.append('owner_CIF', cif);
                formData.append('owner_contact_email', email);
                formData.append('owner_contact_phone', phone);

                try {
                    await axios.post('http://172.17.0.2:8888/create_owner', formData);



                    const response = await axios.get('http://172.17.0.2:8888/get_owners');

                    const promises = response.data
                        .filter(owner => owner.name === name)
                        .map(async owner => {
                            await axios.post('http://172.17.0.2:8888/create_worker/' + owner.id_restaurant, {
                                worker_name: owner.name + "_admin",
                                worker_username: username,
                                worker_password: password,
                                worker_roles: "2"
                            });
                        });

                    await Promise.all(promises);


                    navigate("/admin");

                    window.location.reload();
                } catch (error) {
                    console.error('Error al añadir propietario:', error);
                }
            }
        }

    };

    const modifyOwner = async () => {
        const response = await axios.post('http://172.17.0.2:8888/get_sesion', {
            username: localStorage.getItem('username'),
            password: localStorage.getItem('password'),
        });

        if (response.data.roles == 1){
            if (validarCampos()){

                const formData = new FormData();
                formData.append('owner_logo', logoPreview);
                formData.append('owner_name', name);
                formData.append('owner_CIF', cif);
                formData.append('owner_contact_email', email);
                formData.append('owner_contact_phone', phone);
                const config = {
                    headers: {
                        'Content-Type': 'application/json'
                    }
                };

                try {
                    await axios.put(`http://172.17.0.2:8888/update_owner/${props.data.id_restaurant}`, formData, config);
                    navigate("/admin");
                    window.location.reload();
                } catch (error) {
                    console.error('Error al modificar propietario:', error);
                }
            }

        }


    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        switch (name) {
            case 'name':
                setName(value);
                break;
            case 'cif':
                setCif(value);
                break;
            case 'contactEmail':
                setEmail(value);
                break;
            case 'contactPhone':
                setPhone(value);
                break;
            case 'username':
                setUsername(value);
                break;
            case 'password':
                setPassword(value);
                break;
            case 'logo':
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        setLogoPreview(e.target.result);
                    };
                    reader.readAsDataURL(e.target.files[0]);
                }
                break;
            default:
                break;
        }
    };

    useEffect(() => {
        if (!props.action) {
            setName(props.data.name);
            setCif(props.data.cif);
            setEmail(props.data.contactEmail);
            setPhone(props.data.contactPhone);

            // Vista Previa Logo
            axios.get("/images/owners/" + props.data.name + "/img/logo.png", {
                responseType: 'blob'
            }).then(response => {
                const reader = new FileReader();
                reader.onload = () => {
                    setLogoPreview(reader.result);
                };
                reader.readAsDataURL(response.data);
            }).catch(error => {
                if (error.response && error.response.status === 404) {
                    setLogoPreview(null);
                } else {
                    console.error('Error al cargar el logo:', error);
                }
            });


        }
    }, [props.action, props.data]);


    const validarCampos = () => {
        const regexTelefono = /^\d{9}$/;
        const regexCIF = /^[ABCDEFGHJKLMNPQRSUVWabcdefghjklmnpqrsuvw]\d{7}[0-9A-J]$/;
        const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        let errorMessage = "";

        if (!regexTelefono.test(phone)) {
            errorMessage += "- El número de teléfono debe tener 9 dígitos. <br>";
        }

        if (!regexCIF.test(cif)) {
            errorMessage += "- El CIF no es válido. <br>";
        }

        if (!regexEmail.test(email)) {
            errorMessage += "- El email no es válido. ";
        }

        if (errorMessage !== "") {
            document.getElementsByClassName("errorCard")[0].style.display = "block";
            document.getElementsByClassName("errorCard")[0].innerHTML = errorMessage;
            return false;
        }

        return true;


    }

    return (
        <div id="contenedorAllOwners" className="contenedorAllOwners">
            <div className="contenedorFormOwners">
                <form id="formOwners" encType="multipart/form-data">
                    <div>
                        <label htmlFor="logo">Logo del Propietario:</label><br/>
                        <input
                            type="file"
                            id="logo"
                            name="logo"
                            accept="image/png"
                            ref={inputFileRef}
                            onChange={handleChange}
                            required
                        /><br/>
                        <br/>
                        <label htmlFor="name">Nombre del Restaurante:</label><br/>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Introduce el nombre de la empresa..."
                            value={name}
                            onChange={handleChange}
                            required
                        /><br/>
                        {props.action ? (
                            <>
                                <label htmlFor="username">Nombre de Usuario:</label><br/>
                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    placeholder="Introduce el nombre de usuario..."
                                    value={username}
                                    onChange={handleChange}
                                    required
                                />
                                <label htmlFor="password">Contraseña:</label><br/>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Introduce la contraseña del usuario..."
                                    value={password}
                                    onChange={handleChange}
                                    required
                                />
                            </>
                        ) : (
                            <>
                                <label htmlFor="cif">CIF:</label><br/>
                                <input
                                    type="text"
                                    id="cif"
                                    name="cif"
                                    placeholder="Introduce el cif de la empresa..."
                                    value={cif}
                                    onChange={handleChange}
                                    required
                                />
                            </>
                        )}

                    </div>

                    <div>
                        <div className="boximage">
                            {logoPreview ? (
                                <img src={logoPreview} alt="Vista previa del logo"
                                     style={{width: 'auto', height: '84px'}}/>
                            ) : (
                                <div>Imagen No Subida</div>
                            )}
                        </div>
                        <br/>
                        {props.action && (
                            <>
                                <label htmlFor="cif">CIF:</label><br/>
                                <input
                                    type="text"
                                    id="cif"
                                    name="cif"
                                    placeholder="Introduce el cif de la empresa..."
                                    value={cif}
                                    onChange={handleChange}
                                    required
                                />
                            </>

                        )}

                        <label htmlFor="contactEmail">Email de Contacto:</label><br/>
                        <input
                            type="email"
                            id="contactEmail"
                            name="contactEmail"
                            placeholder="Ej: correodecontacto@ayuda.com"
                            value={email}
                            onChange={handleChange}
                            required
                        /><br/>
                        <label htmlFor="contactPhone">Teléfono de Contacto:</label><br/>
                        <input
                            type="tel"
                            id="contactPhone"
                            name="contactPhone"
                            placeholder="Introduce el telefono de la persona de contacto..."
                            value={phone}
                            onChange={handleChange}
                            required
                        />
                    </div>

                </form>
                <div className="errorCard"></div>

                {props.action ? (
                    <button type="button" className="openOwnerButton actionOwnerbutton" onClick={addOwner}>Añadir</button>
                ) : (
                    <button type="button" onClick={modifyOwner} className="actionOwnerbutton saveChangesButton">Guardar Cambios</button>
                )}
            </div>
        </div>
    );
}
