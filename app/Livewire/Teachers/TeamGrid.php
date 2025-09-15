<?php

namespace App\Livewire\Teachers;

use App\Models\Teacher;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class TeamGrid extends Component
{
    /** Cuántos docentes mostrar (9 por defecto). */
    public int $limit = 9;

    /** Título y subtítulo configurables. */
    public string $title = 'Nuestros Docentes';
    public string $subtitle = 'Nuestros cursos son dictados por referentes clínicos y académicos con reconocimiento nacional
 e internacional. Cada uno aporta su mirada interdisciplinaria desde áreas como fonoaudiología,
odontología, kinesiología y más.';

    /** Disco para las imágenes (null = usa por defecto "public"). */
    public ?string $disk = null;

    /** Mostrar nombre bajo la foto. */
    public bool $showNames = false;

    public function mount(
        int $limit = 9,
        ?string $title = null,
        ?string $subtitle = null,
        ?string $disk = null,
        bool $showNames = false
    ): void {
        $this->limit     = $limit;
        $this->title     = $title     ?? $this->title;
        $this->subtitle  = $subtitle  ?? $this->subtitle;
        $this->disk      = $disk;
        $this->showNames = $showNames;
    }

    public function render()
    {
        $teachers = Teacher::query()
            ->where('is_active', true)
            ->latest('created_at')
            ->take($this->limit)
            ->get()
            ->map(function (Teacher $t) {
                $t->foto_url = $this->resolveImageUrl($t->foto);
                return $t;
            });

        return view('livewire.teachers.team-grid', compact('teachers'));
    }

    private function resolveImageUrl(?string $path): string
    {
        if (blank($path)) {
            return asset('img/placeholder-avatar.png'); // agrega un placeholder si quieres
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        $disk = $this->disk ?? config('filesystems.default', 'public');
        return Storage::disk($disk)->url($path);
    }
}
