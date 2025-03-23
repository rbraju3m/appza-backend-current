<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BuildRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    /**
     * Create a new message instance.
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->details['subject'],
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->details['mail_template'] == 'build_request'){
            return $this->subject($this->details['subject'])->view('mail-template.build-request');
        }
        if($this->details['mail_template'] == 'build_failed_android'){
            return $this->subject($this->details['subject'])->view('mail-template.build-failed-android');
        }
        if($this->details['mail_template'] == 'build_failed_ios'){
            return $this->subject($this->details['subject'])->view('mail-template.build-failed-ios');
        }
        if($this->details['mail_template'] == 'build_complete_android'){
            return $this->subject($this->details['subject'])->view('mail-template.build-success-android');
        }
        if($this->details['mail_template'] == 'build_complete_ios'){
            return $this->subject($this->details['subject'])->view('mail-template.build-success-ios');
        }
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
