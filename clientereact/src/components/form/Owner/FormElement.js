import {BackButton} from "../../buttons/BackButton";
import { ReactComponent as Arrow } from '../../../images/arrow-sm-left-svgrepo-com.svg';
import "../../../styles/forms/formElement.css"
import {useEffect, useRef, useState} from "react";
import axios from "axios";
import {useNavigate} from "react-router-dom";

export function FormElement(props) {

    const [logoPreview, setLogoPreview] = useState(null);
    const [name, setName] = useState('');
    const [price, setPrice] = useState('');
    const [dataloaded, setdataloaded] = useState(false);
    const inputFileRef = useRef(null);
    const navigate = useNavigate();

    useEffect(() => {
        setdataloaded(false)
        if (!props.action){
            setName(props.data.name)
            axios.get("/images/owners/" + props.restaurant.name + "/img/" + props.element + "/" + props.data.name + ".png", {
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
                    console.error('Error al cargar la imagen:', error);
                }
            });
            if (props.element === "articles"){
                setPrice(props.data.price)
            }

        }
        setdataloaded(true)
    }, [props.action]);

    const handleChange = (e) => {
        const { name, value } = e.target;
        switch (name) {
            case 'name':
                setName(value);
                break;
            case 'price':
                setPrice(value);
                break;
            case 'imagen':
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
    }

    const modifyElement = async () => {
        const response = await axios.post('http://172.17.0.2:8888/get_sesion', {
            username: localStorage.getItem('username'),
            password: localStorage.getItem('password'),
        });

        if (response.data.roles.includes(2)){
            if (validarCampos()){
                const formData = new FormData();
                const config = {
                    headers: {
                        'Content-Type': 'application/json'
                    }
                };

                try {
                    switch (props.element) {
                        case "sections":
                            formData.append('section_img', logoPreview);
                            formData.append('section_name', name);
                            await axios.put(`http://172.17.0.2:8888/update_section/${props.data.id_section}`, formData, config);
                            navigate("/" + props.restaurant.name + "/admin")
                            navigate("/" + props.restaurant.name + "/admin/sections")
                            break;
                        case "articles":
                            formData.append('article_img', logoPreview);
                            formData.append('article_name', name);
                            formData.append('article_price', price);
                            await axios.put('http://172.17.0.2:8888/update_article/' + props.data.id_article, formData, config);
                            const responseSection = await axios.get('http://172.17.0.2:8888/get_section/' + props.data.id_section, formData);
                            navigate("/" + props.restaurant.name + "/admin/")
                            navigate("/" + props.restaurant.name + "/admin/" + responseSection.data[0].name)
                            break;
                        default:
                            break;
                    }
                    window.location.reload();
                } catch (error) {
                    console.error('Error al añadir propietario:', error);
                }
            }
        }
    }

    const addElement = async () => {
        const response = await axios.post('http://172.17.0.2:8888/get_sesion', {
            username: localStorage.getItem('username'),
            password: localStorage.getItem('password'),
        });

        if (response.data.roles.includes(2)){
            if (validarCampos()){
                const formData = new FormData();
                try {
                    switch (props.element) {
                        case "sections":
                            formData.append('section_img', inputFileRef.current.files[0]);
                            formData.append('section_name', name);
                            await axios.post('http://172.17.0.2:8888/create_section/' + props.restaurant.id_restaurant, formData);
                            navigate("/" + props.restaurant.name + "/admin")
                            navigate("/" + props.restaurant.name + "/admin/sections")
                            break;
                        case "articles":
                            formData.append('article_img', inputFileRef.current.files[0]);
                            formData.append('article_name', name);
                            formData.append('article_price', price);
                            await axios.post('http://172.17.0.2:8888/create_article/' + props.data.id_section, formData);
                            navigate("/" + props.restaurant.name + "/admin/sections")
                            navigate("/" + props.restaurant.name + "/admin/" + props.data.name)
                            break;
                        default:
                            break;
                    }
                    window.location.reload();
                } catch (error) {
                    console.error('Error al añadir propietario:', error);
                }
            }
        }

    };

    const validarCampos = () => {
        const nameInput = document.getElementById('name');

        let errorMessage = "";

        if (!nameInput.value.trim()) {
            errorMessage += "- Debes ingresar un nombre. <br>";
        }

        if (!logoPreview || logoPreview.length === 0) {
            errorMessage += "- Debes seleccionar una imagen. <br>";
        }

        if (props.element === "articles") {
            const priceInput = document.getElementById('price');

            if (!isNaN(Number(priceInput)) || priceInput.value.length === 0){
                errorMessage += "- Debes ingresar un numero en el precio. <br>";
            }
        }

        if (errorMessage !== "") {
            document.getElementsByClassName("errorCard")[0].style.display = "block";
            document.getElementsByClassName("errorCard")[0].innerHTML = errorMessage;
            return false;
        }

        return true;
    }




    return (
        <>
            {dataloaded && (<>
                <h1 className="titleForm">
                    <BackButton value={<Arrow className="flechaVolver"/>}></BackButton>
                    {props.title}
                </h1>
                <form className="formelements">
                    <div className="containerImage">
                        <div className="custom-file-upload">
                            <label htmlFor="imagen" className="labelFormElements">Imagen:</label>
                            <input
                                className="inputElement"
                                type="file"
                                id="imagen"
                                name="imagen"
                                accept="image/png"
                                ref={inputFileRef}
                                onChange={handleChange}
                                required
                            />
                        </div>
                        <div className="containerPreview">
                            {logoPreview ? (
                                <img src={logoPreview} alt="Vista previa del logo"
                                     style={{maxWidth: '100px', maxHeight: '100px'}}/>
                            ) : (
                                <div>Imagen No Subida</div>
                            )}
                        </div>
                    </div>
                    <div className="containerName">
                        <label htmlFor="name" className="labelFormElements">Nombre
                            de {props.title.split(" ")[1]}:</label><br/>
                        <input
                            className="inputElement"
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Introduce el nombre..."
                            value={name}
                            onChange={handleChange}
                            required
                        />
                    </div>
                    {props.element === "articles" ? (
                        <div className="containerPrice">
                            <label htmlFor="name" className="labelFormElements">Precio del Articulo:</label><br/>
                            <input
                                className="inputElement"
                                type="text"
                                id="price"
                                name="price"
                                placeholder="Introduce el precio..."
                                value={price}
                                onChange={handleChange}
                                required
                            />
                        </div>
                    ) : null}
                </form>
                <div className="errorCard errorFormElement"></div>
                <div className="containerFormElementActionButton">
                    {props.action ? (
                        <button type="button"
                                className="openOwnerButton actionOwnerbutton buttonFormElement"
                                onClick={addElement}
                        >
                            Añadir
                        </button>
                    ) : (
                        <button type="button" className="actionOwnerbutton saveChangesButton buttonFormElement"
                                onClick={modifyElement}
                        >Guardar
                            Cambios</button>
                    )}
                </div>

            </>)}

        </>
    );
}