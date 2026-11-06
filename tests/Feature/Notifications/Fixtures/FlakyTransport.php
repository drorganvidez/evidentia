<?php

namespace Tests\Feature\Notifications\Fixtures;

use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

/**
 * Mail transport of the tests that fails for one address and keeps the rest.
 */
class FlakyTransport extends AbstractTransport
{
    /** @var list<string> */
    public array $delivered = [];

    public function __construct(private string $failingAddress)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        foreach ($message->getEnvelope()->getRecipients() as $recipient) {
            if ($recipient->getAddress() === $this->failingAddress) {
                throw new TransportException('Servidor de correo caído');
            }
            $this->delivered[] = $recipient->getAddress();
        }
    }

    public function __toString(): string
    {
        return 'flaky://';
    }
}
