<?php

namespace Webkul\Admin\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderCommentNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The order comment instance.
     *
     * @var  \Webkul\Sales\Contracts\OrderComment  $comment
     */
    public $comment;

    /**
     * Create a new message instance.
     *
     * @param  \Webkul\Sales\Contracts\OrderComment  $comment
     * @return void
     */
    public function __construct($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(core()->getSenderEmailDetails()['email'], core()->getSenderEmailDetails()['name'])
                    ->to($this->comment->order->customer_email, $this->comment->order->customer_full_name)
                    ->subject(trans('shop::app.mail.order.comment.subject'))
                    ->view('shop::emails.sales.new-order-comment');
    }
}
