<?php

namespace App\Livewire\Courses;

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

    public function mount(Course $course)
    {
        // Asegura que solo se vean cursos activos/publicados
        if (! $course->is_active || ($course->published_at && $course->published_at->isFuture())) {
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

        // 🔁 refrescar el FAB
        $this->dispatch('cart:refresh');

        // 🔔 abrir popover del FAB (sin Alpine)
        $this->dispatch('cart:open');

        // UI feedback
        $this->dispatch('toast', body: 'Curso agregado al carrito');
    }

    public function downloadFicha(): StreamedResponse
    {
        $path = $this->course->ficha;

        if (blank($path)) {
            $this->dispatch('toast', body: 'Este curso no tiene ficha disponible.');
            abort(404);
        }

        $disk = 'public'; // si después lo mueves a S3 lo ajustamos aquí

        if (! Storage::disk($disk)->exists($path)) {
            $this->dispatch('toast', body: 'La ficha no está disponible por el momento.');
            abort(404);
        }

        $filename = 'ficha-'.Str::slug($this->course->nombre ?? 'curso').'.pdf';

        return Storage::disk($disk)->download($path, $filename);
    }

    public function render()
    {
        return view('livewire.courses.show-course')
            ->title(Str::limit(strip_tags($this->course->nombre_diploma), 55).' | OTEC Mitcare');
    }

    private function resolveImageUrl(?string $path): string
    {
        if (blank($path)) {
            return asset('img/placeholder-img.png');
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Asumimos disco 'public' (ajusta si usas S3)
        $disk = $this->disk ?? config('filesystems.default', 'public');

        return Storage::disk($disk)->url($path);
    }
}
