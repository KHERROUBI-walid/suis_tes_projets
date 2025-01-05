document.addEventListener('DOMContentLoaded', function () {
    // Simule un délai de chargement
    setTimeout(function () {
        document.getElementById('skeleton').style.display = 'none';
        document.getElementById('projects-section').style.display = 'block';
    }, 1500); // Temps simulé en millisecondes
});
