<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAttendee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CourseEnrollmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public OrderItem $item,
        public OrderItemAttendee $attendee,
    ) {}

    public function build()
    {
        return $this->subject('Inscripción confirmada: '.$this->item->course_name)
            ->markdown('mail.course-enrollment');
    }
}
