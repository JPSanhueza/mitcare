<div class="text-sm space-y-1">

    <div class="flex items-center gap-2">
        <span class="text-green-600 font-semibold">✔</span>
        <span>Creados: <strong>{{ $summary['created'] }}</strong></span>
    </div>

    <div class="flex items-center gap-2">
        <span class="text-yellow-600 font-semibold">⚠</span>
        <span>Omitidos por datos faltantes: <strong>{{ $summary['skipped_missing_required'] }}</strong></span>
    </div>

    <div class="flex items-center gap-2">
        <span class="text-red-600 font-semibold">❌</span>
        <span>RUT inválido: <strong>{{ $summary['skipped_invalid_rut'] }}</strong></span>
    </div>

    <div class="flex items-center gap-2">
        <span class="text-orange-600 font-semibold">⛔</span>
        <span>Duplicados: <strong>{{ $summary['skipped_existing'] }}</strong></span>
    </div>

    @if ($summary['failed'] > 0)
        <div class="flex items-center gap-2">
            <span class="text-red-700 font-semibold">💥</span>
            <span>Errores inesperados: <strong>{{ $summary['failed'] }}</strong></span>
        </div>
    @endif

</div>
