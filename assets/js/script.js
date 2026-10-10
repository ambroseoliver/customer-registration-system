const form = document.querySelector("#contact-form");
const messageField = document.querySelector("#message");
const messageCount = document.querySelector("#message-count");
const formStatus = document.querySelector("#form-status");

if (form instanceof HTMLFormElement) {
    const fields = Array.from(form.querySelectorAll("input, textarea"));

    function getErrorMessage(field) {
        field.setCustomValidity("");

        if (field.required && field.value.trim() === "") {
            return "Please fill out this field.";
        }

        if (field.id === "fullName" && field.value.trim().length < 2) {
            return "Please enter at least 2 characters.";
        }

        if (field.id === "message" && field.value.trim().length < 10) {
            return "Please enter at least 10 characters.";
        }

        if (field.id === "phone" && field.value.trim() !== "") {
            const phoneDigits = field.value.match(/\d/g) || [];
            if (!/^\+?[0-9().\-\s]+$/.test(field.value) || phoneDigits.length < 7 || phoneDigits.length > 15) {
                return "Enter a phone number with 7 to 15 digits, using only standard phone punctuation.";
            }
        }

        if (!field.validity.valid) {
            if (field.validity.typeMismatch && field.type === "email") {
                return "Please enter a valid email address.";
            }

            if (field.validity.tooShort) {
                return `Please enter at least ${field.minLength} characters.`;
            }

            return field.validationMessage;
        }

        return "";
    }

    function validateField(field) {
        const error = getErrorMessage(field);
        const errorElement = document.querySelector(`#${field.id}-error`);

        field.setAttribute("aria-invalid", String(Boolean(error)));

        if (errorElement) {
            errorElement.textContent = error;
        }

        return error === "";
    }

    function updateMessageCount() {
        if (messageField instanceof HTMLTextAreaElement && messageCount) {
            messageCount.textContent = `${messageField.value.length} / ${messageField.maxLength}`;
        }
    }

    fields.forEach((field) => {
        field.addEventListener("blur", () => validateField(field));
        field.addEventListener("input", () => {
            if (field.hasAttribute("aria-invalid")) {
                validateField(field);
            }
            formStatus.textContent = "";
        });
    });

    messageField?.addEventListener("input", updateMessageCount);
    updateMessageCount();

    form.addEventListener("submit", (event) => {
        const invalidFields = fields.filter((field) => !validateField(field));

        if (invalidFields.length > 0) {
            event.preventDefault();
            formStatus.textContent = "Please check the highlighted fields and try again.";
            invalidFields[0].focus();
        }
    });
}
