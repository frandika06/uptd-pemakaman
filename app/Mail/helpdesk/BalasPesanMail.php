<?php

namespace App\Mail\helpdesk;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BalasPesanMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @param object $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subjek = "Reply Pesan : " . $this->data->subjek;
        return $this->view('pages.mail.balas_pesan')
            ->subject($subjek)
            ->with([
                'nama_lengkap' => $this->data->nama_lengkap,
                'subjek' => $this->data->subjek,
                'pesan' => $this->data->pesan,
                'balasan' => $this->data->balasan,
            ]);
    }
}
