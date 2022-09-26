<?php


namespace App\Service;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\{LoaderError,RuntimeError,SyntaxError};

class MailerService
{
    public function __construct(private readonly MailerInterface $mailer, private readonly Environment $twig){}

    public function send($from, string $subject, $to, string $content, array $parameters = []): void
    {
        try {
            $email = (new Email())->from($from)->to($to)->subject($subject);

            if(empty($parameters)) $email->text($content);
            else $email->html($this->twig->render($content, $parameters));

            $this->mailer->send($email);
        } catch (TransportExceptionInterface | LoaderError | RuntimeError | SyntaxError $e) {
            print $e->getMessage()."\n"; //throw $e;
        }
    }
}