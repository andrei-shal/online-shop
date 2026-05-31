import { showAlert } from "../../script/ui/alerts.js";
import { touchAPI } from "./lib/api.js";

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById('product-form');
    if (!form) return;

    const editBtn = document.getElementById('edit-btn');
    const saveBtn = document.getElementById('save-btn');
    const cancelBtn = document.getElementById('cancel-btn');
    const fileContainer = document.getElementById('file-input-container');
    const fileInput = document.getElementById('product-file-input');
    const imgPreview = document.getElementById('product-image-view');
    const placeholder = document.getElementById('product-image-placeholder');

    const inputs = form.querySelectorAll('input:not([type="hidden"]):not([type="file"]), textarea');

    let initialValues = {};
    let base64Image = null;

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (imgPreview) {
                    imgPreview.src = e.target.result;
                    imgPreview.classList.remove('d-none');
                }
                if (placeholder) {
                    placeholder.classList.add('d-none');
                }
                base64Image = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });

    editBtn.addEventListener('click', () => {
        inputs.forEach(input => {
            initialValues[input.name] = input.value;
            input.disabled = false;
            input.classList.remove('border-0', 'bg-transparent', 'p-0');
        });

        editBtn.classList.add('d-none');
        saveBtn.classList.remove('d-none');
        cancelBtn.classList.remove('d-none');
        if (fileContainer) fileContainer.classList.remove('d-none');
    });

    cancelBtn.addEventListener('click', () => {
        rollbackForm();
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const productId = form.dataset.productId;
        const formData = new FormData(form);

        setFormDisabled(true);

        const priceInRubles = parseFloat(formData.get('price'));
        const priceInCents = Math.round(priceInRubles * 100);
        formData.set('price', priceInCents);

        formData.set('image', base64Image || '');
        formData.set('id', productId);

        touchAPI("products/update", Object.fromEntries(formData))
            .then(res => res.json()).then(data => {
            if (!data.success) {
                showAlert(data.error);
                setFormDisabled(false);
            } else {
                inputs.forEach(input => {
                    initialValues[input.name] = input.value;
                });

                rollbackForm();

                form.classList.add('is-valid');
                setTimeout(() => form.classList.remove('is-valid'), 1000);
            }
        })
            .catch(() => {
                showAlert("Ошибка связи с сервером");
                setFormDisabled(false);
            })
    });

    function rollbackForm() {
        inputs.forEach(input => {
            input.value = initialValues[input.name] || '';
            input.disabled = true;
            input.classList.add('border-0', 'bg-transparent', 'p-0');
        });

        fileInput.value = '';
        base64Image = null;

        if (imgPreview) {
            imgPreview.src = imgPreview.getAttribute('src');
        }

        editBtn.classList.remove('d-none');
        saveBtn.classList.add('d-none');
        cancelBtn.classList.add('d-none');
        if (fileContainer) fileContainer.classList.add('d-none');
    }

    function setFormDisabled(status) {
        inputs.forEach(input => input.disabled = status);
        saveBtn.disabled = status;
        cancelBtn.disabled = status;
    }
});