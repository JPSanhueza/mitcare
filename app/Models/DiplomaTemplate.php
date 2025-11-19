<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiplomaTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'background_image',
        'signature_left',
        'signature_right',
        'font_family',
        'title_color',
        'text_color',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /* ----------------- RELACIONES ----------------- */

    public function diplomas()
    {
        return $this->hasMany(Diploma::class, 'template_id');
    }
}
