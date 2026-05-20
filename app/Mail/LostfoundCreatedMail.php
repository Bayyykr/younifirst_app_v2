<?php

namespace App\Mail;

use App\Models\LostfoundItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LostfoundCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $item;

    public function __construct(LostfoundItem $item)
    {
        $this->item = $item;
    }

    public function build()
    {
        return $this->subject('Pemberitahuan: Postingan Lost & Found Baru')
                    ->view('emails.lostfound-created');
    }
}
