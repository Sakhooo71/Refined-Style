<?php

namespace App\MessageHandler;

use App\Message\SendContactEmailMessage;
use App\Repository\ContactRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
class SendContactEmailMessageHandler
{
    private MailerInterface $mailer;
    private ContactRepository $contactRepository;

    public function __construct(MailerInterface $mailer, ContactRepository $contactRepository)
    {
        $this->mailer = $mailer;
        $this->contactRepository = $contactRepository;
    }

    public function __invoke(SendContactEmailMessage $message)
    {
        $contact = $this->contactRepository->find($message->getContactId());

        if (!$contact) {
            return;
        }

        $email = (new Email())
            ->from($contact->getEmail())
            ->to('ayham.test.2024@gmail.com') // Or your admin email
            ->subject($contact->getSubject())
            ->text($contact->getMessage());

        $this->mailer->send($email);
    }
}
