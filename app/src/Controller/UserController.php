<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFormType;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/register", methods={"POST"}, name="register")
     */
    public function register(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $tokenManager
    ): JsonResponse {
        $payload = $request->toArray();

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user)
            ->submit($payload)
        ;

        if ($form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $hasher->hashPassword($user, $form->get('password')->getData())
            );

            $entityManager->persist($user);
            $entityManager->flush();

            return new JsonResponse(['token' => $tokenManager->create($user)], Response::HTTP_CREATED);
        }

        $errors = $this->getFormErrors($form);

        return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/login", methods={"POST"}, name="login")
     */
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $hasher,
        JWTTokenManagerInterface $tokenManager
    ): JsonResponse {
        $payload = $request->toArray();
        $email = $payload['email'];

        $form = $this->createForm(LoginFormType::class)
            ->submit([
                'email' => $email
            ])
        ;

        if ($form->isValid()) {
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user instanceof User === false) {
                return new JsonResponse(
                    [
                        'errors' => [
                            'email' => sprintf("User with email (%s) does not exist.", $email),
                        ]
                    ],
                    Response::HTTP_NOT_FOUND);
            }

            $password = $payload['password'];

            if ($hasher->isPasswordValid($user, $password) === false) {
                return new JsonResponse(
                    [
                        'errors' => [
                            'password' => "Incorrect password - please try again.",
                        ]
                    ],
                    Response::HTTP_UNAUTHORIZED);
            }

            return new JsonResponse(['token' => $tokenManager->create($user)]);
        }

        $errors = $this->getFormErrors($form);

        return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * NOTE - FOR TESTING IN THE TECH TEST ONLY
     * THIS WOULD NEVER EXIST IN PRODUCTION
     *
     * @Route("/delete", methods={"DELETE"}, name="delete")
     */
    public function deleteAll(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $userRepository->deleteAllUsers();
        $entityManager->flush();

        return new JsonResponse("All Users Deleted");
    }

    private function getFormErrors(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->getErrors(true) as $error) {
            $errors['errors'][$error->getOrigin()->getName()] = $error->getMessage();
        }

        return $errors;
    }
}
