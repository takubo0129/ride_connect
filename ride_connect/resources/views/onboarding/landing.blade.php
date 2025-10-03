<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ride Connect</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --logo-duration: 3.4s; }

        @keyframes wheel-enter {
            0% { transform: translate3d(-140%, 0, 0) rotate(-540deg) scale(0.9); opacity: 0; }
            35% { opacity: 1; }
            65% { transform: translate3d(-4%, 0, 0) rotate(-4deg) scale(1.02); }
            78% { transform: translate3d(0, 0, 0) rotate(0deg) scale(1); }
            100% { transform: translate3d(0, 0, 0) rotate(0deg) scale(1); opacity: 1; }
        }

        @keyframes core-enter {
            0% { transform: translate3d(140%, 0, 0) rotate(540deg) scale(0.9); opacity: 0; }
            35% { opacity: 1; }
            65% { transform: translate3d(4%, 0, 0) rotate(4deg) scale(1.02); }
            78% { transform: translate3d(0, 0, 0) rotate(0deg) scale(1); }
            100% { transform: translate3d(0, 0, 0) rotate(0deg) scale(1); opacity: 1; }
        }

        @keyframes final-reveal {
            0%, 74% { opacity: 0; transform: scale(0.96); }
            84% { opacity: 1; transform: scale(1.02); }
            100% { opacity: 1; transform: scale(1); }
        }

        @keyframes shadow-pulse {
            0% { transform: scaleX(0.4); opacity: 0; }
            40% { opacity: 0.4; }
            70% { transform: scaleX(1); opacity: 0.45; }
            100% { transform: scaleX(0.9); opacity: 0.25; }
        }

        body { background: #ffffff; }

        .splash-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 2.5rem;
        }

        .logo-stage {
            position: relative;
            width: min(68vw, 17rem);
            aspect-ratio: 1 / 1;
        }

        .logo-stage::after {
            content: "";
            position: absolute;
            inset: auto 16%;
            bottom: -12%;
            height: 18%;
            background: radial-gradient(circle, rgba(15, 118, 110, 0.4), transparent 60%);
            border-radius: 999px;
            transform-origin: center;
            filter: blur(10px);
            animation: shadow-pulse var(--logo-duration) ease forwards;
        }

        .logo-piece {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-piece img { width: 100%; height: 100%; object-fit: contain; }

        .logo-wheel { z-index: 1; animation: wheel-enter var(--logo-duration) cubic-bezier(0.19, 1, 0.22, 1) forwards; }
        .logo-core { z-index: 2; animation: core-enter var(--logo-duration) cubic-bezier(0.19, 1, 0.22, 1) forwards; }
        .logo-final { z-index: 3; animation: final-reveal var(--logo-duration) ease forwards; }

        .splash-caption {
            font-size: 0.95rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: rgba(20, 83, 45, 0.72);
            font-weight: 600;
            text-align: center;
        }
    </style>
</head>
<body class="text-slate-900">
    <div class="splash-wrapper" x-data="{ redirect: '{{ $redirectUrl }}' }" x-init="setTimeout(() => window.location.href = redirect, 3400)">
        <div class="logo-stage">
            <div class="logo-piece logo-wheel">
                <img src="{{ asset('images/ride-connect-wheel.svg') }}" alt="Ride Connect ホイール">
            </div>
            <div class="logo-piece logo-core">
                <img src="{{ asset('images/ride-connect-core.svg') }}" alt="Ride Connect コア">
            </div>
            <div class="logo-piece logo-final">
                <img src="{{ asset('images/ride-connect-logo.svg') }}" alt="Ride Connect ロゴ">
            </div>
        </div>
        <p class="splash-caption">Ride Together, Connect Further</p>
    </div>
</body>
</html>
