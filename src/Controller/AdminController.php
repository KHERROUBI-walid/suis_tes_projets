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
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
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

    #[IsGranted('ROLE_ADMIN')]
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


    #[IsGranted('ROLE_ADMIN')]
    #[Route('/manager/{manager_id}/delete', name: 'manager_delete', methods: ['POST'])]
    public function delete(
        int $manager_id, 
        Request $request, 
        EntityManagerInterface $entityManager, 
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        // Vérification du token CSRF
        $token = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete_manager_' . $manager_id, $token))) {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        $manager = $entityManager->getRepository(User::class)->find($manager_id);
        if (!$manager) {
            $this->addFlash('error', 'Le chef de projet n’existe pas.');
        }

        $entityManager->remove($manager);
        $entityManager->flush();

        $this->addFlash('success', 'Chef de projet supprimé avec succès.');

        return $this->redirectToRoute('app_admin');
    }
}
