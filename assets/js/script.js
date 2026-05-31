document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("[data-confirm], .js-confirm-delete").forEach(function (element) {
        element.addEventListener("click", function (event) {
            var message = element.getAttribute("data-confirm") || "Confirmer cette action ?";

            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll(".success").forEach(function (message) {
        setTimeout(function () {
            message.style.display = "none";
        }, 5000);
    });
});
