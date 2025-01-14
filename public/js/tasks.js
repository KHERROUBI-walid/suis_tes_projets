/****************************nouvelle tache *************************/

document.addEventListener('DOMContentLoaded', function () {
const urlParams = new URLSearchParams(window.location.search);
const newTaskId = urlParams.get('target_task');

if (newTaskId) {
    const newTaskElement = document.getElementById(`task-${newTaskId}`);
    if (newTaskElement) {
        newTaskElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        newTaskElement.classList.add('highlight');
        setTimeout(() => newTaskElement.classList.remove('highlight'), 1000);
    }
}
});

/********************  Menu de satut de taches  ******************* */

   function openPopup(taskId) {
    document.querySelectorAll('.popup').forEach(popup => popup.style.display = 'none');
    document.getElementById(`popup-${taskId}`).style.display = 'block';
}

// Fermer les popups en cliquant ailleurs
document.addEventListener('click', function(e) {
    if (!e.target.closest('.task')) {
        document.querySelectorAll('.popup').forEach(popup => popup.style.display = 'none');
    }
});



/***************  drag  ************************ */
document.addEventListener("DOMContentLoaded", function () {
    // Sélectionner toutes les tâches et les jours comme zones de drop
    const tasks = document.querySelectorAll(".task");
    const days = document.querySelectorAll(".day");

    // Permettre le glisser-déposer des tâches
    tasks.forEach(task => {
        task.setAttribute("draggable", true);

        // Début du drag
        task.addEventListener("dragstart", (e) => {
            e.dataTransfer.setData("taskId", task.id);
            e.target.classList.add("en-deplacement");
        });

        // Fin du drag
        task.addEventListener("dragend", (e) => {
            e.target.classList.remove("en-deplacement");
        });
    });

    // Permettre aux jours de recevoir des tâches
    days.forEach(day => {
        day.addEventListener("dragover", (e) => {
            e.preventDefault();
            day.classList.add("survole");
        });

        day.addEventListener("dragleave", (e) => {
            day.classList.remove("survole");
        });

        day.addEventListener("drop", (e) => {
            e.preventDefault();
            day.classList.remove("survole");

            const taskId = e.dataTransfer.getData("taskId");
            const droppedTask = document.getElementById(taskId);

            if (droppedTask) {
                day.appendChild(droppedTask);

                // Envoyer une requête au serveur pour mettre à jour la date
                updateTaskDate(taskId, day);
            }
        });
    });

    /**
     * Mettre à jour la date de la tâche côté serveur
     * @param {string} taskId 
     * @param {HTMLElement} day 
     */
    function updateTaskDate(taskId, day) {
        const newDay = day.querySelector(".date-number").textContent.trim();

        fetch(`/task/drag/${taskId}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({
                newDay: newDay,
            })
        }).then(response => {
            if (response.ok) {
                console.log("Date mise à jour avec succès");
            } else {
                console.error("Erreur lors de la mise à jour");
            }
        }).catch(err => console.error(err));
    }
});

