<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Google_Client;
use Symfony\Component\HttpFoundation\Response;

class GoogleController extends AbstractController
{
    #[Route('/google/login', name: 'google_login')]
    public function login(): RedirectResponse
    {
        // Initialize the Google Client
        $client = new Google_Client();
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
        $client->addScope('email');
        $client->addScope('profile');

        // Get the Google OAuth URL
        $authUrl = $client->createAuthUrl();

        // Redirect the user to the Google login page
        return new RedirectResponse($authUrl);
    }

    #[Route('/google/callback', name: 'google_callback')]
    public function callback(Request $request): Response
    {
        // Initialize the Google Client again
        $client = new Google_Client();
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
        $client->addScope('email');
        $client->addScope('profile');

        // Get the code from the request
        $code = $request->get('code');

        // If the code is present, get the access token and user info
        if ($code) {
            $token = $client->fetchAccessTokenWithAuthCode($code);
            $client->setAccessToken($token['access_token']);
            $oauth2 = new \Google_Service_Oauth2($client);
            $userInfo = $oauth2->userinfo->get();

            // Do something with the user info
            return new Response('User info: ' . json_encode($userInfo));
        }

        // Redirect back to homepage if no code
        return new RedirectResponse('/');
    }
}
