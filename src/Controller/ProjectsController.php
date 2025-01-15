<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Project;
use App\Form\AddProjectType;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectsController extends AbstractController
{
    #[Route('/projects/{statut_envoye}', name: 'app_projects', methods: ['GET'], defaults: ['statut_envoye' => null])]
    public function displayProjects(
        ?string $statut_envoye, 
        ProjectRepository $projectRepository,
        TaskRepository $taskRepository,
        EntityManagerInterface $entityManager
    ): Response {
    
        // Récupérer les projets avec ou sans filtre de statut
        $projects = $projectRepository->findByStatusAndDateFin($statut_envoye);
    
        // Mise à jour des statuts des projets
        foreach ($projects as $project) {
            $this->updateProjectStatus($project, $taskRepository, $entityManager);
        }
    
        return $this->render('projects/projects.html.twig', [
            'projects' => $projects,
            'is_manager' => false,
        ]);
    }
    

    #[Route('/manager/projects/{statut_envoye}', name: 'app_projects_manager', methods: ['GET', 'POST'], defaults: ['statut_envoye' => null])]
    public function managerProjects(
        ?string $statut_envoye,
        Request $request,
        ProjectRepository $projectRepository,
        TaskRepository $taskRepository,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User|null $user */
        $user = $this->getUser();

        $email = $user->getEmail();
    
        // Vérification des droits d'accès
        if (!$this->isGranted('ROLE_MANAGER')) {
            throw $this->createAccessDeniedException('Accès réservé aux gestionnaires.');
        }
    
        // Récupérer les projets selon le statut
        $projects = $projectRepository->findByManagerAndStatus($user->getId(), $statut_envoye);
    
        foreach ($projects as $project) {
            $this->updateProjectStatus($project, $taskRepository, $entityManager);
        }
    
        // Gestion de l'ajout d'un projet
        $project = new Project();
        $form = $this->createForm(AddProjectType::class, $project);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $project->setUser($user);
            $project->setStatutProjet('pas_commence');
            $entityManager->persist($project);
            $entityManager->flush();
    
            $this->addFlash('success', 'Projet ajouté avec succès.');
    
            return $this->redirectToRoute('app_projects_manager');
        }
    
        return $this->render('projects/projects.html.twig', [
            'projects' => $projects,
            'addProjectForm' => $form->createView(),
            'is_manager' => true,
            'email' => $email,
        ]);
    }
    

    private function updateProjectStatus(Project $project, TaskRepository $taskRepository, EntityManagerInterface $entityManager): void
    {
        $now = new \DateTime();
        $dateDebut = $project->getDateDebut();
        $dateFin = $project->getDateFin();
        $tasks = $taskRepository->findBy(['project' => $project]);
    
        // Statut par défaut si aucune tâche n'existe
        if (count($tasks) === 0) {
            $project->setStatutProjet('pas_commence');
        } 
        else {
            $allTasksTerminated = true;
            $atLeastOneInProgress = false;
    
            foreach ($tasks as $task) {
                $taskStatus = $task->getStatutTask();
    
                if ($taskStatus !== 'termine') {
                    $allTasksTerminated = false;
                }
    
                if ($taskStatus === 'en_cours' || $taskStatus === 'termine') {
                    $atLeastOneInProgress = true;
                }
            }
    
            // Détermination du statut prioritaire
            if ($now > $dateFin && ! $allTasksTerminated) {
                $project->setStatutProjet('en_retard');
            } elseif ($allTasksTerminated) {
                $project->setStatutProjet('termine');
            } elseif ($atLeastOneInProgress) {
                $project->setStatutProjet('en_cours');
            } else {
                $project->setStatutProjet('pas_commence');
            }
        }

        $entityManager->persist($project);
        $entityManager->flush();
    }
}

