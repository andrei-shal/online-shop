import { initOrdersContent } from "./content/orders-content.js";
import { touchAPI } from "./lib/api.js";
import { showAlert } from "../../script/ui/alerts.js";

document.addEventListener("DOMContentLoaded", () => {
    const roleSelect = document.getElementById('user-role-select');
    if (!roleSelect) return;

    let previousRole = roleSelect.value;

    roleSelect.addEventListener('change', function () {
        const userId = this.dataset.userId;
        const newRole = this.value;

        roleSelect.disabled = true;

        touchAPI("users/update-role", { userId: userId, role: newRole })
            .then(res => res.json()).then(data => {
                if (!data.success) {
                    showAlert(data.error);
                    roleSelect.value = previousRole;
                } else {
                    previousRole = newRole;
                }
            })
            .catch(() => {
                showAlert("Ошибка связи с сервером");
                roleSelect.value = previousRole;
            })
            .finally(() => {
                roleSelect.disabled = false;
            });
    });

    initOrdersContent();
});