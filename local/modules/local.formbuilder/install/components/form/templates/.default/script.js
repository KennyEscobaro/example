class Form {
    #config = undefined;

    #classes = {
        orderServiceBtn: 'order-service-btn',
        modalOverlay: 'modal-overlay',
        modalForm: 'modal-form',
        modalFormErrorMessage: 'error-message',
        modalFormGroup: 'form-group',
        modalCloseBtn: 'close-modal-btn',
        modalSuccessCloseBtn: 'modal-success-close-btn',
        modalCancelBtn: 'btn-secondary',
        modalSubmitBtn: 'btn-primary'
    }

    #nodes = {}

    constructor(config)
    {
        this.#config = config;
    }

    init()
    {
        this.#nodes.orderServiceBtn = document.querySelector(`.${this.#classes.orderServiceBtn}`);

        this.#addEventHandler();
    }

    #addEventHandler()
    {
        this.#nodes.orderServiceBtn.addEventListener('click', async () => {
            BX.Local.Global.Preloader.show();
            await this.#renderModal();
            BX.Local.Global.Preloader.close();
        });
    }

    #addModalEventHandler(modal)
    {
        const modalOverlay = modal.querySelector(`.${this.#classes.modalOverlay}`);
        const closeBtn = modal.querySelector(`.${this.#classes.modalCloseBtn}`);
        const cancelBtn = modal.querySelector(`.${this.#classes.modalCancelBtn}`);
        const submitBtn = modal.querySelector(`.${this.#classes.modalSubmitBtn}`);

        closeBtn.addEventListener('click', () => {
           this.#closeModal(modal);
        });

        cancelBtn.addEventListener('click', () => {
            this.#closeModal(modal);
        });

        modalOverlay.addEventListener('click', () => {
            this.#closeModal(modal);
        });

        submitBtn.addEventListener('click', async () => {
            BX.Local.Global.Preloader.show();
            await this.#handleSubmitModal(modal);
            BX.Local.Global.Preloader.close();
        });
    }

    #addModalSuccessEventHandler(modal)
    {
        const modalOverlay = modal.querySelector(`.${this.#classes.modalOverlay}`);
        const closeButtons = modal.querySelectorAll(`.${this.#classes.modalSuccessCloseBtn}`);

        closeButtons.forEach((closeBtn) => {
            closeBtn.addEventListener('click', () => {
                this.#closeModal(modal);
            });
        });

        modalOverlay.addEventListener('click', () => {
            this.#closeModal(modal);
        });
    }

    #closeModal(modal)
    {
        modal.remove();
    }

    async #handleSubmitModal(modal)
    {
        const form = modal.querySelector(`.${this.#classes.modalForm}`);

        if (!this.#validateForm(form)) {
            return;
        }

        const formData = this.#collectFormData(form);

        await this.#sendFormData(modal, form, formData);
    }

    async #sendFormData(modal, form, formData)
    {
        try {
            const response = await BX.Local.Global.SenderToBitrixService.request({
                componentName: this.#config.componentName,
                signedParameters: this.#config.signedParameters,
                action: this.#config.actions?.['createResult'],
                data: {fields: formData}
            });

            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = response.data.html;

            const modalSuccess = tempContainer.firstElementChild;

            this.#closeModal(modal);
            document.body.appendChild(modalSuccess);

            this.#addModalSuccessEventHandler(modalSuccess);
        } catch (error) {
            const errorMessage = document.createElement('span');
            errorMessage.classList.add('f-14', 'error-message');
            errorMessage.textContent = error.data.result;

            error.data.invalidFields.forEach(fieldName => {
                const fields = form.querySelectorAll(`[name="${fieldName}"]`);
                fields.forEach(field => {
                    field.classList.add('error');
                });
            });

            form.appendChild(errorMessage);
        }
    }

    #collectFormData(form)
    {
        const data = {};
        const fields = form.querySelectorAll('input, textarea, select, [type="file"]');

        fields.forEach(field => {
            const name = field.name;
            if (!name) {
                return;
            }

            if (field.type === 'checkbox' || field.type === 'radio') {
                if (!field.checked) {
                    return;
                }

                if (field.type === 'checkbox') {
                    const checkboxesWithSameName = document.querySelectorAll(`input[type="checkbox"][name="${field.name}"]`);

                    if (checkboxesWithSameName.length > 1) {
                        data[name] = data[name] || [];
                        data[name].push(field.value);
                    } else {
                        data[name] = field.value;
                    }
                } else {
                    data[name] = field.value;
                }

                return;
            }

            if (field.tagName === 'SELECT' && field.multiple) {
                data[name] = Array.from(field.selectedOptions).map(opt => opt.value);
                return;
            }

            if (field.type === 'file') {
                if (field.files.length > 0) {
                    const files = Array.from(field.files).map(file => ({
                        name: file.name,
                        type: file.type,
                        size: file.size,
                        tmp_name: file._bxTempPath,
                    }));

                    data[name] = field.multiple ? files : files[0];
                }

                return;
            }

            data[name] = field.value;
        });

        return data;
    }

    #validateForm(form)
    {
        let isValid = true;

        this.#resetErrors(form);

        const requiredFields = form.querySelectorAll('[required]');

        requiredFields.forEach(field => {
            let isFieldValid = true;

            switch(field.type) {
                case 'checkbox':
                case 'radio':
                    const radioGroup = form.querySelectorAll(`[name="${field.name}"]`);
                    isFieldValid = Array.from(radioGroup).some(radio => radio.checked);
                    break;
                default:
                    isFieldValid = field.value.trim() !== '';
            }

            if (!isFieldValid) {
                field.classList.add('error');
                isValid = false;
            }
        });

        const errorMessage = document.createElement('span');
        errorMessage.classList.add('f-14', 'error-message');
        errorMessage.textContent = 'Заполните обязательные поля';

        form.appendChild(errorMessage);

        return isValid;
    }

    #resetErrors(form)
    {
        const errorElements = form.querySelectorAll(`.${this.#classes.modalFormGroup} .error`);

        errorElements.forEach(group => {
            group.classList.remove('error');
        });

        const errorMessage = form.querySelector(`.${this.#classes.modalFormErrorMessage}`);

        if (errorMessage) {
            errorMessage.remove();
        }
    }

    async #renderModal()
    {
        try {
            const response = await BX.Local.Global.SenderToBitrixService.request({
                componentName: this.#config.componentName,
                signedParameters: this.#config.signedParameters,
                action: this.#config.actions?.['getModal']
            });

            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = response.data.html;

            const modal = tempContainer.firstElementChild;
            document.body.appendChild(modal);

            const js = response.data.assets.js.length ? response.data.assets.js : [];
            const css = response.data.assets.css.length ? response.data.assets.css : [];
            const assets = js.concat(css);

            if (assets.length) {
                BX.load(assets, () => {
                    BX.ajax.processScripts(BX.processHTML(response.data.html).SCRIPT);
                });
            }

            this.#addModalEventHandler(modal);
        } catch (error) {}
    }
}

BX.Form = Form;