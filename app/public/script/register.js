import { touchAPI } from "./lib/api.js";
import { showAlert } from "./ui/alerts.js";

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("register-form");

    if (!form) return;

    form.onsubmit = (e) => {
        e.preventDefault();

        let form = new FormData(e.target);

        touchAPI("auth/register",
            {
                "username": form.get("username"),
                "email": form.get("email"),
                "password": form.get("password"),
                "password2": form.get("password2")
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