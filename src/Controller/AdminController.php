<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\InterestType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'app_admin_')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function dashboard(UserRepository $userRepository):Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'nbTotalUsers' => $userRepository->countAll(),
            'avgAgeUsers' => $userRepository->averageAge(),
            'nbUsersMiddleAge' => $userRepository->nbBetweenUsers()
        ]);
    }

    #[Route('/users/{page}', name: 'users_list', requirements: ['page' => '\d+'])]
    public function index(UserRepository $userRepository, PaginatorInterface $paginator, Request $request, int $page = 1): Response
    {
        $data = $userRepository->findBy([], ['lastname' => 'ASC']);

        $users = $paginator->paginate(
            $data,
            $request->query->getInt('page', $page),
            50
        );

        return $this->render('admin/users/index.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/users/new', name: 'users_new')]
    public function addUser(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User;

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            //encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Nouvel utilisateur enregistré !');

            return $this->redirectToRoute('app_admin_users_list');
        }
        
        return $this->render('admin/users/new.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    #[Route('/users/edit/{id}', name: 'users_edit')]
    public function editUser(Request $request, EntityManagerInterface $entityManager, User $user)
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur modifié !');

            return $this->redirectToRoute('app_admin_users_list');
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    #[Route('/users/interest/{id}', name: 'users_interest')]
    public function addInterests(Request $request, EntityManagerInterface $entityManager, User $user)
    {
        $form = $this->createForm(InterestType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Centre d\'intérêt ajouté !');

            return $this->redirectToRoute('app_admin_users_list');
        }

        return $this->render('admin/users/interest.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
}
