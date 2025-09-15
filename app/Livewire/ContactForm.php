<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use App\Mail\ContactMessage;

class ContactForm extends Component
{
    // Campos del formulario
    public string $nombre    = '';
    public ?string $empresa  = '';
    public string $email     = '';
    public ?string $telefono = '';
    public ?string $curso    = '';
    public string $mensaje   = '';

    // Honeypot anti-spam (campo oculto)
    public string $website = '';

    // Flag (por si lo usas en la vista)
    public bool $sent = false;

    protected function rules(): array
    {
        return [
            'nombre'   => ['required', 'string', 'max:120'],
            'empresa'  => ['nullable', 'string', 'max:120'],
            'email'    => ['required', 'email', 'max:160'],
            'telefono' => ['nullable', 'digits_between:9,12', 'max:40'],
            'curso'    => ['nullable', 'string', 'max:150'],
            'mensaje'  => ['required', 'string', 'max:3000'],
            'website'  => ['nullable', 'size:0'], // honeypot debe venir vacío
        ];
    }

    // Traducción de nombres de atributos (para mensajes de error)
    protected array $validationAttributes = [
        'nombre'   => 'nombre',
        'empresa'  => 'empresa',
        'email'    => 'email',
        'telefono' => 'teléfono',
        'curso'    => 'curso de interés',
        'mensaje'  => 'mensaje',
    ];

    // Mensajes en español (Opción A rápida por componente)
    protected array $messages = [
        'required'        => 'El :attribute es obligatorio.',
        'email.email'     => 'Ingresa un correo válido.',
        'nombre.max'      => 'El nombre no puede superar :max caracteres.',
        'empresa.max'     => 'La empresa no puede superar :max caracteres.',
        'email.max'       => 'El email no puede superar :max caracteres.',
        'telefono.digits_between' => 'El teléfono debe tener entre :min y :max dígitos.',
        'curso.max'       => 'El curso no puede superar :max caracteres.',
        'mensaje.max'     => 'El mensaje no puede superar :max caracteres.',
        'website.size'    => 'Error de validación.', // honeypot
    ];

    // Validación en vivo por campo
    public function updated($field): void
    {
        $this->validateOnly($field);
    }

    public function send(): void
    {
        // Rate limit (opcional, evita abuso: 5 envíos/5 min por IP)
        $key = 'contact:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->dispatch('toast', type: 'error', title: 'Demasiados intentos', message: 'Intenta nuevamente en unos minutos.');
            return;
        }

        $data = $this->validate();

        // Honeypot
        if (!empty($this->website)) {
            Log::warning('Honeypot Contact blocked', ['ip' => request()->ip()]);
            $this->resetForm();
            $this->dispatch('toast', type: 'success', title: 'Gracias', message: 'Tu solicitud fue enviada correctamente.');
            return;
        }

        try {
            // Opción simple con Mail::raw (no necesitas Mailable)
            // Mail::raw(
            //     "Nueva solicitud de contacto\n"
            //     ."Nombre: {$data['nombre']}\n"
            //     ."Empresa: ".($data['empresa'] ?? '-')."\n"
            //     ."Email: {$data['email']}\n"
            //     ."Teléfono: ".($data['telefono'] ?? '-')."\n"
            //     ."Curso: ".($data['curso'] ?? '-')."\n\n"
            //     ."Mensaje:\n{$data['mensaje']}",
            //     function ($m) {
            //         $m->to(config('mail.from.address'))
            //           ->subject('Nueva solicitud de contacto');
            //     }
            // );

            // Si prefieres un Mailable, descomenta y crea App\Mail\ContactMessage:
            Mail::to(config('mail.from.address'))->send(new ContactMessage($data));

            RateLimiter::hit($key, 300); // 5 min
            Log::info('Contact mail sent OK');
            $this->sent = true;

            // Limpia campos y validaciones
            $this->resetForm();

            // Toast bonito
            $this->dispatch('toast', type: 'success', title: '¡Gracias!', message: 'Tu solicitud fue enviada correctamente.');
        } catch (\Throwable $e) {
            Log::error('Contact mail ERROR', ['msg' => $e->getMessage()]);
            $this->dispatch('toast', type: 'error', title: 'Ups', message: 'No pudimos enviar tu solicitud. Intenta nuevamente.');
        }
    }

    private function resetForm(): void
    {
        $this->reset(['nombre','empresa','email','telefono','curso','mensaje','website']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
