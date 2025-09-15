<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Component;

class ShowCourse extends Component
{
    public Course $course;
    public string $imageUrl;

    public function mount(Course $course)
    {
        // Asegura que solo se vean cursos activos/publicados
        if (!$course->is_active || ($course->published_at && $course->published_at->isFuture())) {
            abort(404);
        }

        $this->course = $course;
        $this->imageUrl = $this->resolveImageUrl($course->image);
    }

    public function startCheckout(): void
    {
        // Aquí después iniciarás el flujo de compra
        // por ahora solo redirige a una futura ruta de checkout o emite evento:
        // return redirect()->route('checkout.start', $this->course->id);
        $this->dispatch('toast', body: 'Flujo de compra en construcción 👷‍♂️');
    }

    public function render()
    {
        return view('livewire.courses.show-course')
            ->title($this->course->nombre); // para <title>
    }

    private function resolveImageUrl(?string $path): string
    {
        if (blank($path)) return asset('img/placeholder-img.png');
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;

        // Asumimos disco 'public' (ajusta si usas S3)
        $disk = $this->disk ?? config('filesystems.default', 'public');
        return Storage::disk($disk)->url($path);
    }
}
