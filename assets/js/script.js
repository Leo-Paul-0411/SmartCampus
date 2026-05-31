document.addEventListener("DOMContentLoaded", function () {
    // Confirmation avant les actions sensibles : suppression, refus, desinscription.
    document.querySelectorAll("[data-confirm], .js-confirm-delete").forEach(function (element) {
        element.addEventListener("click", function (event) {
            var message = element.getAttribute("data-confirm") || "Confirmer cette action ?";

            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });

    // Les messages de succes disparaissent automatiquement pour garder l'interface lisible.
    document.querySelectorAll(".success, .alert-success").forEach(function (message) {
        setTimeout(function () {
            message.style.display = "none";
        }, 5000);
    });

    // Aide simple : placer le focus sur le premier champ HTML invalide.
    document.querySelectorAll("form").forEach(function (form) {
        form.addEventListener("submit", function (event) {
            var invalid = form.querySelector(":invalid");

            if (invalid) {
                invalid.focus();
                form.classList.add("form-has-error");
            }
        });
    });

    // Bouton de retour en haut de page, utile sur les tableaux longs.
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
