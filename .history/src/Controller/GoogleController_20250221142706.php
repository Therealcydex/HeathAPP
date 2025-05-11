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
        // Initialize the Google Client
        $client = new Google_Client();
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
        $client->addScope('email');
        $client->addScope('profile');

        // Get the code from the request
        $code = $request->get('code');
        
        if ($code) {
            try {
                // Fetch the access token using the authorization code
                $token = $client->fetchAccessTokenWithAuthCode($code);

                // Check if the access token is set
                if (!isset($token['access_token'])) {
                    return new Response('Error: Access token not found.');
                }

                // Set the access token in the Google Client
                $client->setAccessToken($token['access_token']);
                $client->setConfig(['debug' => true]);


                // Initialize Google OAuth2 service to get user info
                $oauth2 = new \Google_Service_Oauth2($client);
                
                try {
                    // Attempt to get user info from Google
                    $userInfo = $oauth2->userinfo->get();

                    // Check if user info is returned
                    if (is_null($userInfo)) {
                        return new Response('Error: No user information returned.');
                    }

                    // Return user info as a JSON response
                    return new Response('User info: ' . json_encode($userInfo));

                } catch (\Google_Service_Exception $e) {
                    // Handle Google API exceptions
                    return new Response('Google API error: ' . $e->getMessage());
                }

            } catch (\Google_Service_Exception $e) {
                // Handle errors related to Google API
                return new Response('Google Service error: ' . $e->getMessage());
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                // Handle Guzzle HTTP client errors
                return new Response('Guzzle HTTP error: ' . $e->getMessage());
            } catch (\Exception $e) {
                // Catch any general errors
                return new Response('Error: ' . $e->getMessage());
            }
        }

        // Redirect back to homepage if no code
        return new RedirectResponse('/');
    }
}
