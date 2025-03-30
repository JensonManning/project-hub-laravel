<?php

namespace App\Mail;

use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MessageNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The sender of the message.
     */
    public User $sender;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Message $message,
        public User $recipient
    ) {
        $this->sender = User::find($message->sender_id);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Message: {$this->message->title}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.message-notification',
            with: [
                'messageTitle' => $this->message->title,
                'messageSubject' => $this->message->subject,
                'messageBody' => $this->message->body,
                'senderName' => $this->sender->name,
                'recipientName' => $this->recipient->name,
                'projectId' => $this->message->project_id,
                'messageId' => $this->message->id,
                'messageUrl' => route('projects.messages.show', [
                    'project' => $this->message->project_id,
                    'message' => $this->message->id
                ]),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
