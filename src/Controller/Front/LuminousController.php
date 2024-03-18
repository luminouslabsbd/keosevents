<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use App\Service\AppServices;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
// use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// Used for Login 
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

// Used for Sign Up 
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use App\Form\RegistrationType;

class LuminousController extends Controller {
    // For Login
    private $tokenManager;
    // For Sign Up
    private $eventDispatcher;
    private $formFactory;
    private $userManager;
    private $tokenStorage;
    private $services;

    public function __construct(CsrfTokenManagerInterface $tokenManager = null, FactoryInterface $formFactory,EventDispatcherInterface $eventDispatcher,  UserManagerInterface $userManager, TokenStorageInterface $tokenStorage, AppServices $services) {
        // For Login 
        $this->tokenManager = $tokenManager;
        // For Sign Up 
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
        $this->services = $services;

    }

    /**
     * @Route("/", name="ll_home")
    */
    
    public function ll_home(Request $request, PaginatorInterface $paginator, TranslatorInterface $translator,AppServices $services) {
        
        return $this->render('Front/Luminous/home.html.twig');
    }

    /**
     * @Route("/luminous/ll_signin", name="ll_signin")
    */

    public function ll_signin(Request $request, PaginatorInterface $paginator, AppServices $services, TranslatorInterface $translator) {
// dd($request->all());
          
            if ($this->isGranted("IS_AUTHENTICATED_REMEMBERED")) {
                return $this->redirectToRoute("dashboard_index");
            }

            /** @var $session Session */
            $session = $request->getSession();

            $authErrorKey = Security::AUTHENTICATION_ERROR;
            $lastUsernameKey = Security::LAST_USERNAME;

            // get the error if any (works with forward and redirect -- see below)
            if ($request->attributes->has($authErrorKey)) {
                $error = $request->attributes->get($authErrorKey);
            } elseif (null !== $session && $session->has($authErrorKey)) {
                $error = $session->get($authErrorKey);
                $session->remove($authErrorKey);
            } else {
                $error = null;
            }

            if (!$error instanceof AuthenticationException) {
                $error = null; // The value does not come from the security component.
            }

            // last username entered by the user
            $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);

            $csrfToken = $this->tokenManager ? $this->tokenManager->getToken('authenticate')->getValue() : null;

            $data = array(
                'last_username' => $lastUsername,
                'error' => $error,
                'csrf_token' => $csrfToken
            );

        return $this->render('Front/Luminous/signin.html.twig',$data);
    }

    /**
     * @Route("/luminous/signup/attendee", name="ll_signup_attendee")
    */

    public function ll_signup_attendee(Request $request) {
        $user = $this->userManager->createUser();
        $user->setEnabled(true);

        $form = $this->createForm(RegistrationType::class, $user);
        if ($this->isGranted("IS_AUTHENTICATED_REMEMBERED")) {
            return $this->redirectToRoute("dashboard_index");
        }

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form->remove("organizer");

        if ($this->services->getSetting("google_recaptcha_enabled") == "no") {
            $form->remove("recaptcha");
        }

        $form->setData($user);
        $form->handleRequest($request);
        
        try {
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $event = new FormEvent($form, $request);
                    $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
                    $user->addRole('ROLE_ATTENDEE');

                    $this->userManager->updateUser($user);
                    if (null === $response = $event->getResponse()) {
                        $url = $this->generateUrl('fos_user_registration_confirmed');
                        $response = new RedirectResponse($url);
                    }
                    $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));
                    return $response;
                }
                $event = new FormEvent($form, $request);
                $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);
                if (null !== $response = $event->getResponse()) {
                    return $response;
                }
            }
        } catch (\Execption $ex) {
            dd($ex->getMessage());
        }
        
        // $csrfToken = $this->tokenManager ? $this->tokenManager->getToken('authenticate')->getValue() : null;

        $data = [
            'form' => $form->createView(),
        ];
        return $this->render('Front/Luminous/attendee_signup.html.twig', $data);

    }       

    /**
     * @Route("/luminous/signup/organizer", name="ll_signup_organizer")
    */

    public function ll_signup_organizer(Request $request, PaginatorInterface $paginator, AppServices $services, TranslatorInterface $translator) {
        if ($this->isGranted("IS_AUTHENTICATED_REMEMBERED")) {
            return $this->redirectToRoute("dashboard_index");
        }

        $user = $this->userManager->createUser();
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->formFactory->createForm();
        if ($this->services->getSetting("google_recaptcha_enabled") == "no") {
            $form->remove("recaptcha");
        }
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $event = new FormEvent($form, $request);
                $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
                $user->addRole('ROLE_ORGANIZER');
                $user->getOrganizer()->setUser($user);
                $this->userManager->updateUser($user);

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('fos_user_registration_confirmed');
                    $response = new RedirectResponse($url);
                }

                $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                return $response;
            }

            $event = new FormEvent($form, $request);
            $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);

            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        return $this->render('Front/Luminous/organizer_signup.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/luminous/forget-password", name="ll_forget_password")
    */

    public function ll_forget_password(Request $request, PaginatorInterface $paginator, AppServices $services, TranslatorInterface $translator) {
        
        return $this->render('Front/Luminous/forget_password.html.twig');
    }


     public function checkEmailAction(Request $request) {
        $email = $request->getSession()->get('fos_user_send_confirmation_email/email');

        if (empty($email)) {
            return new RedirectResponse($this->generateUrl('ll_home'));
        }

        $request->getSession()->remove('fos_user_send_confirmation_email/email');
        $user = $this->userManager->findUserByEmail($email);

        if (null === $user) {
            return new RedirectResponse($this->container->get('router')->generate('ll_signin'));
        }
        return $this->render('Front/Luminous/check_email.html.twig', array(
            'user' => $user,
        ));
    }


}
