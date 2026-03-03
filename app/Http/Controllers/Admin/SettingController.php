<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin.users.index')->only('index');
        $this->middleware('can:admin.users.update')->only('update');
    }

    /**
     * Mostrar configuración general
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Cargar únicamente las claves necesarias (explícito y escalable)
        |--------------------------------------------------------------------------
        */
        $settings = [
            'contact_address'   => Setting::getValue('contact_address'),
            'contact_phone'     => Setting::getValue('contact_phone'),
            'contact_email'     => Setting::getValue('contact_email'),
            'schedule_weekdays' => Setting::getValue('schedule_weekdays'),
            'schedule_saturday' => Setting::getValue('schedule_saturday'),
            'qr_image'          => Setting::getValue('qr_image'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Actualizar configuración general
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'contact_address'   => 'required|string|max:255',
            'contact_phone'     => 'required|string|max:20',
            'contact_email'     => 'required|email:rfc,dns|max:255',
            'schedule_weekdays' => 'required|string|max:255',
            'schedule_saturday' => 'required|string|max:255',

            'qr_image' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:1024',
                'dimensions:min_width=200,min_height=200,max_width=1000,max_height=1000',
            ],
        ]);

        DB::transaction(function () use ($validated, $request) {

            /*
            |--------------------------------------------------------------------------
            | 1️⃣ Guardar campos dinámicamente
            |--------------------------------------------------------------------------
            */
            $keysToSave = [
                'contact_address',
                'contact_phone',
                'contact_email',
                'schedule_weekdays',
                'schedule_saturday',
            ];

            foreach ($keysToSave as $key) {
                Setting::setValue($key, $validated[$key]);
            }

            /*
            |--------------------------------------------------------------------------
            | 2️⃣ Manejo seguro del QR
            |--------------------------------------------------------------------------
            */
            if ($request->hasFile('qr_image')) {

                $currentQr = Setting::getValue('qr_image');

                if ($currentQr && Storage::disk('public')->exists($currentQr)) {
                    Storage::disk('public')->delete($currentQr);
                }

                $file = $request->file('qr_image');

                $filename = 'qr_' . now()->timestamp . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();

                $path = $file->storeAs('qr', $filename, 'public');

                Setting::setValue('qr_image', $path);
            }
        });

        return redirect()
            ->route('admin.settings.index')
            ->with('info', 'Configuración actualizada correctamente.');
    }
}