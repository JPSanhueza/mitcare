<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends Model
{
    use HasFactory;

    // Si no cambias el nombre de la tabla, no hace falta $table
    protected $table = 'organizations';

    protected $fillable = [
        'nombre',
        'logo',
    ];

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    /**
     * Accesor para obtener la URL pública del logo.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        // Asumiendo que guardas en 'public' o en s3, lo puedes adaptar
        if (Storage::disk('public')->exists($this->logo)) {
            return Storage::disk('public')->url($this->logo);
        }

        // fallback por si viene una URL absoluta
        return $this->logo;
    }
}
