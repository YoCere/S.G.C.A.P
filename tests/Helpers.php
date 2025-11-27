<?php
// tests/Helpers.php
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Genera atributos de prueba basados en $fillable de un modelo.
 * Versión mejorada: mapea enums/keys comunes a valores válidos según tus migraciones.
 */
if (! function_exists('attrsForModel')) {
    function attrsForModel(string $modelClass, array $overrides = []): array {
        $m = new $modelClass;
        $fillable = method_exists($m, 'getFillable') ? $m->getFillable() : [];
        $data = [];

        foreach ($fillable as $f) {
            $lf = strtolower($f);

            // Foreign keys (campo termina en _id)
            if (Str::endsWith($lf, '_id') || preg_match('/(^|_)(id|ids)$/', $lf)) {
                // valor por defecto 1 (se espera que el test cree el registro con id=1)
                $data[$f] = 1;
                continue;
            }

            // Campos de referencia / código
            if (Str::contains($lf, ['referenc', 'referencia', 'reference', 'codigo', 'code'])) {
                $data[$f] = 'REF_' . strtoupper(substr(md5($f . rand()), 0, 6));
                continue;
            }

            // Barrio: valores permitidos en tu migración
            if (Str::contains($lf, ['barrio'])) {
                // usar uno de los enum válidos
                $data[$f] = 'Centro';
                continue;
            }

            // tipo_trabajo_pendiente: usar un valor permitido por el enum
            if (Str::contains($lf, ['tipo_trabajo_pendiente','tipo_trabajo'])) {
                // valores permitidos: conexion_nueva, corte_mora, reconexion
                $data[$f] = 'conexion_nueva';
                continue;
            }

            // estado: usar uno de los valores permitidos por tu migración
            if (Str::contains($lf, ['estado','status','state'])) {
                $data[$f] = 'pendiente_conexion';
                continue;
            }

            // Fechas / datetimes / timestamps
            if (Str::contains($lf, ['fecha','date','at','time','registro'])) {
                $data[$f] = Carbon::now()->toDateTimeString();
                continue;
            }

            // Montos / amounts / numeric
            if (Str::contains($lf, ['monto','amount','price','total','importe'])) {
                $data[$f] = 100.00;
                continue;
            }

            // Coordenadas
            if (Str::contains($lf, ['lat','long','lng'])) {
                $data[$f] = Str::contains($lf, 'lat') ? -20.1234500 : -63.12345000;
                continue;
            }

            // CI / DNI / identificadores personales
            if (Str::contains($lf, ['ci','dni','document','ident'])) {
                $data[$f] = '1000000';
                continue;
            }

            // telefono / phone
            if (Str::contains($lf, ['phone','telefono','cel'])) {
                $data[$f] = '777000111';
                continue;
            }

            // booleanos
            if (Str::startsWith($lf, 'is_') || Str::startsWith($lf, 'has_') || Str::contains($lf, ['activo','enabled'])) {
                $data[$f] = true;
                continue;
            }

            // enteros/generales por nombre
            if (Str::contains($lf, ['cantidad','count','numero','num','qty'])) {
                $data[$f] = 1;
                continue;
            }

            // fallback: string simple (sin caracteres que rompan CHECKs)
            $data[$f] = substr('valor_' . $f, 0, 64);
        }

        return array_merge($data, $overrides);
    }
}
