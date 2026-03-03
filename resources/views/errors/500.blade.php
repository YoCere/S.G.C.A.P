<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Descansando...</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 50%, #e0f7fa 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 24px;
            padding: 48px 40px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }

        /* Animación del grifo */
        .faucet-container {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 24px;
        }

        .faucet-icon {
            font-size: 72px;
            animation: wobble 2s ease-in-out infinite;
            display: block;
            line-height: 120px;
        }

        @keyframes wobble {
            0%, 100% { transform: rotate(-5deg); }
            50% { transform: rotate(5deg); }
        }

        /* Gotas de agua */
        .drops {
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 6px;
        }

        .drop {
            width: 8px;
            height: 8px;
            background: #38bdf8;
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            animation: drip 1.5s ease-in infinite;
            opacity: 0;
        }

        .drop:nth-child(1) { animation-delay: 0s; }
        .drop:nth-child(2) { animation-delay: 0.5s; }
        .drop:nth-child(3) { animation-delay: 1s; }

        @keyframes drip {
            0%   { opacity: 0; transform: translateY(0); }
            20%  { opacity: 1; }
            100% { opacity: 0; transform: translateY(20px); }
        }

        .title {
            font-size: 26px;
            font-weight: 800;
            color: #0c4a6e;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .subtitle {
            font-size: 16px;
            color: #475569;
            margin-bottom: 28px;
            line-height: 1.6;
        }

        .subtitle strong {
            color: #0369a1;
        }

        /* Barra de progreso */
        .progress-container {
            background: #e0f2fe;
            border-radius: 999px;
            height: 12px;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #38bdf8, #0ea5e9);
            border-radius: 999px;
            width: 0%;
            animation: fill 8s linear forwards;
        }

        @keyframes fill {
            0%   { width: 0%; }
            80%  { width: 85%; }
            100% { width: 95%; }
        }

        .progress-text {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 28px;
        }

        .countdown {
            font-weight: 700;
            color: #0369a1;
        }

        /* Botón */
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            color: white;
            font-size: 16px;
            font-weight: 700;
            padding: 14px 32px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 15px rgba(14,165,233,0.4);
            width: 100%;
            margin-bottom: 16px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14,165,233,0.5);
        }

        .footer-note {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 8px;
        }

        .badge {
            display: inline-block;
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #16a34a;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 999px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="card">

    <span class="badge">🌿 Comité de Agua La Grampa</span>

    <!-- Grifo animado -->
    <div class="faucet-container">
        <span class="faucet-icon">🚰</span>
        <div class="drops">
            <div class="drop"></div>
            <div class="drop"></div>
            <div class="drop"></div>
        </div>
    </div>

    <h1 class="title">¡El sistema estaba<br>tomando un descanso!</h1>

    <p class="subtitle">
        No se preocupe, <strong>ya lo estamos despertando.</strong><br>
        En unos segundos todo estará listo,<br>como el agua fresca por las mañanas. 💧
    </p>

    <!-- Barra de progreso -->
    <div class="progress-container">
        <div class="progress-bar"></div>
    </div>
    <p class="progress-text">
        Recargando automáticamente en <span class="countdown" id="countdown">8</span> segundos...
    </p>

    <button class="btn" onclick="recargar()">
        💧 Recargar ahora
    </button>

    <p class="footer-note">
        Si el problema persiste, contáctenos por WhatsApp.
    </p>

</div>

<script>
    // Cuenta regresiva y recarga automática
    let segundos = 8;
    const countdownEl = document.getElementById('countdown');

    const intervalo = setInterval(function() {
        segundos--;
        countdownEl.textContent = segundos;

        if (segundos <= 0) {
            clearInterval(intervalo);
            recargar();
        }
    }, 1000);

    function recargar() {
        window.location.reload();
    }
</script>

</body>
</html>