<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public $messageRecord;

    /**
     * Create a new message instance.
     *
     * @param Message $messageRecord
     */
    public function __construct(Message $messageRecord)
    {
        $this->messageRecord = $messageRecord;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // Use the email view below and pass in the necessary data.
        return $this->subject('New message from a seller/customer')
                    ->view('emails.contact_admin')
                    ->with([
                        'senderName'  => optional($this->messageRecord->user)->name ?? 'Unknown',
                        'subjectText' => $this->messageRecord->subject,
                        'bodyText'    => $this->messageRecord->message,
                    ]);
    }
}
