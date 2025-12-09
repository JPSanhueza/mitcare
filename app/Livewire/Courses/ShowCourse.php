<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use App\Services\Cart\CartService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Illuminate\Support\Str;

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

        // Asumimos disco 'public' (ajusta si usas S3)
        $disk = $this->disk ?? config('filesystems.default', 'public');

        return Storage::disk($disk)->url($path);
    }
}
