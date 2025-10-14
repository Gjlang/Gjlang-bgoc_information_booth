import "./bootstrap";

import Alpine from "alpinejs";

window.Alpine = Alpine;

Alpine.start();

// resources/js/app.js (add below your Alpine.start())
window.createTaskModal = (actionUrl = "/items") => ({
    open: false,
    submitting: false,
    errors: {},
    form: {
        date_in: "",
        deadline: "",
        assign_by_id: "",
        assign_to_id: "",
        type_label: "INTERNAL", // or 'CLIENT'
        company_id: "",
        pic_name: "",
        product_id: "",
        status: "",
        remarks: "",
    },
    reset() {
        this.form = {
            date_in: "",
            deadline: "",
            assign_by_id: "",
            assign_to_id: "",
            type_label: "INTERNAL",
            company_id: "",
            pic_name: "",
            product_id: "",
            status: "",
            remarks: "",
        };
        this.errors = {};
    },
    async submit() {
        this.submitting = true;
        this.errors = {};
        try {
            const token = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");
            const res = await fetch(actionUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token,
                    Accept: "application/json",
                },
                body: JSON.stringify(this.form),
            });

            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                this.errors = data?.errors || {};
                throw new Error("Submit failed");
            }

            this.open = false;
            this.reset();
            document.dispatchEvent(new CustomEvent("items:refresh")); // your table can listen for this
        } finally {
            this.submitting = false;
        }
    },
});
