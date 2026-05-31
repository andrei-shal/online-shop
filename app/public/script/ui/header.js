import {touchAPI} from "../lib/api.js";
import {showAlert} from "./alerts.js";

document.addEventListener("DOMContentLoaded", () => {
    const logoutButton = document.getElementById("logout-button");

    if (!logoutButton) return;

    logoutButton.onclick = () => {
        touchAPI("auth/logout", {}).then(() => window.location.href = "index.php").catch(() => {
            showAlert("Ошибка связи с сервером");
        });
    };
});