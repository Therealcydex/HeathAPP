<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Google_Client;
use Google_Service_Oauth2;
use GuzzleHttp\Exception\RequestException;

class GoogleController extends AbstractController
{
    #[Route('/google/login', name: 'google_login')]
    public function login(): RedirectResponse
    {
        // Initialize Google Client
        $client = new Google_Client();
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
        $client->addScope('email');
        $client->addScope('profile');

        // Get the Google OAuth URL
        $authUrl = $client->createAuthUrl();

        return new RedirectResponse($authUrl);
    }

    #[Route('/google/callback', name: 'google_callback')]
    public function callback(Request $request): Response
    {
        $client = new Google_Client();
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
        $client->addScope('email');
        $client->addScope('profile');

        // Ensure correct token URL
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        // Get authorization code from the request
        $code = $request->get('code');

        if (!$code) {
            return new Response('Error: Authorization code not found.');
        }

        try {
            // Fetch the access token using the authorization code
            $token = $client->fetchAccessTokenWithAuthCode($code);

            // Debugging: Check if we got a valid response
            if (!isset($token['access_token'])) {
                return new Response('Error: Access token not found. Full response: ' . json_encode($token));
            }

            if (!isset($token['expires_in'])) {
                return new Response('Error: Missing "expires_in" key. Full response: ' . json_encode($token));
            }

            // Set the access token
            $client->setAccessToken($token);

            // Initialize Google OAuth2 service
            $oauth2 = new Google_Service_Oauth2($client);

            try {
                // Get user info from Google
                $userInfo = $oauth2->userinfo->get();

                if (is_null($userInfo)) {
                    return new Response('Error: No user information returned.');
                }

                // Return user info as a JSON response
                return new Response('User info: ' . json_encode($userInfo));

            } catch (\Google_Service_Exception $e) {
                return new Response('Google API error: ' . $e->getMessage());
            }

        } catch (\Google_Service_Exception $e) {
            return new Response('Google Service error: ' . $e->getMessage());
        } catch (RequestException $e) {
            return new Response('Guzzle HTTP error: ' . $e->getMessage());
        } catch (\Exception $e) {
            return new Response('Error: ' . $e->getMessage());
        }

        return new RedirectResponse('/');
    }
    #[Route('/google/add-event/{appointmentId}', name: 'google_add_event')]
    public function addEventToGoogleCalendar(Request $request, int $appointmentId): Response
    {
        $session = $request->getSession();
        $token = $session->get('google_token');

        if (!$token) {
            return new RedirectResponse($this->generateUrl('google_login')); // Redirect if not authenticated
        }

        $client = $this->getGoogleClient();
        $client->setAccessToken($token);

        // Fetch the appointment details from the database
        $appointment = $this->getDoctrine()->getRepository(Appointment::class)->find($appointmentId);
        if (!$appointment) {
            return new Response('Error: Appointment not found.');
        }

        $calendarService = new Google_Service_Calendar($client);

        // Create a new Google Calendar Event
        $event = new Google_Service_Calendar_Event([
            'summary' => 'Appointment with Dr. ' . $appointment->getDoctor()->getName(),
            'description' => 'Appointment for ' . $appointment->getClientName(),
            'start' => [
                'dateTime' => $appointment->getAppointmentDate()->format('Y-m-d\TH:i:s'),
                'timeZone' => 'Europe/Paris',
            ],
            'end' => [
                'dateTime' => $appointment->getAppointmentDate()->modify('+1 hour')->format('Y-m-d\TH:i:s'),
                'timeZone' => 'Europe/Paris',
            ],
        ]);

        $calendarId = 'primary';
        $event = $calendarService->events->insert($calendarId, $event);

        return new Response('Event added: ' . $event->htmlLink);
    }

    private function getGoogleClient(): Google_Client
    {
        $client = new Google_Client();
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
        $client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        return $client;
    }
}
