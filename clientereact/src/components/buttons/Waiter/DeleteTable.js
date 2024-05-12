import "../../../styles/buttons/waiter/deleteTable.css";
import axios from "axios";

export function DeleteTable(props) {
    const deleteTable = async () => {
        try {
            await axios.delete('http://172.17.0.2:8888/delete_table/' + props.restaurant)
            window.location.reload();
        }catch (e){
            console.error(e)
        }
    }

    return (
        <div className="deleteTable" onClick={deleteTable}>{props.text}</div>
    );
}