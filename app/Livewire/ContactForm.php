<?php

namespace App\Livewire;

use App\Mail\ContactMessage;
use App\Models\Course;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

class ContactForm extends Component
{
    // Campos del formulario
    public string $nombre = '';

    public ?string $empresa = '';

    public string $email = '';

    public ?string $telefono = '';

    public ?int $curso_id = null; // <- ahora seleccionas por ID

    public string $mensaje = '';

    // Honeypot anti-spam (campo oculto)
    public string $website = '';

    public string $formKey = '';

    // Flag (por si lo usas en la vista)
    public bool $sent = false;

    // Opciones para el select
    public array $courses = [];

    public function mount(): void
    {
        $this->formKey = Str::uuid()->toString();
        // Carga de opciones del select (ajusta columnas/nombre según tu modelo)
        $this->courses = Course::query()
            ->when(function ($q) {
                // Si tienes un flag de publicación/activo, úsalo aquí
                $q->where('is_active', true);
            })
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn ($c) => ['id' => $c->id, 'nombre' => $c->nombre])
            ->toArray();

        // Si venimos del redirect posterior al envío, mostramos el toast
        if (session('contact_success')) {
            // opcional: $this->sent = true;
            $this->dispatch('toast', type: 'success', title: '¡Gracias!', message: 'Tu solicitud fue enviada correctamente.');
        }
    }

    protected function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'empresa' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'telefono' => ['nullable', 'digits_between:9,12', 'max:40'],
            'curso_id' => ['nullable', 'integer', 'exists:courses,id'], // <- valida ID
            'mensaje' => ['required', 'string', 'max:3000'],
            'website' => ['nullable', 'size:0'], // honeypot debe venir vacío
        ];
    }

    protected array $validationAttributes = [
        'nombre' => 'nombre',
        'empresa' => 'empresa',
        'email' => 'email',
        'telefono' => 'teléfono',
        'curso_id' => 'curso de interés',
        'mensaje' => 'mensaje',
    ];

    protected array $messages = [
        'required' => 'El :attribute es obligatorio.',
        'email.email' => 'Ingresa un correo válido.',
        'nombre.max' => 'El nombre no puede superar :max caracteres.',
        'empresa.max' => 'La empresa no puede superar :max caracteres.',
        'email.max' => 'El email no puede superar :max caracteres.',
        'telefono.digits_between' => 'El teléfono debe tener entre :min y :max dígitos.',
        'mensaje.max' => 'El mensaje no puede superar :max caracteres.',
        'website.size' => 'Error de validación.', // honeypot
        'curso_id.exists' => 'Selecciona un curso válido.',
    ];

    // Validación en vivo por campo
    public function updated($field): void
    {
        $this->validateOnly($field);
    }

    public function send(): void
    {
        $key = 'contact:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->dispatch('toast', type: 'error', title: 'Demasiados intentos', message: 'Intenta nuevamente en unos minutos.');

            return;
        }

        $this->validate();

        if (! empty($this->website)) {
            Log::warning('Honeypot Contact blocked', ['ip' => request()->ip()]);
            $this->resetForm(); // ← limpia
            $this->dispatch('toast', type: 'success', title: 'Gracias', message: 'Tu solicitud fue enviada correctamente.');

            return;
        }

        try {
            $cursoNombre = $this->curso_id ? Course::whereKey($this->curso_id)->value('nombre_diploma') : null;

            $payload = [
                'nombre' => $this->nombre,
                'empresa' => $this->empresa,
                'email' => $this->email,
                'telefono' => $this->telefono,
                'curso' => $cursoNombre,
                'curso_id' => $this->curso_id,
                'mensaje' => $this->mensaje,
            ];

            Mail::to(config('mail.from.address'))->send(new ContactMessage($payload));

            RateLimiter::hit($key, 300);
            Log::info('Contact mail sent OK');

            // 🔥 limpiar y toastear, nada más
            $this->resetForm();
            $this->dispatch('toast', type: 'success', title: '¡Gracias!', message: 'Tu solicitud fue enviada correctamente.');
        } catch (\Throwable $e) {
            Log::error('Contact mail ERROR', ['msg' => $e->getMessage()]);
            $this->dispatch('toast', type: 'error', title: 'Ups', message: 'No pudimos enviar tu solicitud. Intenta nuevamente.');
        }
    }

    private function resetForm(): void
    {
        $this->reset(['nombre', 'empresa', 'email', 'telefono', 'curso_id', 'mensaje', 'website']);
        $this->resetValidation();
        $this->formKey = Str::uuid()->toString(); // <- clave para forzar re-render
    }

    public function render()
    {
        return view('livewire.contact-form', [
            'courses' => $this->courses, // pasa opciones a la vista
        ]);
    }
}
