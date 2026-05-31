document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("[data-confirm], .js-confirm-delete").forEach(function (element) {
        element.addEventListener("click", function (event) {
            var message = element.getAttribute("data-confirm") || "Confirmer cette action ?";

            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll(".success, .alert-success").forEach(function (message) {
        setTimeout(function () {
            message.style.display = "none";
        }, 5000);
    });

    document.querySelectorAll("form").forEach(function (form) {
        form.addEventListener("submit", function (event) {
            var invalid = form.querySelector(":invalid");

            if (invalid) {
                invalid.focus();
                form.classList.add("form-has-error");
            }
        });
    });

    var button = document.createElement("button");
    button.id = "backToTop";
    button.type = "button";
    button.textContent = "Haut";
    document.body.appendChild(button);

    window.addEventListener("scroll", function () {
        button.style.display = window.scrollY > 400 ? "block" : "none";
    });

    button.addEventListener("click", function () {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });
});
