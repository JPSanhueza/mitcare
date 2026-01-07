<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Services\Cart\CartService;
use Illuminate\Support\Str;
class FeaturedGrid extends Component
{
    /** Número de cursos a mostrar (15 por defecto). */
    public int $limit = 15;

    /** Título y subtítulo configurables. */
    public string $title = 'Cursos destacados';
    public string $subtitle = 'Te presentamos nuestros cursos destacados.';

    /** Si quieres forzar disco para imágenes (ej: 's3' o 'public'). Null = default. */
    public ?string $disk = null;

    public function mount(int $limit = 24, ?string $title = null, ?string $subtitle = null, ?string $disk = null): void
    {
        $this->limit = $limit;
        $this->title = $title ?? $this->title;
        $this->subtitle = $subtitle ?? $this->subtitle;
        $this->disk = $disk;
    }
    public function addToCart(int $courseId, CartService $cart): void
    {
        /** @var Course $course */
        $course = Course::query()
            ->where('id', $courseId)
            ->where('is_active', true)
            ->firstOrFail();

        // Respeta preventa (mismo comportamiento que en show)
        if ($course->pre_sale) {
            $this->dispatch('toast', body: 'Este curso está en preventa. Solo puedes reservar cupo.');
            return;
        }

        $imageUrl = $this->resolveImageUrl($course->image);

        $cart->add(
            $course->id,
            $course->nombre ?? 'Curso',
            (float) $course->price,
            1,
            $imageUrl
        );

        $this->dispatch('cart:refresh');
        $this->dispatch('cart:open');
        $this->dispatch('toast', body: 'Curso agregado al carrito');
    }
    public function render()
    {
        $courses = Course::query()
            ->when(method_exists(Course::class, 'scopePublicado'), fn($q) => $q->publicado(), fn($q) => $q->where('is_active', true))
            ->orderBy('order', 'asc')
            // ->take($this->limit)

            ->get()
            ->map(function (Course $c) {
                $c->image_url = $this->resolveImageUrl($c->image);
                return $c;
            });

        return view('livewire.courses.featured-grid', compact('courses'));
    }

    private function resolveImageUrl(?string $path): string
    {
        if (blank($path)) {
            return asset('img/placeholder-img.png'); // pon un placeholder si quieres
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $disk = $this->disk ?? config('filesystems.default', 'public');
        return Storage::disk($disk)->url($path);
    }
}
