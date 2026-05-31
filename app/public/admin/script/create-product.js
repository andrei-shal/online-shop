import { showAlert } from "../../script/ui/alerts.js";
import { touchAPI } from "./lib/api.js";

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById('product-create-form');
    if (!form) return;

    const fileInput = document.getElementById('product-file-input');
    const imgPreview = document.getElementById('product-image-preview');
    const placeholder = document.getElementById('product-image-placeholder');
    const saveBtn = document.getElementById('save-btn');
    const inputs = form.querySelectorAll('input, textarea');

    let base64Image = null;

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imgPreview.src = e.target.result;
                imgPreview.classList.remove('d-none');
                placeholder.classList.add('d-none');

                base64Image = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        if (!base64Image) {
            showAlert("Пожалуйста, выберите изображение");
            return;
        }

        const formData = new FormData(form);

        setFormDisabled(true);

        const priceInRubles = parseFloat(formData.get('price'));
        const priceInCents = Math.round(priceInRubles * 100);
        formData.set('price', priceInCents);

        formData.set('image', base64Image);

        touchAPI("products/create", Object.fromEntries(formData))
            .then(res => res.json()).then(data => {
                if (!data.success) {
                    showAlert(data.error);
                    setFormDisabled(false);
                } else {
                    window.location.href = '/admin/products.php';
                }
            })
            .catch(() => {
                showAlert("Ошибка связи с сервером");
                setFormDisabled(false);
            })
    });

    function setFormDisabled(status) {
        inputs.forEach(input => input.disabled = status);
        saveBtn.disabled = status;
    }
});