<?php

namespace App\Livewire\Courses;

use App\Models\BrochureRequest;
use App\Models\Course;
use App\Services\Cart\CartService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShowCourse extends Component
{
    public Course $course;

    public string $imageUrl;

    public ?int $courseId = null;

    public int $qty = 1;

    // ✅ Modal obligatorio
    public bool $showFichaModal = false;

    // ✅ 3 datos obligatorios
    public string $ficha_full_name = '';
    public string $ficha_email = '';
    public string $ficha_phone = '';
    public string $ficha_phone_country = '+56';
    public string $ficha_phone_number = '';


    public function mount(Course $course)
    {
        if (!$course->is_active || ($course->published_at && $course->published_at->isFuture())) {
            abort(404);
        }

        $this->courseId = $course->id;
        $this->course = $course;
        $this->imageUrl = $this->resolveImageUrl($course->image);
    }

    public function addToCart(CartService $cart): void
    {
        $c = $this->course;

        $cart->add($c->id, $c->nombre ?? 'Curso', (float) $c->price, max(1, $this->qty), $this->imageUrl);

        $this->dispatch('cart:refresh');
        $this->dispatch('cart:open');
        $this->dispatch('toast', body: 'Curso agregado al carrito');
    }

    /**
     * Antes: descargaba.
     * Ahora: abre modal obligatorio (si existe ficha).
     */
    public function downloadFicha(): void
    {
        if (blank($this->course->ficha)) {
            $this->dispatch('toast', body: 'Este curso no tiene ficha disponible.');
            return;
        }

        $this->showFichaModal = true;
    }

    public function submitFichaAndDownload(): StreamedResponse
    {
        if (blank($this->course->ficha)) {
            $this->showFichaModal = false;
            abort(404);
        }

        $data = $this->validate([
            'ficha_full_name' => ['required', 'string', 'min:3', 'max:120'],
            'ficha_email' => ['required', 'email:rfc,dns', 'max:190'],
            'ficha_phone_country' => ['required', 'regex:/^\+\d{1,4}$/'],
            'ficha_phone_number' => ['required', 'regex:/^\d{6,14}$/'],
        ], [
            'ficha_full_name.required' => 'Debes ingresar tu nombre completo.',
            'ficha_email.required' => 'Debes ingresar tu correo.',
            'ficha_email.email' => 'Ingresa un correo válido.',
            'ficha_phone_country.required' => 'Ingresa el código de país.',
            'ficha_phone_country.regex' => 'Formato inválido. Ej: +56',
            'ficha_phone_number.required' => 'Ingresa el número.',
            'ficha_phone_number.regex' => 'Solo números. Ej: 912345678',

        ]);
        $country = preg_replace('/\s+/', '', (string) $data['ficha_phone_country']);
        $number = preg_replace('/\D+/', '', (string) $data['ficha_phone_number']);

        $phone = $country . $number;

        // validación final E.164 (por si acaso)
        if (!preg_match('/^\+[1-9]\d{6,14}$/', $phone)) {
            $this->addError('ficha_phone_number', 'Teléfono inválido. Revisa código país y número.');
            
        }

        BrochureRequest::create([
            'course_id' => $this->course->id,
            'full_name' => $data['ficha_full_name'],
            'email' => $data['ficha_email'],
            'phone' => $phone,
        ]);


        $this->showFichaModal = false;
        $this->reset(['ficha_full_name', 'ficha_email', 'ficha_phone_number']);
        $this->ficha_phone_country = '+';


        // ✅ descarga real (misma lógica que ya tenías)
        return $this->downloadFichaFileResponse();
    }

    /**
     * Tu descarga original, intacta, solo movida aquí.
     */
    protected function downloadFichaFileResponse(): StreamedResponse
    {
        $path = $this->course->ficha;

        if (blank($path)) {
            $this->dispatch('toast', body: 'Este curso no tiene ficha disponible.');
            abort(404);
        }

        $disk = 'public';

        if (!Storage::disk($disk)->exists($path)) {
            $this->dispatch('toast', body: 'La ficha no está disponible por el momento.');
            abort(404);
        }

        $filename = 'ficha-' . Str::slug(strip_tags($this->course->nombre) ?? 'curso') . '.pdf';

        return Storage::disk($disk)->download($path, $filename);
    }

    public function render()
    {
        return view('livewire.courses.show-course')
            ->title(Str::limit(strip_tags($this->course->nombre), 55) . ' | OTEC Mitcare');
    }

    private function resolveImageUrl(?string $path): string
    {
        if (blank($path)) {
            return asset('img/placeholder-img.png');
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $disk = $this->disk ?? config('filesystems.default', 'public');

        return Storage::disk($disk)->url($path);
    }
}
