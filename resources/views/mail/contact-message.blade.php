<h2>Nueva solicitud de contacto</h2>
<ul>
  <li><strong>Nombre:</strong> {{ $data['nombre'] }}</li>
  <li><strong>Empresa:</strong> {{ $data['empresa'] ?? '-' }}</li>
  <li><strong>Email:</strong> {{ $data['email'] }}</li>
  <li><strong>Teléfono:</strong> {{ $data['telefono'] ?? '-' }}</li>
  <li><strong>Curso:</strong> {{ $data['curso'] ?? '-' }}</li>
</ul>
<p><strong>Mensaje:</strong></p>
<p>{{ $data['mensaje'] }}</p>
