            // Ouverture / fermeture du menu mobile
            function openMenu() {
                document.getElementById("menu-mobile").classList.remove("hidden");
                document.getElementById("menu-overlay").classList.remove("hidden");
            }

            function closeMenu() {
                document.getElementById("menu-mobile").classList.add("hidden");
                document.getElementById("menu-overlay").classList.add("hidden");
            }

            // ⭐ DÉLÉGATION : un seul écouteur sur document, qui survit à tout
            document.addEventListener("click", (event) => {
                // Clic sur le burger ☰ → ouvrir
                if (event.target.closest("#menu-burger")) {
                    openMenu();
                }
                // Clic sur le fond noir (en dehors de la fenêtre) → fermer
                if (event.target.closest("#menu-overlay")) {
                    closeMenu();
                }
                // Clic sur la croix ✕ → fermer
                if (event.target.closest("#menu-close")) {
                    closeMenu();
                }
                // Clic sur un lien du menu → fermer
                if (event.target.closest("#menu-mobile a")) {
                    closeMenu();
                }
            });

            // Touche Échap → fermer (accessibilité)
            document.addEventListener("keydown", (event) => {
                if (event.key === "Escape") {
                    closeMenu();
                }
            });
