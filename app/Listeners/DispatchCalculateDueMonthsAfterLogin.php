<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Jobs\CalculateDueMonthsJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DispatchCalculateDueMonthsAfterLogin
{
    public function __construct()
    {
    }

    public function handle(Login $event)
    {
        // Evitar despachar si ya hay cache fresca (reduce trabajos)
        $globalCacheFlag = 'meses_adeudados_global_cached_v1';
        if (Cache::has($globalCacheFlag)) {
            Log::info("DispatchCalculateDueMonthsAfterLogin: cache global presente, no despachar job.");
            return;
        }

        // Despacha en background y crea un flag temporal para no despachar repetidamente
        CalculateDueMonthsJob::dispatch();

        // evita despachar repetidamente desde otros logins por X minutos
        Cache::put($globalCacheFlag, true, now()->addMinutes(10));
        Log::info("DispatchCalculateDueMonthsAfterLogin: Job despachado por user_id=" . optional($event->user)->id);
    }
}
