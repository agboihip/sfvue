<?php

namespace App\Controller;

use App\Form\RegisterFormType;
use App\Service\UserManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;

class SecurityController extends AbstractController
{
    public function __construct(private readonly UserManager $manager){}

    #[Route('/profile', name: 'app_profile')]
    public function profile(Request $req): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var User $user */ $user = $this->getUser();

        $user = $this->manager->one($user->getId());
        if(in_array('text/html', $req->getAcceptableContentTypes()))
            return $this->render('security/profile.html.twig', ['user' => $user,]);
        return $this->json($user, 200, [], array('groups' => ['user:read']));
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $utils, #[CurrentUser] ?User $user): Response
    {
        if (null !== $user) return $this->json(['message' => 'missing credentials',], Response::HTTP_UNAUTHORIZED);

        return $this->render('security/login.html.twig', [
            'last_username' => $utils->getLastUsername(),
            'error'         => $utils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterFormType::class, $user);

        if ($request->isMethod('POST')) {
            $data = $request->request->all($form->getName());
            $json = json_decode($request->getContent(), true);
            $form->submit($json??$data);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->manager->registerUser($user->setPassword($form->get('plainPassword')->getData()));
                return in_array('text/html', $request->getAcceptableContentTypes()) ? $this->redirectToRoute('app_login') : $this->json($user, Response::HTTP_CREATED, [], ['groups' => ['user:read']]);
            }
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        try {
            $this->manager->confirmUser((array)$request->getContent());
        } catch (NotFoundHttpException $exception) {

            $this->addFlash('verify_email_error', $exception->getMessage());
            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Your email address has been verified.');
        return $this->redirectToRoute('app_login');
    }
}
