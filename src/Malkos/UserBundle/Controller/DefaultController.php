<?php

namespace Malkos\UserBundle\Controller;

use Malkos\UserBundle\Form\UserLoginType;
use Malkos\UserBundle\Form\UserRegisterType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="malkos_user_home")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return array();
    }

    /**
     * @Route("/admin/login", name="malkos_user_admin_login")
     * @Template()
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return array(
            // last username entered by the user
            'last_username' => $lastUsername,
            'error'         => $error,
        );
    }

    /**
     * @Route("/admin/logout", name="malkos_user_admin_logout")
     * @Template()
     */
    public function logoutAction()
    {
        $this->get('security.context')->setToken(null);
        $this->get('request')->getSession()->invalidate();
        return $this->redirect($this->generateUrl('malkos_user_home'));
//        return new Response("Logout");
    }

    /**
     * @Route("/admin/login_check", name="malkos_user_admin_check")
     * @Template()
     */
    public function checkAction()
    {
        return new Response("Hello");
    }

    /**
     * @Route("/servicelogin/{service}", name="malkos_user_servicelogin")
     * @Template()
     */
    public function serviceLoginAction(Request $request, $service = 'default')
    {
        $session = $request->getSession();
        $setter_id = null;
        $setter_token = null;
        $error = null;

        $formRegister = $this->createForm(new UserRegisterType());
        $formRegister->handleRequest($request);

        $formLogin = $this->createForm(new UserLoginType());
        $formLogin->handleRequest($request);

        if ($formRegister->isValid()) {
            $data = $formRegister->getData();

            $user = $this->get('symfonysocial.services.social')->createUser($data);

            $this->authenticateUser($user, null);

            return $this->redirect($this->generateUrl('malkos_user_home'));

        } elseif ($formLogin->isValid()) {

            $data = $formLogin->getData();
            $password = $data['password'];
            $userManager = $this->get('fos_user.user_manager');
            $user = $userManager->findUserByUsername($data['username']);

            if ($user) {
                // Get the encoder for the users password
                if ($this->get('security.encoder_factory')->getEncoder($user)->isPasswordValid(
                        $user->getPassword(), $password, $user->getSalt()
                    )
                ) {

                    $user = $this->get('symfonysocial.services.social')->createUser($data, $user);

                    $this->authenticateUser($user, $user->getPassword());

                    return $this->redirect($this->generateUrl('malkos_user_home'));
                } else {
                    $formLogin->get('password')->addError(new FormError('Invalid password!'));
                }
            } else {
                $formLogin->get('username')->addError(new FormError('No user found!!'));
            }
        } elseif (in_array($service, ['facebook', 'google', 'linkedin', 'twitter', 'github'])) {

            $userManager = $this->get('fos_user.user_manager');
            $user = $userManager->findUserByEmail($session->get('social.email'));

            if ($user) {
                // Get the encoder for the users password

                $this->get('symfonysocial.services.social')->getServiceFields(
                    $session->get('social.service'), $setter_id, $setter_token
                );

                $user = $this->get('symfonysocial.services.social')->createUser(
                    [
                        'service_service' => $session->get('social.service'),
                        'service_id' => $session->get('social.id'),
                        'service_token' => $session->get('social.token'),
                    ], $user);

                $this->authenticateUser($user, $user->getPassword());
                return $this->redirect($this->generateUrl('malkos_user_home'));
            } else {
                $error = 'User not found!';
            }
        }

        $currentService = $session->has('social.service') ? $session->get('social.service') : "";
        if ($session->has('social.connect')) {
            if (!$formRegister->isSubmitted()) {

                $formRegister->get('username')->setData($session->get('social.username'));
                $formRegister->get('email')->setData($session->get('social.email'));
                $formRegister->get('service_service')->setData($session->get('social.service'));
                $formRegister->get('service_id')->setData($session->get('social.id'));
                $formRegister->get('service_token')->setData($session->get('social.token'));
            }

            if (!$formLogin->isSubmitted()) {

                $formLogin->get('service_service')->setData($session->get('social.service'));
                $formLogin->get('service_id')->setData($session->get('social.id'));
                $formLogin->get('service_token')->setData($session->get('social.token'));
            }

        }

        return array(
            'register_form' => $formRegister->createView(),
            'form_login'    => $formLogin->createView(),
            'service'       => $currentService,
            'error'         => $error,
        );
    }

    /**
     * @param $user
     * @param $password
     */
    protected function authenticateUser($user, $password)
    {
        $providerKey = $this->container->getParameter('fos_user.firewall_name');
        $token = new UsernamePasswordToken($user, $password, $providerKey, $user->getRoles());

        $this->get('security.context')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));
    }
}
