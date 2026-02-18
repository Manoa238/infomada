<?php
session_start();
require_once '../../include/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion Formateur</title>
<script src="../../assets/js/Tailwind.js"></script>
<link rel="stylesheet" href="../../assets/css/index.css">
</head>
<body class="h-screen flex items-center justify-center bg-gray-100">

<div class="bg-white py-10 px-8 rounded-lg shadow-xl w-[460px] border bordure-conteneur-animee">
    <h1 class="mb-4 text-2xl font-bold text-primary-local text-center">Connexion Formateur</h1>
    <p class="mb-6 text-sm text-gray-600 text-center">
        Entrez votre adresse e-mail pour vérifier votre compte.
    </p>

    <!-- EMAIL -->
    <div class="space-y-4">
        <input type="email" id="email_formateur" placeholder="Votre adresse e-mail" required
               class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
        <button id="btn_verifier"
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition">
            Vérifier
        </button>
    </div>

    <!-- Infos formateur et cours -->
    <div id="info_formateur" class="hidden mt-6 text-left border-t pt-4"></div>
</div>

<script>
document.getElementById('btn_verifier').addEventListener('click', function () {
    const email = document.getElementById('email_formateur').value.trim();
    if (!email) return alert("Veuillez entrer un email.");

    fetch('verifier_formateur.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'email=' + encodeURIComponent(email)
    })
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('info_formateur');
        if(data.exists){
            let html = `
                <div class="flex items-center gap-3 mb-3">
                    <img src="${data.photo || '../../assets/img/incognito.png'}" class="w-12 h-12 rounded-full border">
                    <div>
                        <p class="font-semibold text-gray-800">${data.nom}</p>
                        <p class="text-sm text-gray-600">${data.email}</p>
                    </div>
                </div>
                <p class="font-medium text-gray-700 mb-2">Cours existants :</p>
                <ul class="list-disc list-inside text-gray-600 text-sm mb-4">
            `;
            data.cours.forEach(c => {
                html += `<li>${c.titre} (${c.niveau})</li>`;
            });
            html += `</ul>
                <button id="btn_envoyer_code"
                        class="w-full bg-primary-local text-white py-2 px-4 rounded-md hover:bg-green-700 transition">
                    Envoyer le code
                </button>`;
            container.innerHTML = html;
            container.classList.remove('hidden');

            // BOUTON "Envoyer le code"
            document.getElementById('btn_envoyer_code').addEventListener('click', function () {
                const btn = this;
                btn.disabled = true;
                btn.textContent = "Envoi en cours...";
                btn.classList.add("opacity-70", "cursor-not-allowed");

                fetch('envoyer_otp.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'email=' + encodeURIComponent(data.email)
                })
                .then(res => res.text())
                .then(response => {
                    if (response.trim() === "ok") {
                        btn.textContent = "Code envoyé ✅";
                        btn.classList.remove("bg-green-600", "hover:bg-green-700");
                        btn.classList.add("bg-green-800");
                        setTimeout(() => {
                            window.location.href = 'formateur_otp.php';
                        }, 1000);
                    } else {
                        btn.textContent = "Réessayer";
                        btn.disabled = false;
                        btn.classList.remove("opacity-70", "cursor-not-allowed");
                        alert("Erreur : " + response);
                    }
                })
                .catch(err => {
                    btn.textContent = "Réessayer";
                    btn.disabled = false;
                    btn.classList.remove("opacity-70", "cursor-not-allowed");
                    console.error(err);
                    alert("Échec lors de l'envoi du code.");
                });
            });
        } else {
            container.innerHTML = `<p class="text-red-600">${data.message}</p>`;
            container.classList.remove('hidden');
        }
    })
    .catch(err => console.error(err));
});
</script>

</body>
</html>
