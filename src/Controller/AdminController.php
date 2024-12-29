<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AddManagerType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_login');
        }
    
        // Récupérer les paramètres de tri depuis l'URL
        $sortField = $request->query->get('sort', 'u.nom'); 
        $sortDirection = $request->query->get('direction', 'asc');
    
        // Récupérer les utilisateurs avec pagination
        $query = $userRepository->findUsersByRole('ROLE_MANAGER', $sortField, $sortDirection);
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        // Créer un formulaire vierge pour l'affichage
        $form = $this->createForm(AddManagerType::class);

        return $this->render('manager/manager.html.twig', [
            'pagination' => $pagination,
            'addManagerForm' => $form->createView(),
        ]);
    }


    #[Route('/add-manager', name: 'add-manager')]
    public function addManager(
        Request $request,
        UserRepository $taskRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $hasher,
    ): Response {

        // Gestion de l'ajout d'un chef de projet
        $manager = new User();
        $form = $this->createForm(AddManagerType::class, $manager);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password_gen = bin2hex(random_bytes(6));

            $manager->setRoles(["ROLE_MANAGER"]);
            $manager->setLastLogin(new \DateTime('31-12-1999 00:00'));
            $manager->setPassword($hasher->hashPassword($manager, $password_gen));

            $entityManager->persist($manager);
            $entityManager->flush();

            $this->addFlash('success', 'Chef de projet ajouté avec succès. Son mot de passe est ' .$password_gen);

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('projects/projects.html.twig', [
            'addManagerForm' => $form->createView(),
        ]);
    }
}
