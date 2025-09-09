<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Validate;
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

    // Flag de éxito
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

    protected array $validationAttributes = [
        'nombre'   => 'nombre',
        'empresa'  => 'empresa',
        'email'    => 'email',
        'telefono' => 'teléfono',
        'curso'    => 'curso de interés',
        'mensaje'  => 'mensaje',
    ];

    // Validación en vivo por campo
    public function updated($field): void
    {
        $this->validateOnly($field);
    }

    public function send(): void
    {
        $data = $this->validate();

        // Honeypot
        if (!empty($this->website)) {
            Log::warning('Honeypot Contact blocked', ['ip' => request()->ip()]);
            $this->resetForm();
            $this->dispatch('toast', type: 'success', title: 'Gracias', message: 'Tu solicitud fue enviada correctamente.');
            return;
        }

        try {
            Mail::raw(
                "Nueva solicitud de contacto\n"
                    . "Nombre: {$data['nombre']}\n"
                    . "Empresa: " . ($data['empresa'] ?? '-') . "\n"
                    . "Email: {$data['email']}\n"
                    . "Teléfono: " . ($data['telefono'] ?? '-') . "\n"
                    . "Curso: " . ($data['curso'] ?? '-') . "\n\n"
                    . "Mensaje:\n{$data['mensaje']}",
                function ($m) {
                    $m->to(config('mail.from.address'))
                        ->subject('Nueva solicitud de contacto');
                }
            );

            Log::info('Contact mail sent OK');

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
        $this->reset(['nombre', 'empresa', 'email', 'telefono', 'curso', 'mensaje', 'website']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
