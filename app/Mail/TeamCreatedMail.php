<?php

namespace App\Mail;

use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TeamCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $team;

    public function __construct(Team $team)
    {
        $this->team = $team;
    }

    public function build()
    {
        return $this->subject('Pemberitahuan: Tim Kompetisi Baru Menunggu Persetujuan')
                    ->view('emails.team-created');
    }
}
