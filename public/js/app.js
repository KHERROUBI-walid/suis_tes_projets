document.addEventListener('DOMContentLoaded', function () {
    const header = document.querySelector('.header');
    const changeBackgroundScrollValue = 5;
    const hideHeaderScrollValue = 100; 

    if (header) {
        document.addEventListener('scroll', function () {
            if (window.scrollY > hideHeaderScrollValue) {
                header.style.display = 'none'; 
            } else {
                header.style.display = '';

                if (window.scrollY > changeBackgroundScrollValue) {
                    header.classList.add('scrolled'); 
                } else {
                    header.classList.remove('scrolled');
                }
            }
        });
    } else {
        console.error("L'élément avec la classe 'header' est introuvable.");
    }
});

/**************************** Alerte ******************************* */
document.addEventListener('DOMContentLoaded', function () {
    const boutonsFermer = document.querySelectorAll('[data-fermer-alerte]');

    boutonsFermer.forEach(bouton => {
        bouton.addEventListener('click', function () {
            const alerte = bouton.closest('.alerte');
            if (alerte) {
                alerte.style.display = 'none';
            }
        });
    });
});
