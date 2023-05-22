<?php
/**
 * RTCKit\SIP\StreamParser Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP;

/**
 * Stream Parser Class
 */
class StreamParser
{
    /* Stream parse process return statuses */
    public const READY = 0;
    public const WAIT_MESSAGE = 1;
    public const WAIT_BODY = 2;
    public const SUCCESS = 3;

    /** @var string Buffer holding stream bytes */
    private string $buffer = '';

    /** @var Message Message extracted from stream */
    private Message $message;

    /**
     * Processes a data chunk into SIP Messages
     *
     * @param string $chunk Raw bytes
     * @param ?list<Message> $messages Resulting messages, if any
     * @psalm-suppress ReferenceConstraintViolation Doesn't make a lot of sense here
     * @return int Execution status
     */
    public function process(string $chunk, ?array &$messages): int
    {
        if (!isset($this->buffer[0])) {
            $this->buffer = ltrim($chunk);

            if (!isset($this->buffer[0])) {
                /* Nothing to process! */
                return self::READY;
            }
        } else {
            $this->buffer .= $chunk;
        }

        $status = self::READY;
        $messages = [];

        do {
            if (!isset($this->message)) {
                /* We're waiting for new messages */
                $blocks = explode("\r\n\r\n", $this->buffer, 2);

                if (count($blocks) === 1) {
                    /* We don't have a whole message just yet */
                    $status = self::WAIT_MESSAGE;

                    break;
                }

                assert(count($blocks) === 2);

                $this->message = Message::parse($this->buffer, true);
                $this->buffer = $blocks[1];
            } else {
                /* We are waiting for the remainder of the body of the current message */
            }

            $bodyLength = strlen($this->buffer);

            if (isset($this->message->contentLength)) {
                if ($bodyLength < $this->message->contentLength->value) {
                    /* We need to wait for more body bytes */
                    $status = self::WAIT_BODY;

                    break;
                } else if ($bodyLength > $this->message->contentLength->value) {
                    /* Move the remainder of bytes into the buffer and save the body */
                    $this->message->body = substr($this->buffer, 0, $this->message->contentLength->value);
                    $this->buffer = ltrim(substr($this->buffer, $this->message->contentLength->value));
                } else {
                    /* We're all set! */
                    $this->message->body = $this->buffer;
                    $this->buffer = '';
                }
            } else {
                $this->message->body = '';
            }

            $messages[] = $this->message;

            unset($this->message);

            $status = self::SUCCESS;
        } while (true);

        return (isset($messages[0]))
            ? self::SUCCESS
            : $status;
    }
}
