class CustomForm {

    constructor(formElement) {
        this.form = formElement;
        this.init();
    }

    init() {
        this.form.addEventListener('submit', event => {
            event.preventDefault();
            this.submitForm();
        });
    }

    submitForm() {
        const formData = new FormData(this.form);
        formData.append('sessid', BX.message('bitrix_sessid'));

        this.clearErrorMessages();

        BX.ajax.runComponentAction('custom:form_constructor', 'ajax', {
            mode: 'class',
            data: formData,
            method: 'POST',
        }).then(
            response => {
                console.log('Успешный ответ:', response);
                this.handleResponse(response.data);
            },
            response => {
                console.error('Ошибка ответа:', response);
            }
        );
    }

    clearErrorMessages() {
        const fieldAreas = this.form.querySelectorAll('.field-area span');
        fieldAreas.forEach(span => {
            span.textContent = '';
        });

        const successMessage = this.form.querySelector('.success-message');
        if (successMessage) {
            successMessage.classList.remove('active');
        }
    }

    handleResponse(data) {
        const { status, required_fields, check_file_status } = data;

        if (status === false) {
            required_fields.forEach(({ ID, ERROR_TEXT }) => {
                const inputField = this.form.querySelector(`#field-area-${ID} span.error-msg`);
                if (inputField) inputField.textContent = ERROR_TEXT;
            });
        } else if (status === true) {
            const successMessage = this.form.querySelector('.success-message');
            if (successMessage) successMessage.classList.add('active');
        }

        if (check_file_status) {
            for (const { code, text } of check_file_status) {
                if (code) {
                    const inputFields = this.form.querySelectorAll(`input[name="${code}"]`);
                    for (const inputField of inputFields) {
                        const errorSpan = inputField.parentElement.querySelector('.error-msg');
                        if (errorSpan) errorSpan.textContent = text;
                    }
                }
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('.custom-js-form');
    forms.forEach(form => new CustomForm(form));
});