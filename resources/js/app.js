import "./bootstrap";

import Alpine from "alpinejs";

window.Alpine = Alpine;

Alpine.start();

document.addEventListener("DOMContentLoaded", () => {
    const openBtn = document.getElementById("btnOpenRegisterUser");
    const modal = document.getElementById("modalRegisterUser");
    const closeBtn = document.getElementById("btnCloseRegisterUser");
    const cancelBtn = document.getElementById("btnCancelRegisterUser");
    const form = document.getElementById("formRegisterUser");

    if (openBtn && modal) {
        const open = () => modal.classList.remove("hidden");
        const close = () => modal.classList.add("hidden");

        openBtn.addEventListener("click", open);
        closeBtn && closeBtn.addEventListener("click", close);
        cancelBtn && cancelBtn.addEventListener("click", close);

        // OPTIONAL: submit via fetch agar tidak reload
        form &&
            form.addEventListener("submit", async (e) => {
                if (!form.dataset.ajax) return; // hapus baris ini kalau mau selalu AJAX
                e.preventDefault();
                const fd = new FormData(form);
                const res = await fetch(form.action, {
                    method: "POST",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                    },
                    body: fd,
                });
                const data = await res.json();
                if (data.ok) {
                    alert(
                        "User created: " +
                            data.user.email +
                            " (" +
                            data.user.role +
                            ")"
                    );
                    close();
                    form.reset();
                } else {
                    alert("Failed creating user.");
                }
            });
    }
});
