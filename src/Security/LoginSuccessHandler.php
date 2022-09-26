<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler,
    Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface,
    Symfony\Component\Routing\Generator\UrlGeneratorInterface,
    Symfony\Component\Security\Core\Authentication\Token\TokenInterface,
    Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\{RedirectResponse,Request,Response};

class LoginSuccessHandler extends AuthenticationSuccessHandler
{
    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        EventDispatcherInterface $dispatcher,
        $cookieProviders = [], bool $removeTokenFromBodyWhenCookiesUsed = false,
        private readonly ?UrlGeneratorInterface $generator = null)
    {
        parent::__construct($jwtManager, $dispatcher, $cookieProviders, $removeTokenFromBodyWhenCookiesUsed);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $response = parent::onAuthenticationSuccess($request, $token);

        if(in_array('text/html', $request->getAcceptableContentTypes())) {
            $roles = $token->getUser()?->getRoles();
            $url = in_array('ROLE_ADMIN', $roles) ?
                $this->generator->generate('admin') :
                $this->generator->generate('app_home');

            return new RedirectResponse($url, Response::HTTP_FOUND, $response->headers->allPreserveCase());
        }return $response;
    }
}