<?php

    namespace App\Http\Controllers;

    use App\Models\Tariff;
    use App\Models\Client;
    use App\Models\Property;
    use App\Models\Debt;
    use App\Models\Fine;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Log;
    use App\Models\Setting;

    class WelcomeController extends Controller
    {
        public function welcome()
{
    $settings = [
        'contact_address'   => Setting::getValue('contact_address'),
        'contact_phone'     => Setting::getValue('contact_phone'),
        'contact_email'     => Setting::getValue('contact_email'),
        'schedule_weekdays' => Setting::getValue('schedule_weekdays'),
        'schedule_saturday' => Setting::getValue('schedule_saturday'),
    ];

    return view('welcome', compact('settings'));
}

        public function consultarDeuda()
        {
            $settings = [
                'contact_phone'     => Setting::getValue('contact_phone'),
                'schedule_weekdays' => Setting::getValue('schedule_weekdays'),
                'schedule_saturday' => Setting::getValue('schedule_saturday'),
                'qr_image'          => Setting::getValue('qr_image'),
            ];

            return view('consultar-deuda', compact('settings'));
        }

        public function buscarDeuda(Request $request)
        {
            // Protección básica contra bots (honeypot)
            if (!empty($request->honeypot)) {
                Log::warning('Intento de bot detectado - honeypot activado');
                return back()
                    ->with('error', 'Solicitud inválida.')
                    ->withInput();
            }

            // Validación simple
            $validated = $request->validate([
                'codigo_cliente' => 'required|string|max:20',
                'ci' => 'required|string|max:20',
            ]);

            Log::info('Consulta de deuda iniciada:', [
                'codigo_cliente' => $request->codigo_cliente,
                'ci' => $request->ci
            ]);

            // Buscar cliente
            $client = Client::where('codigo_cliente', $request->codigo_cliente)
                            ->where('ci', $request->ci)
                            ->where('estado_cuenta', 'activo')
                            ->first();

            if (!$client) {
                Log::warning('Cliente no encontrado:', [
                    'codigo' => $request->codigo_cliente,
                    'ci' => $request->ci
                ]);
                return back()
                    ->with('error', 'Cliente no encontrado. Verifique su código de cliente y cédula de identidad.')
                    ->withInput();
            }

            Log::info('Cliente encontrado:', ['client_id' => $client->id]);

            // Consulta COMPLETA de propiedades y deudas (sin multas por ahora)
            $properties = Property::with([
                'tariff',
                'debts' => function($query) {
                    $query->where('monto_pendiente', '>', 0)
                        ->orderBy('fecha_emision', 'desc');
                }
            ])
            ->where('cliente_id', $client->id)
            ->get();

            Log::info('Propiedades encontradas:', [
                'total' => $properties->count(),
                'detalle' => $properties->map(function($prop) {
                    return [
                        'id' => $prop->id,
                        'estado' => $prop->estado,
                        'deudas_count' => $prop->debts->count()
                    ];
                })->toArray()
            ]);

            // SIEMPRE mostrar resultados, incluso si no hay deudas
            Log::info('Mostrando todas las propiedades del cliente:', ['client_id' => $client->id]);
            $settings = [
                'contact_phone'     => Setting::getValue('contact_phone'),
                'schedule_weekdays' => Setting::getValue('schedule_weekdays'),
                'schedule_saturday' => Setting::getValue('schedule_saturday'),
                'qr_image'          => Setting::getValue('qr_image'),
            ];
            
            return view('consultar-deuda', compact('client', 'properties', 'settings'));
        }

        public function nosotros()
        {
            return view('nosotros');
        }

        public function servicios()
        {
            return view('servicios');
        }

        public function tarifas()
        {
            $tarifas = Tariff::where('activo', true)
                            ->orderBy('precio_mensual')
                            ->get();
            
            return view('tarifas', compact('tarifas'));
        }

        public function contacto()
        {
            $settings = [
                'contact_address'   => Setting::getValue('contact_address'),
                'contact_phone'     => Setting::getValue('contact_phone'),
                'schedule_weekdays' => Setting::getValue('schedule_weekdays'),
                'schedule_saturday' => Setting::getValue('schedule_saturday'),
            ];
        
            return view('contacto', compact('settings'));
        }
    }