<?php

namespace App\User\Presentation\Controller;

use App\User\Domain\Entity\User;
use App\User\Infrastructure\Db\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['GET', 'POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $username = $request->request->get('username');
            $plainPassword = $request->request->get('password');

            // Проверка на уникальность email
            if ($userRepository->findBy(['email' => $email])) {
                $this->addFlash('error', 'Email уже используется');
            // Проверка на уникальность username
            } else if ($userRepository->findBy(['username' => $username])) {
                $this->addFlash('error', 'username уже используется');
            } else {
                $user = new User();
                $user->setEmail($email);
                $user->setUsername($username);
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
                $em->persist($user);
                $em->flush();
                $this->addFlash('success', 'Регистрация успешна! Теперь вы можете войти.');
                return $this->redirectToRoute('login');
            }
        }
        return $this->render('security/register.html.twig');
    }
} 