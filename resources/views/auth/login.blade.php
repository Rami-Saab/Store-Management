<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <script>
        (function () {
            try {
                const storedTheme = localStorage.getItem('ui_theme');
                const theme = storedTheme === 'soft' ? 'soft' : 'glass';
                if (theme === 'glass') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else {
                    document.documentElement.removeAttribute('data-theme');
                }
            } catch (e) {}
        })();
    </script>
    <title>Login | Store Branch Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
            --bg-a: #f3f4f6; 
            --bg-b: #fafafa; 
            --bg-c: #eff6ff; 
            --card: #ffffff;
            --card-border: rgba(16, 24, 40, 0.08);
            --ink: #101828;
            --muted: #344054;
            --muted-2: #344054;
            --placeholder: #98a2b3;
            --input-bg: #f9fafb;
            --input-border: #d0d5dd;
            --ring: rgba(59, 130, 246, 0.16);
            --danger-bg: #fef2f2;
            --danger-line: rgba(239, 68, 68, 0.38);
            --danger-ring: rgba(239, 68, 68, 0.22);
            --danger-ink: #b91c1c;
            --shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        html[data-theme="dark"] {
            color-scheme: dark;
            --bg-a: #030712; 
            --bg-b: #111827; 
            --bg-c: #172554; 
            --card: #111827;
            --card-border: #1f2937; 
            --ink: #f9fafb; 
            --muted: #9ca3af; 
            --muted-2: #6b7280; 
            --input-bg: #1f2937; 
            --input-border: #4b5563; 
            --ring: rgba(96, 165, 250, 0.55); 
            --danger-bg: rgba(127, 29, 29, 0.24);
            --danger-line: rgba(248, 113, 113, 0.45);
            --danger-ring: rgba(248, 113, 113, 0.18);
            --danger-ink: #fca5a5;
            --shadow: 0 30px 80px rgba(0, 0, 0, 0.40);
        }
        * { box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            margin: 0;
            font-family: "Poppins", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background: linear-gradient(180deg, var(--bg-a) 0%, var(--bg-b) 48%, var(--bg-c) 100%);
            color: var(--ink);
            overflow-x: hidden;
        }
        .page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            position: relative;
            overflow: hidden;
        }
        .decor {
            position: absolute;
            inset: 0;
            pointer-events: none;
            display: none;
        }
        .blob {
            position: absolute;
            width: 420px;
            height: 420px;
            border-radius: 999px;
            filter: blur(60px);
        }
        .blob-a {
            top: -120px;
            left: -140px;
            background: transparent;
        }
        .blob-b {
            bottom: -120px;
            right: -140px;
            background: transparent;
        }
        html[data-theme="dark"] .blob-a {
            background: transparent;
        }
        html[data-theme="dark"] .blob-b {
            background: transparent;
        }
        .shell { width: min(100%, 1024px); position: relative; z-index: 2; }
        .login-theme-toggle {
            position: absolute;
            top: 18px;
            right: 18px;
            width: 44px;
            height: 44px;
            border-radius: 14px;
            border: 1px solid rgba(16, 24, 40, 0.14);
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(14px) saturate(180%);
            -webkit-backdrop-filter: blur(14px) saturate(180%);
            color: rgba(16, 24, 40, 0.88);
            display: grid;
            place-items: center;
            padding: 0;
            cursor: pointer;
            z-index: 5;
            transition: transform 180ms ease, background 180ms ease, border-color 180ms ease, color 180ms ease;
        }
        html[data-theme="dark"] .login-theme-toggle {
            border-color: rgba(255, 255, 255, 0.16);
            background: rgba(17, 24, 39, 0.58);
            color: rgba(255, 255, 255, 0.90);
        }
        .login-theme-toggle:hover,
        .login-theme-toggle:focus {
            outline: none;
            transform: translateY(-1px);
            background: rgba(255, 255, 255, 0.82);
        }
        html[data-theme="dark"] .login-theme-toggle:hover,
        html[data-theme="dark"] .login-theme-toggle:focus {
            background: rgba(17, 24, 39, 0.70);
        }
        .login-theme-icon {
            width: 20px;
            height: 20px;
            display: none;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .login-theme-icon--soft { display: block; }
        html[data-theme="dark"] .login-theme-icon--soft { display: none; }
        html[data-theme="dark"] .login-theme-icon--glass { display: block; }
        .card {
            background: var(--card);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr;
        }
        @media (min-width: 768px) {
            .grid { grid-template-columns: 1fr 1fr; }
        }
        .brand {
            position: relative;
            padding: 48px;
            color: #fff;
            background: #0f172a;
            overflow: hidden;
        }
        html[data-theme="dark"] .brand {
            background: #0f172a;
        }
        .brand::before {
            content: "";
            position: absolute;
            inset: 0;
            opacity: 0.10;
            background-image: none;
            pointer-events: none;
        }
        .brand-inner {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 520px;
        }
        .logo-wrap {
            position: relative;
            width: 260px;
            height: 260px;
            display: grid;
            place-items: center;
        }
        .logo {
            width: 96px;
            height: 96px;
            border-radius: 16px;
            background: #2563eb;
            display: grid;
            place-items: center;
            box-shadow: 0 28px 70px rgba(0,0,0,0.35);
            transition: transform 260ms ease;
        }
        .logo-wrap:hover .logo { transform: scale(1.08); }
        .icon-store {
            width: 44px;
            height: 44px;
            fill: none;
            stroke: #fff;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .mini {
            position: absolute;
            width: 48px;
            height: 48px;
            border-radius: 8px;
            background: rgba(255,255,255,0.20);
            box-shadow: 0 16px 40px rgba(0,0,0,0.28);
            display: grid;
            place-items: center;
            transform: translate(-50%, -50%);
            animation: float 3.2s ease-in-out infinite;
        }
        .mini svg {
            width: 24px;
            height: 24px;
            fill: none;
            stroke: rgba(255,255,255,0.95);
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .brand-text {
            margin-top: 128px; 
            text-align: center;
        }
        .brand-text h2 {
            margin: 0 0 10px;
            font-size: 30px;
            line-height: 1.2;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .brand-text p {
            margin: 0;
            font-size: 13px;
            line-height: 1.7;
            color: rgba(203, 213, 225, 0.80);
        }
        .form {
            padding: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .form-inner { width: min(100%, 420px); }
        .form-head h3 {
            margin: 0 0 6px;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .form-head p {
            margin: 0 0 24px;
            font-size: 14px;
            color: var(--muted);
        }
        .field { margin-bottom: 16px; }
        .field label {
            display: block;
            margin: 0 0 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--muted-2);
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid var(--input-border);
            background: var(--input-bg);
            color: var(--ink);
            outline: none;
            transition: box-shadow 160ms ease, border-color 160ms ease;
        }
        input[type="text"]::placeholder,
        input[type="password"]::placeholder {
            color: var(--placeholder);
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px var(--ring);
        }
        .field.has-error label {
            color: var(--danger-ink);
        }
        .field.has-error input {
            border-color: var(--danger-line);
            background: var(--danger-bg);
            color: var(--danger-ink);
        }
        .field.has-error input:focus {
            border-color: rgba(239, 68, 68, 0.82);
            box-shadow: 0 0 0 4px var(--danger-ring);
        }
        .pwd { position: relative; }
        .pwd input { padding-right: 50px; }
        .pwd.has-error .pwd-btn {
            color: var(--danger-ink);
        }
        .pwd-btn {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            padding: 6px;
            cursor: pointer;
            color: rgba(107, 114, 128, 0.85);
        }
        html[data-theme="dark"] .pwd-btn { color: rgba(209, 213, 219, 0.75); }
        .pwd-btn:hover { color: rgba(75, 85, 99, 1); }
        html[data-theme="dark"] .pwd-btn:hover { color: rgba(229, 231, 235, 1); }
        .pwd-btn svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .pwd-btn .eye-off { display: none; }
        .pwd-btn.is-visible .eye { display: none; }
        .pwd-btn.is-visible .eye-off { display: inline; }
        .feedback {
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid var(--danger-line);
            background: var(--danger-bg);
            color: var(--danger-ink);
            font-size: 13px;
            line-height: 1.45;
            margin-bottom: 16px;
        }
        .field-error {
            margin: 8px 0 0;
            font-size: 12px;
            font-weight: 600;
            color: var(--danger-ink);
        }
        .login-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            min-width: 240px;
            max-width: 360px;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(251, 146, 60, 0.35);
            background: #fff7ed;
            color: #9a3412;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
            z-index: 9999;
            animation: toastIn 0.25s ease both;
        }
        html[data-theme="dark"] .login-toast {
            background: rgba(124, 45, 18, 0.12);
            border-color: rgba(234, 88, 12, 0.35);
            color: #fdba74;
        }
        .login-toast.login-toast--danger {
            border-color: rgba(239, 68, 68, 0.35);
            background: #fef2f2;
            color: #b91c1c;
        }
        html[data-theme="dark"] .login-toast.login-toast--danger {
            background: rgba(127, 29, 29, 0.22);
            border-color: rgba(248, 113, 113, 0.40);
            color: #fca5a5;
        }
        .login-toast__icon {
            width: 32px;
            height: 32px;
            border-radius: 12px;
            background: rgba(251, 146, 60, 0.18);
            display: grid;
            place-items: center;
            flex: none;
            color: #ea580c;
        }
        .login-toast.login-toast--danger .login-toast__icon {
            background: rgba(239, 68, 68, 0.12);
            color: #dc2626;
        }
        html[data-theme="dark"] .login-toast.login-toast--danger .login-toast__icon {
            background: rgba(248, 113, 113, 0.14);
            color: #fca5a5;
        }
        .login-toast__icon svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            display: block;
        }
        .login-toast__text {
            font-size: 13px;
            line-height: 1.5;
            font-weight: 600;
        }
        .login-toast__close {
            border: none;
            background: transparent;
            color: inherit;
            font-size: 16px;
            line-height: 1;
            cursor: pointer;
            padding: 2px;
            margin-left: auto;
        }
        @keyframes toastIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .remember {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 8px 0 18px;
            color: var(--muted);
            font-size: 13px;
        }
        .remember input {
            width: 16px;
            height: 16px;
            accent-color: #2563eb;
        }
        .submit {
            width: 100%;
            border: none;
            border-radius: 14px;
            padding: 14px 16px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            background: #2563eb;
            box-shadow: 0 18px 44px rgba(15, 23, 42, 0.20);
            transition: transform 160ms ease, box-shadow 160ms ease, filter 160ms ease;
        }
        html[data-theme="dark"] .submit { background: #2563eb; }
        .submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 22px 60px rgba(15, 23, 42, 0.26);
            filter: brightness(1.03);
        }
        .extra {
            margin-top: 18px;
            text-align: center;
        }
        .extra a {
            font-size: 13px;
            color: var(--primary);
            text-decoration: none;
        }
        .extra a:hover { text-decoration: underline; }
        @keyframes float {
            0%, 100% { transform: translate(-50%, -50%) translateY(0); }
            50% { transform: translate(-50%, -50%) translateY(-10px); }
        }
        @media (max-width: 640px) {
            .brand, .form { padding: 28px 20px; }
            .brand-inner { min-height: auto; }
            .brand-text { margin-top: 92px; }
            .logo-wrap { width: 220px; height: 220px; }
        }
    </style>
</head>
@php
    $loginErrors = $errors->getBag('login')->any() ? $errors->getBag('login') : $errors->getBag('default');
    $orbitAngles = [0, 45, 90, 135, 180, 225, 270, 315];
    $orbitRadius = 80;
    $nameError = $loginErrors->first('name');
    $passwordError = $loginErrors->first('password');
    $toastMessage = session('warning') ?: ($passwordError ?: ($loginErrors->any() ? $loginErrors->first() : null));
    $toastClass = session('warning') ? '' : ($toastMessage ? 'login-toast--danger' : '');
@endphp
<body>
    <main class="page">
        <div class="decor" aria-hidden="true">
            <div class="blob blob-a"></div>
            <div class="blob blob-b"></div>
        </div>
        @if ($toastMessage)
            <div class="login-toast {{ $toastClass }}" role="status" aria-live="polite" data-login-toast>
                <span class="login-toast__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 9v4"></path>
                        <path d="M12 17h.01"></path>
                        <path d="M10.3 3.7l-7.7 13a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3l-7.7-13a2 2 0 0 0-3.4 0z"></path>
                    </svg>
                </span>
                <span class="login-toast__text">{{ $toastMessage }}</span>
                <button type="button" class="login-toast__close" aria-label="Dismiss notification" data-login-toast-close>&times;</button>
            </div>
        @endif
        <div class="shell">
            <button type="button" class="login-theme-toggle" data-login-theme-toggle aria-label="Toggle theme">
                <svg class="login-theme-icon login-theme-icon--soft" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v2"></path>
                    <path d="M12 20v2"></path>
                    <path d="M4.93 4.93l1.41 1.41"></path>
                    <path d="M17.66 17.66l1.41 1.41"></path>
                    <path d="M2 12h2"></path>
                    <path d="M20 12h2"></path>
                    <path d="M6.34 17.66l-1.41 1.41"></path>
                    <path d="M19.07 4.93l-1.41 1.41"></path>
                </svg>
                <svg class="login-theme-icon login-theme-icon--glass" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3A7 7 0 0 0 21 12.79z"></path>
                </svg>
            </button>
            <div class="card">
                <div class="grid">
                    <section class="brand">
                        <div class="brand-inner">
                            <div class="logo-wrap" aria-hidden="true">
                                <div class="logo">
                                    <svg class="icon-store" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"></path>
                                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                                        <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"></path>
                                        <path d="M2 7h20"></path>
                                        <path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"></path>
                                    </svg>
                                </div>
                                @foreach ($orbitAngles as $i => $angle)
                                    @php
                                        $rad = deg2rad($angle);
                                        $x = cos($rad) * $orbitRadius;
                                        $y = sin($rad) * $orbitRadius;
                                        $duration = 3 + ($i * 0.2);
                                        $delay = $i * 0.1;
                                    @endphp
                                    <div class="mini" style="left: calc(50% + {{ $x }}px); top: calc(50% + {{ $y }}px); animation-duration: {{ $duration }}s; animation-delay: {{ $delay }}s;">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"></path>
                                            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                                            <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"></path>
                                            <path d="M2 7h20"></path>
                                            <path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"></path>
                                        </svg>
                                    </div>
                                @endforeach
                            </div>
                            <div class="brand-text">
                                <h2>
                                    Store Branch
                                    <br>
                                    Management System
                                </h2>
                                <p>Manage branches efficiently and professionally</p>
                            </div>
                        </div>
                    </section>
                    <section class="form">
                        <div class="form-inner">
                            <div class="form-head">
                                <h3>Welcome back</h3>
                                <p>Please sign in to continue</p>
                            </div>
                            <form method="POST" action="{{ route('login.attempt', [], false) }}" class="login-form" novalidate autocomplete="off">
                                @csrf
                                <div class="field {{ $nameError ? 'has-error' : '' }}">
                                    <label for="name">Name</label>
                                    <input
                                        id="name"
                                        type="text"
                                        name="name"
                                        value="{{ old('name') }}"
                                        placeholder="Username"
                                        required
                                        autocomplete="off"
                                        autocapitalize="off"
                                        spellcheck="false"
                                        aria-invalid="{{ $nameError ? 'true' : 'false' }}"
                                        data-name-input
                                        autofocus
                                    >
                                    @if ($nameError)
                                        <p class="field-error">{{ $nameError }}</p>
                                    @endif
                                </div>
                                <div class="field {{ $passwordError ? 'has-error' : '' }}">
                                    <label for="password">Password</label>
                                    <div class="pwd {{ $passwordError ? 'has-error' : '' }}">
                                        <input
                                            id="password"
                                            type="password"
                                            name="password"
                                            placeholder="Password"
                                            required
                                            value=""
                                            autocomplete="new-password"
                                            aria-invalid="{{ $passwordError ? 'true' : 'false' }}"
                                            data-password-input
                                        >
                                        <button type="button" class="pwd-btn" data-password-toggle data-target="password" aria-label="Show password">
                                            <svg class="eye" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                            <svg class="eye-off" viewBox="0 0 24 24" aria-hidden="true">
                                                <line x1="2" y1="2" x2="22" y2="22"></line>
                                                <path d="M10.733 5.08A10.784 10.784 0 0 1 12 5c7 0 11 7 11 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                                                <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                                                <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    @if ($passwordError)
                                        <p class="field-error">{{ $passwordError }}</p>
                                    @endif
                                </div>
                                <label class="remember" for="remember-ui">
                                    <input id="remember-ui" type="checkbox" name="remember" value="1" @checked(old('remember'))>
                                    <span>Remember me</span>
                                </label>
                                <button type="submit" class="submit">Login</button>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>
    <script>
        (() => {
            const TAB_PARAM = 'tab';
            const STORAGE_KEY = 'rg_tab_id';
            const SHARE_KEY = 'rg_tab_share';
            const AUTH_TOKEN_KEY = 'rg_auth_token';
            const AUTH_TOKEN_CHECK_KEY = 'rg_auth_token_checked_at';
            function sanitizeTabId(value) {
                if (typeof value !== 'string') return null;
                const trimmed = value.trim();
                if (!trimmed) return null;
                if (!/^[A-Za-z0-9_-]{6,64}$/.test(trimmed)) return null;
                return trimmed;
            }
            function generateTabId() {
                const bytes = new Uint8Array(8);
                if (window.crypto && window.crypto.getRandomValues) {
                    window.crypto.getRandomValues(bytes);
                } else {
                    for (let i = 0; i < bytes.length; i += 1) {
                        bytes[i] = Math.floor(Math.random() * 256);
                    }
                }
                return Array.from(bytes).map((b) => b.toString(16).padStart(2, '0')).join('');
            }
            function readShareIntent() {
                try {
                    const raw = localStorage.getItem(SHARE_KEY);
                    if (!raw) return null;
                    const parsed = JSON.parse(raw);
                    const sharedId = sanitizeTabId(parsed && parsed.tabId);
                    const ts = parsed && typeof parsed.ts === 'number' ? parsed.ts : 0;
                    if (!sharedId || ts <= 0) return null;
                    if ((Date.now() - ts) > 8000) return null;
                    return { tabId: sharedId };
                } catch (e) {
                    return null;
                }
            }
            function clearShareIntent() {
                try {
                    localStorage.removeItem(SHARE_KEY);
                } catch (e) {
                }
            }
            sessionStorage.removeItem(AUTH_TOKEN_KEY);
            sessionStorage.removeItem(AUTH_TOKEN_CHECK_KEY);
            const url = new URL(window.location.href);
            const fromUrl = sanitizeTabId(url.searchParams.get(TAB_PARAM));
            const fromStorage = sanitizeTabId(sessionStorage.getItem(STORAGE_KEY));
            let tabId = null;
            if (fromStorage) {
                tabId = fromStorage;
            } else {
                const share = fromUrl ? readShareIntent() : null;
                if (fromUrl && share && share.tabId === fromUrl) {
                    tabId = fromUrl;
                    clearShareIntent();
                } else {
                    tabId = generateTabId();
                }
            }
            if (tabId) {
                sessionStorage.setItem(STORAGE_KEY, tabId);
            }
            const currentParam = sanitizeTabId(url.searchParams.get(TAB_PARAM));
            if (currentParam !== tabId && tabId) {
                url.searchParams.set(TAB_PARAM, tabId);
                window.location.replace(url.toString());
                return;
            }
            const form = document.querySelector('form.login-form');
            if (form && tabId) {
                let input = form.querySelector('input[name="tab"]');
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'tab';
                    form.appendChild(input);
                }
                input.value = tabId;
            }
        })();
        (() => {
            const nameInput = document.querySelector('[data-name-input]');
            const passwordInput = document.querySelector('[data-password-input]');
            const passwordToggle = document.querySelector('[data-password-toggle]');
            const hasNameError = @json(!empty($nameError));
            const hasPasswordError = @json(!empty($passwordError));
            function clearNameField() {
                if (!nameInput) return;
                nameInput.value = '';
                nameInput.setAttribute('value', '');
            }
            function clearPasswordField() {
                if (!passwordInput) return;
                passwordInput.value = '';
                passwordInput.setAttribute('value', '');
                passwordInput.type = 'password';
                if (passwordToggle) {
                    passwordToggle.classList.remove('is-visible');
                    passwordToggle.setAttribute('aria-label', 'Show password');
                }
            }
            function clearLoginFields() {
                clearNameField();
                clearPasswordField();
            }
            if (passwordInput && hasPasswordError) {
                clearPasswordField();
                requestAnimationFrame(() => {
                    clearPasswordField();
                    passwordInput.focus();
                });
                window.addEventListener('pageshow', clearPasswordField);
                setTimeout(clearPasswordField, 120);
            } else if (!hasNameError) {
                clearLoginFields();
                requestAnimationFrame(() => {
                    clearLoginFields();
                    if (nameInput) {
                        nameInput.focus();
                    }
                });
                window.addEventListener('pageshow', clearLoginFields);
                setTimeout(clearLoginFields, 120);
            }
            document.querySelectorAll('[data-password-toggle]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const target = document.getElementById(btn.dataset.target);
                    if (!target) return;
                    const isVisible = target.type === 'text';
                    target.type = isVisible ? 'password' : 'text';
                    btn.classList.toggle('is-visible', !isVisible);
                    btn.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
                    target.focus();
                });
            });
        })();
        (() => {
            const toast = document.querySelector('[data-login-toast]');
            if (!toast) return;
            const closeBtn = toast.querySelector('[data-login-toast-close]');
            const hide = () => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-6px)';
                setTimeout(() => toast.remove(), 200);
            };
            if (closeBtn) {
                closeBtn.addEventListener('click', hide);
            }
            setTimeout(hide, 4500);
        })();
        (() => {
            const STORAGE_KEY = 'ui_theme';
            const button = document.querySelector('[data-login-theme-toggle]');
            if (!button) return;
            function applyTheme(theme) {
                const next = theme === 'soft' ? 'soft' : 'glass';
                try {
                    localStorage.setItem(STORAGE_KEY, next);
                } catch (e) {}
                if (next === 'glass') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else {
                    document.documentElement.removeAttribute('data-theme');
                }
                button.setAttribute('aria-pressed', next === 'glass' ? 'true' : 'false');
                button.setAttribute('title', next === 'glass' ? 'Glass Blur' : 'Soft Light');
            }
            const isGlass = document.documentElement.getAttribute('data-theme') === 'dark';
            button.setAttribute('aria-pressed', isGlass ? 'true' : 'false');
            button.setAttribute('title', isGlass ? 'Glass Blur' : 'Soft Light');
            button.addEventListener('click', () => {
                const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                applyTheme(isDark ? 'soft' : 'glass');
            });
        })();
    </script>
</body>
</html>
