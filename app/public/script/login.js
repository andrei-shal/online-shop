import { touchAPI } from "./lib/api.js";
import { showAlert } from "./ui/alerts.js";

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("login-form");

    if (!form) return;

    form.onsubmit = (e) => {
        e.preventDefault();

        let form = new FormData(e.target);

        touchAPI("auth/login",
            {
                "username": form.get("username"),
                "password": form.get("password")
            }
        ).then(res => res.json()).then(data => {
            if (data.success) {
                window.location = "account.php";
            } else {
                showAlert(data.error);
            }
        }).catch(() => {
            showAlert("Ошибка связи с сервером");
        });
    };
});