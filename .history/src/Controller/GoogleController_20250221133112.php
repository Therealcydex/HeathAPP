<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Google_Client;

class GoogleController extends AbstractController
{
    public function callback(Request $request): Response
    {
        $client = new Google_Client();
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
        $client->addScope('email');
        $client->addScope('profile');

        // Get the authorization code from the request
        $code = $request->get('code');

        if ($code) {
            $token = $client->fetchAccessTokenWithAuthCode($code);

            // Now you can retrieve the user’s profile information
            $client->setAccessToken($token['access_token']);
            $oauth2 = new \Google_Service_Oauth2($client);
            $userInfo = $oauth2->userinfo->get();

            // You can now work with the user info (e.g., email, name)
            return new Response('User info: ' . json_encode($userInfo));
        }

        return new RedirectResponse('/');
    }
}
