<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function send(Request $req)
    {
        // honeypot
        if ($req->filled('website')) {
            return back()->with('ok', 'Gracias!');
        }

        $data = $req->validate([
            'nombre'   => 'required|string|max:120',
            'empresa'  => 'nullable|string|max:120',
            'email'    => 'required|email',
            'telefono' => 'nullable|string|max:40',
            'curso'    => 'nullable|string|max:150',
            'mensaje'  => 'required|string|max:3000',
        ]);

        // TODO: enviar mail / guardar / notificar Filament, etc.
        // Mail::to(config('mail.from.address'))->send(new ContactMail($data));

        return back()->with('ok', '¡Gracias! Tu solicitud fue enviada correctamente.');
    }
}
