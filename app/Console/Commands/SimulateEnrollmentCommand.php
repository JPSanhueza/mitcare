<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAttendee;
use App\Services\EnrollmentService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SimulateEnrollmentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollment:simulate 
                            {email : Email del participante}
                            {--course= : ID del curso (opcional, usa el primero disponible si no se especifica)}
                            {--name= : Nombre del participante (opcional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simula una compra y proceso de enroll enviando el correo de inscripción';

    /**
     * Execute the console command.
     */
    public function handle(EnrollmentService $enrollmentService)
    {
        $email = $this->argument('email');
        $courseId = $this->option('course');
        $name = $this->option('name') ?? 'Usuario de Prueba';

        // Buscar o seleccionar curso
        if ($courseId) {
            $course = Course::find($courseId);
            if (!$course) {
                $this->error("No se encontró el curso con ID: {$courseId}");
                return 1;
            }
        } else {
            $course = Course::first();
            if (!$course) {
                $this->error("No hay cursos disponibles en la base de datos.");
                return 1;
            }
            $this->info("Usando curso: {$course->nombre} (ID: {$course->id})");
        }

        $this->info("Simulando compra para: {$name} ({$email})");
        $this->info("Curso: {$course->nombre}");

        // Crear orden de prueba
        $order = Order::create([
            'code' => 'TEST-' . strtoupper(Str::random(8)),
            'buyer_name' => $name,
            'buyer_email' => $email,
            'status' => 'paid',
            'subtotal' => $course->price ?? 50000,
            'total' => $course->price ?? 50000,
            'payment_method' => 'test',
            'currency' => 'CLP',
        ]);

        $this->line("✓ Orden creada: {$order->code}");

        // Crear item de la orden
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'course_id' => $course->id,
            'course_name' => $course->nombre,
            'qty' => 1,
            'unit_price' => $course->price ?? 50000,
            'subtotal' => $course->price ?? 50000,
        ]);

        $this->line("✓ Item de orden creado");

        // Crear attendee (asistente/participante)
        $attendee = OrderItemAttendee::create([
            'order_item_id' => $orderItem->id,
            'course_id' => $course->id,
            'name' => $name,
            'email' => $email,
            'status' => 'pending', // Se actualizará a 'enrolled' por el servicio
        ]);

        $this->line("✓ Asistente creado: {$attendee->name}");

        // Ejecutar el proceso de enroll
        $this->info("\nEjecutando proceso de enroll...");
        
        try {
            $enrollmentService->enrollFromOrder($order);
            $this->newLine();
            $this->info("✓ Proceso completado exitosamente!");
            $this->info("✓ Correo de inscripción enviado a: {$email}");
            $this->info("✓ Estado del asistente actualizado a: enrolled");
            
            $this->newLine();
            $this->comment("Detalles de la simulación:");
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['Orden', $order->code],
                    ['Curso', $course->nombre],
                    ['Participante', $attendee->name],
                    ['Email', $attendee->email],
                    ['Estado', $attendee->fresh()->status],
                ]
            );

            return 0;
        } catch (\Throwable $e) {
            $this->error("Error al procesar el enroll: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
