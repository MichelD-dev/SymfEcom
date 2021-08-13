<?php

namespace App\Controller;

use App\Entity\User;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordEncoderInterface $passEncoder): Response
    {
        // Cette route  pour fonction de créer un nouvel utilisateur pour notre connexion
        // Nous allons utiliser un formulaire interne afin de créer notre utilisateur
        // Pour enregistrer l'utilisateur, nous devons de'abord récupérer l'EntityManager
        $entityManager = $this->getDoctrine()->getManager();
        // Nous créons notre formulaire interne
        $userForm = $this->createFormBuilder()
            ->add(
                'username',
                TextType::class,
                [
                    'label' => 'Nom de l\'utilisateur:',
                    'attr' => [
                        'class' => 'w3-input w3-border w3-margin w3-round',
                    ],
                ]
            )
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => true,
                'first_options' => [
                    'label' => 'Mot de passe:',
                    'attr' => [
                        'class' => 'w3-input w3-border w3-margin w3-round'
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmation du mot de passe:',
                    'attr' => [
                        'class' => 'w3-input w3-border w3-margin w3-round'
                    ],
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Role: USER' => 'ROLE_USER',
                    'Role: ADMIN' => 'ROLE_ADMIN'
                ],
                'expanded' => false,
                'multiple' => true,
                'attr' => [
                    'class' => 'w3-input w3-border w3-round w3-margin',
                    'style' => 'width: 500px; margin: 0 auto;'
                ],
            ])
            ->add('register', SubmitType::class, [
                'label' => 'Créer son compte',
                'attr' => [
                    'class' => 'w3-button w3-green w3-margin',
                    'style' => 'margin-top: 5px;'
                ],
            ])
            ->getForm();
        // Nous traitons les données reçues au sein de notre formulaire
        $userForm->handleRequest($request);
        if ($request->isMethod('post') && $userForm->isValid()) {
            // On récupère les informations du formulaire
            $data = $userForm->getData();
            // Nous créons et renseignons notre Entity User
            $user = new User;
            $user->setUsername($data['username']);
            $user->setPassword($passEncoder->encodePassword($user, $data['password']));
            $user->setRoles($data['roles']);
            $entityManager->persist($user);
            $entityManager->flush();
            // return $this->render('index/dump.html.twig', ['variable' => $user]);
            return $this->redirect($this->generateUrl('app_login'));
        }
        // Si le formulaire n'est pas validé, nous le présentons à l'utilisateur
        return $this->render('index/dataform.html.twig', [
            'formName' => 'Inscription utilisateur',
            'dataForm' => $userForm->createView()
        ]);
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout()
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
