<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailVerificationService
{
    private MailerInterface $mailer;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(MailerInterface $mailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
    }

    public function sendVerificationEmail(User $user): void
    {
        // Generate the verification URL
        $verificationUrl = $this->urlGenerator->generate('app_verify_email', [
            'token' => $user->getVerificationToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        // Create the email
        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($user->getEmail())
            ->subject('Please verify your email address')
            ->html('<p>Please verify your email by clicking <a href="' . $verificationUrl . '">here</a>.</p>');

        // Send the email
        $this->mailer->send($email);
    }
}
