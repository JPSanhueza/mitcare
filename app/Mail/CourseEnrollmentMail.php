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
        // $courseUrl   = route('courses.show', $this->item->course_id ?? null);
        // $dashboardUrl= route('dashboard');

        return $this
            ->subject('Inscripción confirmada: '.$this->item->course_name)
            // Útil si quieres que respondan a soporte:
            // ->replyTo(config('mail.from.address'), config('mail.from.name'))
            ->markdown('mail.course-enrollment', [
                'previewText'  => '¡Listo! Confirmamos tu cupo en '.$this->item->course_name.'. Aquí te contamos los próximos pasos.',
                // 'courseUrl'    => $courseUrl,
                // 'dashboardUrl' => $dashboardUrl,
            ]);
    }
}
