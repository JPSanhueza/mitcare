<p>Hola {{ $student->nombre }},</p>

<p>
    Has sido inscrito en uno o más cursos de nuestra plataforma.
    Para acceder a tus certificados, primero debes crear tu contraseña.
</p>

<p>
    Puedes hacerlo en el siguiente enlace:
</p>

<p>
    <a href="{{ $url }}">{{ $url }}</a>
</p>

<p>
    Si no reconoces este correo, puedes ignorarlo.
</p>
