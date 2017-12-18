<?php
namespace Xpressengine\Plugins\Freezer\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Common extends Mailable
{
    use Queueable, SerializesModels;

    public $view;

    public $subject;

    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($view, $subject, $data = [])
    {
        $this->view = $view;
        $this->subject = $subject;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view($this->view, $this->data)->subject($this->subject);
    }
}