<!DOCTYPE html>
<html lang="en" dir="ltr" class="theme-glass" data-ui-theme="glass">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>@yield('title', 'Store Management System')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @stack('head')
    <script>
        (function () {
            try {
                const storedTheme = localStorage.getItem('ui_theme');
                const theme = storedTheme === 'soft' ? 'soft' : 'glass';
                document.documentElement.dataset.uiTheme = theme;
                document.documentElement.classList.toggle('theme-glass', theme === 'glass');
                document.documentElement.classList.toggle('theme-soft', theme === 'soft');
            } catch (e) {
                document.documentElement.dataset.uiTheme = 'glass';
                document.documentElement.classList.add('theme-glass');
                document.documentElement.classList.remove('theme-soft');
            }
        })();
    </script>
    <style>
        html.theme-soft {
            color-scheme: light;
            --bg: linear-gradient(180deg, #f4f6f8 0%, #edeff3 100%);
            --surface: #ffffff;
            --surface-strong: #ffffff;
            --surface-soft: #f9fafb;
            --ink: #101828;
            --muted: #344054;
            --placeholder: #98a2b3;
            --line: #d0d5dd;
            --primary: #3b82f6;
            --primary-deep: #2563eb;
            --primary-rgb: 59, 130, 246;
            --primary-tint: #93c5fd;
            --primary-tint-rgb: 147, 197, 253;
            --accent: #f59e0b;
            --success: #22c55e;
            --danger: #ef4444;
            --glass-surface: var(--surface);
            --glass-surface-strong: var(--surface-strong);
            --glass-border: rgba(226, 232, 240, 0.9);
            --glass-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 18px 44px rgba(0, 0, 0, 0.10);
            --shadow-md: 0 12px 28px rgba(0, 0, 0, 0.08);
            --radius-xl: 28px;
            --radius-lg: 22px;
            --radius-md: 16px;
        }
        html.theme-glass {
            color-scheme: dark;
            --bg: linear-gradient(-45deg, #0b1220, #0f1b2d, #172554, #0b2a5b, #0b3a77);
            --surface: rgba(15, 23, 42, 0.72);
            --surface-strong: rgba(15, 23, 42, 0.82);
            --surface-soft: rgba(15, 23, 42, 0.56);
            --ink: rgba(255, 255, 255, 0.92);
            --muted: rgba(226, 232, 240, 0.74);
            --placeholder: rgba(226, 232, 240, 0.55);
            --line: rgba(148, 163, 184, 0.30);
            --primary: #3b82f6;
            --primary-deep: #2563eb;
            --primary-rgb: 59, 130, 246;
            --primary-tint: #93c5fd;
            --primary-tint-rgb: 147, 197, 253;
            --accent: #f59e0b;
            --success: #22c55e;
            --danger: #ef4444;
            --glass-surface: rgba(15, 23, 42, 0.66);
            --glass-surface-strong: rgba(15, 23, 42, 0.78);
            --glass-border: rgba(255, 255, 255, 0.16);
            --glass-shadow:
                0 10px 36px 0 rgba(0, 0, 0, 0.55),
                0 3px 12px 0 rgba(0, 0, 0, 0.28),
                inset 0 1px 1px 0 rgba(255, 255, 255, 0.08);
            --shadow-lg: 0 26px 70px rgba(0, 0, 0, 0.62);
            --shadow-md: 0 16px 44px rgba(0, 0, 0, 0.52);
            --radius-xl: 28px;
            --radius-lg: 22px;
            --radius-md: 16px;
        }
        * {
            box-sizing: border-box;
        }
        body {
            min-height: 100vh;
            margin: 0;
            background: var(--bg);
            color: var(--ink);
            font-family: "Inter", system-ui, -apple-system, "Segoe UI", Tahoma, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .page-loader {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.18s ease, visibility 0.18s ease;
            z-index: 9999;
        }
        .page-loader.is-visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        .page-loader__spinner {
            width: 46px;
            height: 46px;
            border-radius: 999px;
            border: 4px solid rgba(148, 163, 184, 0.35);
            border-top-color: var(--primary);
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.12);
            background: #ffffff;
            animation: pageLoaderSpin 0.75s linear infinite;
        }
        html.theme-glass body {
            background: var(--bg);
            background-size: 400% 400%;
            animation: glassGradientShift 20s ease infinite;
        }
        html.theme-glass .page-loader {
            background: var(--bg);
            background-size: 400% 400%;
            animation: glassGradientShift 20s ease infinite;
        }
        html.theme-glass .app-main {
            background: transparent;
        }
        html.theme-glass .page-loader__spinner {
            background: rgba(255, 255, 255, 0.10);
            border-color: rgba(255, 255, 255, 0.14);
            border-top-color: var(--primary);
            box-shadow: 0 18px 34px rgba(0, 0, 0, 0.35);
        }
        @keyframes pageLoaderSpin {
            to {
                transform: rotate(360deg);
            }
        }
        @keyframes glassGradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        @media (prefers-reduced-motion: reduce) {
            html.theme-glass body,
            html.theme-glass .page-loader {
                animation: none;
            }
        }
        a {
            text-decoration: none;
        }
        .app-shell {
            display: grid;
            grid-template-areas:
                "sidebar"
                "main";
            grid-template-columns: minmax(0, 1fr);
            grid-template-rows: auto 1fr;
            min-height: 100vh;
        }
        .main-sidebar {
            grid-area: sidebar;
            position: sticky;
            top: 0;
            z-index: 30;
            margin-bottom: 0;
            min-height: 82px;
            padding: 0.95rem 1.35rem;
            border-bottom: 1px solid rgba(var(--primary-tint-rgb), 0.22);
            background: #123244;
            box-shadow:
                inset 0 -1px 0 rgba(var(--primary-tint-rgb), 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.05);
            display: grid;
            grid-template-columns: max-content minmax(0, 1fr) max-content;
            align-items: center;
            gap: 0.95rem 1.2rem;
            overflow: visible;
        }
        html.theme-glass .main-sidebar {
            background: rgba(255, 255, 255, 0.06);
            border-bottom-color: rgba(255, 255, 255, 0.12);
            border-right: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow:
                4px 0 24px 0 rgba(0, 0, 0, 0.10),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
        }
        .main-sidebar::before {
            content: "";
            position: absolute;
            inset: 0;
            background: none;
            pointer-events: none;
        }
        .app-main {
            grid-area: main;
            padding: 1.35rem 1.5rem 1.5rem;
            min-width: 0;
            background: var(--bg);
        }
        .content-topbar {
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.95rem 1.5rem;
            margin: 0 0 1.35rem;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
            position: sticky;
            top: 1rem;
            z-index: 60;
        }
        html.theme-glass .content-topbar {
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            box-shadow:
                0 4px 20px 0 rgba(0, 0, 0, 0.15),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.10);
        }
        .content-topbar-search-row {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            flex: 1 1 auto;
            min-width: 0;
        }
        .content-topbar-search {
            flex: 1 1 auto;
            max-width: 720px;
            min-width: min(520px, 100%);
            margin: 0;
        }
        .content-topbar-search-inner {
            position: relative;
        }
        .content-topbar-search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(100, 116, 139, 0.8);
            pointer-events: none;
        }
        .content-topbar-search-icon svg {
            width: 18px;
            height: 18px;
            display: block;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .content-topbar-search-input {
            width: 100%;
            min-height: 44px;
            padding: 0.7rem 1rem 0.7rem 2.6rem;
            border-radius: 999px;
            border: 2px solid var(--line);
            background: var(--surface-soft);
            color: var(--ink);
            font-size: 0.92rem;
            font-weight: 600;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }
        .content-topbar-search-input::placeholder {
            color: var(--placeholder);
            font-weight: 500;
        }
        .content-topbar-search-input:focus {
            border-color: var(--primary);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.12);
        }
        html.theme-glass .content-topbar-search-input:focus {
            background: var(--surface-strong);
            box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.22);
        }
        .content-topbar-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.7rem;
            flex: 0 0 auto;
            min-width: max-content;
        }
        .content-topbar-actions .topbar-avatar {
            order: 1;
        }
        .content-topbar-actions .topbar-user-copy {
            order: 2;
        }
        .topbar-theme-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            border: 1px solid rgba(var(--primary-tint-rgb), 0.16);
            background: rgba(255, 255, 255, 0.04);
            color: rgba(243, 247, 251, 0.88);
            display: grid;
            place-items: center;
            padding: 0;
            transition: background 0.18s ease, border-color 0.18s ease, transform 0.18s ease;
        }
        .topbar-theme-btn:hover,
        .topbar-theme-btn:focus {
            background: rgba(var(--primary-tint-rgb), 0.12);
            border-color: rgba(var(--primary-tint-rgb), 0.26);
            color: #ffffff;
            transform: translateY(-1px);
        }
        .topbar-theme-icon {
            width: 18px;
            height: 18px;
            display: none;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        html.theme-soft .topbar-theme-icon--soft {
            display: block;
        }
        html.theme-glass .topbar-theme-icon--glass {
            display: block;
        }
        .content-topbar .topbar-theme-btn {
            border-color: var(--line);
            background: var(--surface-soft);
            color: var(--ink);
        }
        .content-topbar .topbar-theme-btn:hover,
        .content-topbar .topbar-theme-btn:focus {
            background: var(--surface);
            border-color: rgba(var(--primary-rgb), 0.28);
            color: var(--ink);
            box-shadow: 0 16px 28px rgba(15, 23, 42, 0.08);
        }
        .content-topbar-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            height: 40px;
            padding: 0 0.95rem;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: var(--surface-soft);
            color: var(--ink);
            font-size: 0.85rem;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, border-color 0.18s ease, color 0.18s ease;
        }
        .content-topbar-pill:hover,
        .content-topbar-pill:focus {
            background: var(--surface);
            border-color: rgba(var(--primary-rgb), 0.28);
            box-shadow: 0 16px 28px rgba(15, 23, 42, 0.08);
            transform: translateY(-1px);
            color: var(--ink);
        }
        .content-topbar-pill.is-active {
            background: rgba(var(--primary-rgb), 0.12);
            border-color: rgba(var(--primary-rgb), 0.28);
            color: #1d4ed8;
        }
        .content-topbar-pill svg {
            width: 18px;
            height: 18px;
            display: block;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .content-topbar-pill-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            border-radius: 999px;
            background: rgba(239, 68, 68, 0.12);
            color: #b91c1c;
            font-size: 0.72rem;
            font-weight: 900;
            line-height: 1;
        }
        .content-topbar .topbar-notifications-btn {
            border-color: var(--line);
            background: var(--surface-soft);
            color: var(--ink);
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, border-color 0.18s ease;
        }
        .content-topbar .topbar-notifications-btn:hover,
        .content-topbar .topbar-notifications-btn:focus {
            background: var(--surface);
            border-color: rgba(var(--primary-rgb), 0.28);
            box-shadow: 0 16px 28px rgba(15, 23, 42, 0.08);
            transform: translateY(-1px);
        }
        .topbar-notifications-count {
            position: absolute;
            top: 6px;
            right: 6px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            background: #ef4444;
            color: #ffffff;
            font-size: 0.7rem;
            font-weight: 900;
            line-height: 1;
            display: grid;
            place-items: center;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.9);
        }
        .content-topbar .topbar-user {
            gap: 0.75rem;
        }
        .content-topbar .topbar-user-copy {
            justify-items: center;
            text-align: center;
        }
        .content-topbar .topbar-user-name {
            color: var(--ink);
            max-width: 220px;
            text-align: center;
        }
        .content-topbar .topbar-user-role {
            justify-content: center;
            justify-self: center;
            color: var(--muted);
        }
        .content-topbar .topbar-user-role::before {
            background: rgba(var(--primary-rgb), 0.6);
            box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.12);
        }
        .content-topbar .topbar-avatar {
            background: #2563eb;
            border-color: rgba(37, 99, 235, 0.14);
        }
        .content-topbar .topbar-avatar svg {
            color: #ffffff;
        }
        .content-topbar .topbar-logout-btn {
            border-color: rgba(239, 68, 68, 0.22);
            background: rgba(239, 68, 68, 0.08);
            color: #b91c1c;
            box-shadow: none;
        }
        .content-topbar .topbar-logout-btn:hover,
        .content-topbar .topbar-logout-btn:focus {
            background: rgba(239, 68, 68, 0.12);
            border-color: rgba(239, 68, 68, 0.32);
            color: #7f1d1d;
        }
        .topbar-brand,
        .topbar-nav,
        .topbar-actions {
            position: relative;
            z-index: 1;
        }
        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            min-width: 0;
        }
        .topbar-brand-mark {
            display: grid;
            place-items: center;
            width: 46px;
            height: 46px;
            border-radius: 14px;
            flex: none;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(var(--primary-tint-rgb), 0.2);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }
        .topbar-brand-mark svg {
            display: block;
        }
        .topbar-brand-mark .system-icon-svg {
            width: 34px;
            height: 34px;
            filter: drop-shadow(0 8px 14px rgba(7, 18, 28, 0.16));
        }
        .topbar-brand-copy {
            min-width: 0;
        }
        .topbar-title {
            margin: 0;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
        }
        .topbar-subtitle {
            margin-top: 0.2rem;
            color: rgba(243, 247, 251, 0.72);
            font-size: 0.78rem;
            font-weight: 600;
            line-height: 1;
            white-space: nowrap;
        }
        .topbar-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            min-width: 0;
            flex-wrap: wrap;
            padding: 0;
            border: none;
            background: transparent;
            box-shadow: none;
        }
        .topbar-nav-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            min-height: 38px;
            padding: 0.42rem 0.85rem;
            border-radius: 12px;
            border: 1px solid transparent;
            background: transparent;
            color: rgba(243, 247, 251, 0.72);
            font-size: 0.8rem;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
            transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }
        .topbar-nav-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            flex: none;
        }
        .topbar-nav-icon svg {
            width: 18px;
            height: 18px;
            display: block;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.9;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .topbar-nav-link:hover,
        .topbar-nav-link:focus {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(var(--primary-tint-rgb), 0.16);
            color: #ffffff;
            transform: translateY(-1px);
        }
        .topbar-nav-link.active {
            background: rgba(var(--primary-rgb), 0.16);
            border-color: rgba(var(--primary-tint-rgb), 0.2);
            color: #ffffff;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            justify-content: flex-end;
            min-width: max-content;
        }
        .topbar-notifications {
            position: relative;
        }
        .topbar-notifications-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            border: 1px solid rgba(var(--primary-tint-rgb), 0.16);
            background: rgba(255, 255, 255, 0.04);
            color: rgba(243, 247, 251, 0.88);
            display: grid;
            place-items: center;
            position: relative;
        }
        .topbar-notifications-btn:hover,
        .topbar-notifications-btn:focus {
            background: rgba(var(--primary-tint-rgb), 0.12);
            border-color: rgba(var(--primary-tint-rgb), 0.26);
        }
        .topbar-notifications-btn svg {
            width: 18px;
            height: 18px;
        }
        .topbar-notifications-dot {
            position: absolute;
            top: 9px;
            right: 10px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ef4444;
            box-shadow: 0 0 0 2px #ffffff;
        }
        .topbar-notifications-menu {
            position: absolute;
            top: calc(100% + 0.65rem);
            inset-inline-end: 0;
            inset-inline-start: auto;
            min-width: 240px;
            max-width: calc(100vw - 32px);
            width: 380px;
            max-height: 440px;
            padding: 0;
            border-radius: 20px;
            border: 1px solid rgba(148, 163, 184, 0.25);
            background: var(--surface);
            box-shadow: 0 28px 60px rgba(15, 23, 42, 0.16);
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-6px);
            transition: 0.18s ease;
            z-index: 2500;
        }
        html.theme-glass .topbar-notifications-menu {
            border-color: var(--glass-border);
            box-shadow: var(--shadow-lg);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
        }
        .topbar-notifications.is-flipped .topbar-notifications-menu {
            inset-inline-start: 0;
            inset-inline-end: auto;
        }
        .topbar-notifications-panel {
            display: flex;
            flex-direction: column;
            max-height: 440px;
        }
        .topbar-notifications-header {
            padding: 1rem 1.5rem;
            background: var(--surface-soft);
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }
        .topbar-notifications-title {
            font-size: 1rem;
            font-weight: 800;
            color: var(--ink);
        }
        .topbar-notifications-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.2rem 0.7rem;
            border-radius: 999px;
            background: rgba(59, 130, 246, 0.18);
            color: var(--primary);
            font-size: 0.75rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .topbar-notifications-scroll {
            max-height: 340px;
            overflow-y: scroll;
            scrollbar-gutter: stable both-edges;
            scrollbar-width: thin;
            scrollbar-color: rgba(59, 130, 246, 0.35) transparent;
            overscroll-behavior: contain;
            padding: 0.75rem 0.9rem 0.75rem 0.75rem;
            background: var(--surface-soft);
        }
        .topbar-notifications-scroll.is-single {
            max-height: none;
            overflow: hidden;
            padding: 0.9rem;
        }
        .topbar-notifications-menu.is-single {
            max-height: none;
        }
        .topbar-notifications-scroll::-webkit-scrollbar {
            width: 8px;
        }
        .topbar-notifications-scroll::-webkit-scrollbar-track {
            background: transparent;
            border-radius: 999px;
            margin-block: 12px;
            box-shadow: inset 0 0 0 5px rgba(226, 232, 240, 0.45);
        }
        .topbar-notifications-scroll::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.35);
            border-radius: 999px;
            border: 2px solid transparent;
            background-clip: content-box;
        }
        .topbar-notifications-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.55);
            background-clip: content-box;
        }
        html.theme-glass .topbar-notifications-scroll::-webkit-scrollbar-track {
            box-shadow: inset 0 0 0 5px rgba(15, 23, 42, 0.75);
        }
        html.theme-glass .topbar-notifications-scroll::-webkit-scrollbar-thumb {
            background: rgba(var(--primary-rgb), 0.42);
        }
        html.theme-glass .topbar-notifications-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(var(--primary-rgb), 0.62);
        }
        .topbar-notifications.is-open .topbar-notifications-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .topbar-notifications.is-open {
            z-index: 2600;
        }
        .topbar-notifications::after {
            content: "";
            position: absolute;
            top: calc(100% + 0.25rem);
            right: 16px;
            width: 10px;
            height: 10px;
            background: var(--glass-surface-strong);
            border: 1px solid var(--glass-border);
            transform: rotate(45deg);
            opacity: 0;
            visibility: hidden;
            transition: 0.18s ease;
            z-index: 2499;
        }
        .topbar-notifications.is-flipped::after {
            right: auto;
            left: 16px;
        }
        .topbar-actions {
            align-items: center;
        }
        .topbar-notifications.is-open::after {
            opacity: 1;
            visibility: visible;
        }
        .topbar-notifications-empty {
            display: grid;
            gap: 0.35rem;
            place-items: center;
            text-align: center;
            padding: 2rem 1.5rem;
            border-radius: 0;
            border: none;
            background: transparent;
            color: var(--muted);
        }
        .topbar-notifications-empty svg {
            width: 44px;
            height: 44px;
            color: rgba(148, 163, 184, 0.8);
        }
        .topbar-notifications-empty strong {
            display: block;
            font-size: 0.9rem;
            color: var(--ink);
        }
        .topbar-notifications-empty span {
            display: block;
            font-size: 0.78rem;
            color: var(--muted);
        }
        .topbar-notification-item {
            padding: 0.95rem 1.05rem;
            border-radius: 16px;
            border: 1px solid var(--glass-border);
            background: var(--glass-surface-strong);
            margin-bottom: 0.75rem;
            box-shadow: var(--glass-shadow);
            width: 100%;
        }
        .topbar-notification-item--pending {
            background: var(--glass-surface-strong);
        }
        .topbar-notification-item--approved {
            background: rgba(34, 197, 94, 0.10);
            border-color: rgba(34, 197, 94, 0.22);
        }
        .topbar-notification-item--rejected {
            background: rgba(239, 68, 68, 0.10);
            border-color: rgba(239, 68, 68, 0.22);
        }
        .topbar-notification-item:last-child {
            margin-bottom: 0;
        }
        .topbar-notification-item:hover {
            background: rgba(59, 130, 246, 0.06);
        }
        .topbar-notification-item--unread {
            background: rgba(59, 130, 246, 0.1);
            border-color: rgba(59, 130, 246, 0.2);
        }
        .topbar-notification-item--unread:hover {
            background: rgba(59, 130, 246, 0.14);
        }
        .topbar-notification-row {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
        }
        .topbar-notification-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #3b82f6;
            margin-top: 0.35rem;
            flex: none;
        }
        .topbar-notification-dot.is-muted {
            opacity: 0;
        }
        .topbar-notification-title {
            display: block;
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--ink);
        }
        .topbar-notification-meta {
            display: block;
            margin-top: 0;
            font-size: 0.78rem;
            color: var(--muted);
        }
        .topbar-notification-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 0.35rem;
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            background: rgba(15, 118, 110, 0.12);
            color: var(--primary);
            font-size: 0.72rem;
            font-weight: 700;
        }
        .topbar-notification-action--transfer {
            background: rgba(148, 163, 184, 0.2);
            color: var(--muted);
        }
        .topbar-notification-dismiss {
            flex: none;
            width: 24px;
            height: 24px;
            border-radius: 8px;
            border: 1px solid rgba(148, 163, 184, 0.25);
            background: rgba(148, 163, 184, 0.12);
            color: var(--muted);
            display: grid;
            place-items: center;
            font-size: 0.8rem;
            font-weight: 700;
            line-height: 1;
        }
        .topbar-notification-content {
            display: grid;
            gap: 0.4rem;
            flex: 1 1 auto;
            min-width: 0;
        }
        .topbar-notifications-footer {
            padding: 0.75rem 1.5rem;
            background: var(--surface-soft);
            border-top: 1px solid var(--line);
        }
        .topbar-notifications-view {
            display: block;
            width: 100%;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }
        .topbar-notifications-view:hover {
            color: var(--primary-deep);
        }
        .topbar-notification-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }
        .topbar-notification-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.3rem;
            margin-top: 0.2rem;
            justify-content: flex-start;
        }
        .topbar-notification-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .topbar-notification-tag--remove::before {
            content: "\00D7";
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.1em;
            height: 1.1em;
            border-radius: 999px;
            font-weight: 900;
            line-height: 1;
        }
        .topbar-notification-tag--remove {
            background: rgba(239, 68, 68, 0.16);
            color: #b91c1c;
            padding-inline: 0.65rem;
            text-align: center;
            max-width: 100%;
            white-space: normal;
        }
        .topbar-notification-tag--transfer {
            background: rgba(148, 163, 184, 0.22);
            color: #475569;
        }
        .topbar-notification-tag--pending {
            background: rgba(15, 118, 110, 0.12);
            color: var(--primary);
            text-decoration: none;
        }
        .topbar-notification-tag--pending.topbar-notification-tag--add {
            background: rgba(34, 197, 94, 0.18);
            color: #15803d;
        }
        .topbar-notification-tag--pending.topbar-notification-tag--remove {
            background: rgba(239, 68, 68, 0.16);
            color: #b91c1c;
        }
        .topbar-notification-tag--pending.topbar-notification-tag--transfer {
            background: rgba(148, 163, 184, 0.22);
            color: #475569;
        }
        .topbar-notification-tag--add {
            background: rgba(34, 197, 94, 0.18);
            color: #15803d;
        }
        .topbar-notification-tag--approved {
            background: rgba(34, 197, 94, 0.18);
            color: #15803d;
        }
        .topbar-notification-tag--rejected {
            background: rgba(239, 68, 68, 0.16);
            color: #b91c1c;
        }
        .topbar-logout-form {
            margin: 0;
            display: flex;
        }
        .topbar-logout-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0 0.92rem;
            border-radius: 12px;
            border: 1px solid rgba(149, 227, 219, 0.12);
            background: rgba(255, 255, 255, 0.04);
            color: rgba(243, 247, 251, 0.88);
            font-size: 0.78rem;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
            transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
        }
        .topbar-logout-btn:hover,
        .topbar-logout-btn:focus {
            background: rgba(149, 227, 219, 0.10);
            border-color: rgba(149, 227, 219, 0.2);
            color: #ffffff;
            transform: translateY(-1px);
        }
        .topbar-user {
            display: flex;
            align-items: center;
            gap: 0.68rem;
            min-height: 40px;
            padding: 0;
        }
        .topbar-user-copy {
            display: grid;
            justify-items: end;
            align-content: center;
            min-width: 0;
            gap: 0.24rem;
        }
        .topbar-user-name {
            display: block;
            color: #ffffff;
            font-size: 0.92rem;
            font-weight: 800;
            line-height: 1.05;
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .topbar-user-role {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            justify-self: end;
            gap: 0.38rem;
            min-height: auto;
            padding: 0;
            border: none;
            background: transparent;
            color: rgba(232, 241, 246, 0.72);
            font-size: 0.72rem;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
        }
        .topbar-user-role::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--primary-tint);
            box-shadow: 0 0 0 2px rgba(var(--primary-tint-rgb), 0.16);
        }
        .topbar-avatar {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            flex: none;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(var(--primary-tint-rgb), 0.16);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }
        .topbar-avatar svg {
            width: 18px;
            height: 18px;
            color: rgba(255, 255, 255, 0.92);
        }
        @media (min-width: 1201px) {
            .app-shell {
                grid-template-areas: "sidebar main";
                grid-template-columns: 292px minmax(0, 1fr);
                grid-template-rows: minmax(0, 1fr);
            }
            .main-sidebar {
                border-bottom: none;
                border-right: 1px solid rgba(var(--primary-tint-rgb), 0.22);
                min-height: 100vh;
                height: 100vh;
                padding: 1.15rem 1rem;
                display: flex;
                flex-direction: column;
                align-items: stretch;
                justify-content: flex-start;
                gap: 1rem;
            }
            .main-sidebar .topbar-nav {
                justify-content: flex-start;
                align-items: stretch;
                flex-direction: column;
                flex-wrap: nowrap;
                gap: 0.35rem;
                padding: 0.85rem 0 0.25rem;
                flex: 1 1 auto;
                min-height: 0;
                overflow-y: auto;
            }
            .main-sidebar .topbar-nav-link {
                width: 100%;
                justify-content: flex-start;
                padding: 0.55rem 0.9rem;
            }
            .main-sidebar .topbar-nav-link:hover,
            .main-sidebar .topbar-nav-link:focus {
                transform: translateX(2px);
            }
            .main-sidebar .topbar-actions {
                margin-top: auto;
                min-width: 0;
                width: 100%;
                display: grid;
                grid-template-columns: max-content minmax(0, 1fr);
                grid-template-areas:
                    "notif user"
                    "logout logout";
                gap: 0.75rem 0.65rem;
                align-items: center;
                justify-content: stretch;
                padding-top: 1rem;
                border-top: 1px solid rgba(var(--primary-tint-rgb), 0.14);
            }
            .main-sidebar .topbar-notifications {
                grid-area: notif;
            }
            .main-sidebar .topbar-user {
                grid-area: user;
                justify-content: space-between;
                padding: 0.55rem 0.65rem;
                border-radius: 14px;
                border: 1px solid rgba(var(--primary-tint-rgb), 0.16);
                background: rgba(255, 255, 255, 0.04);
            }
            .main-sidebar .topbar-user-copy {
                justify-items: start;
            }
            .main-sidebar .topbar-user-role {
                justify-content: flex-start;
                justify-self: start;
            }
            .main-sidebar .topbar-logout-form {
                grid-area: logout;
                width: 100%;
            }
            .main-sidebar .topbar-logout-btn {
                width: 100%;
            }
            .main-sidebar .topbar-notifications-menu {
                top: auto;
                bottom: 0;
                inset-inline-start: calc(100% + 0.85rem);
                inset-inline-end: auto;
                max-height: min(520px, calc(100vh - 1.25rem));
                transform: translateX(-6px);
            }
            .main-sidebar .topbar-notifications.is-open .topbar-notifications-menu {
                transform: translateX(0);
            }
            .main-sidebar .topbar-notifications::after {
                content: none;
            }
            .main-sidebar .topbar-notifications.is-flipped .topbar-notifications-menu {
                inset-inline-start: auto;
                inset-inline-end: calc(100% + 0.85rem);
            }
            .content-topbar {
                display: flex;
                margin: -1.35rem -1.5rem 1.35rem;
                border-radius: 0;
                border-left: none;
                border-right: none;
                border-top: none;
                box-shadow: 0 20px 34px rgba(15, 23, 42, 0.06);
                top: 0;
            }
            .main-sidebar .topbar-actions {
                display: flex;
                flex-direction: column;
                gap: 0.85rem;
            }
            .main-sidebar .topbar-actions .topbar-notifications,
            .main-sidebar .topbar-actions .topbar-user {
                display: none;
            }
            .main-sidebar .topbar-logout-btn {
                border-color: rgba(248, 113, 113, 0.5);
                background: rgba(239, 68, 68, 0.10);
                color: rgba(254, 202, 202, 0.95);
                box-shadow: none;
            }
            .main-sidebar .topbar-logout-btn:hover,
            .main-sidebar .topbar-logout-btn:focus {
                background: rgba(239, 68, 68, 0.16);
                border-color: rgba(248, 113, 113, 0.68);
                color: #fee2e2;
                transform: translateY(-1px);
            }
        }
        .content-shell {
            display: grid;
            gap: 1.5rem;
        }
        .card,
        .hero-card,
        .feature-tile,
        .panel-note,
        .metric-card,
        .surface-card {
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            background: var(--glass-surface);
            box-shadow: var(--glass-shadow);
        }
        html.theme-glass .card,
        html.theme-glass .hero-card,
        html.theme-glass .feature-tile,
        html.theme-glass .panel-note,
        html.theme-glass .metric-card,
        html.theme-glass .surface-card,
        html.theme-glass .ab-card,
        html.theme-glass .eb-card,
        html.theme-glass .bd-card,
        html.theme-glass .bd-sidebar-card,
        html.theme-glass .as-card,
        html.theme-glass .lp-card,
        html.theme-glass .br-search-card,
        html.theme-glass .br-card,
        html.theme-glass .br-empty,
        html.theme-glass .dashboard-metric,
        html.theme-glass .dashboard-table-card,
        html.theme-glass .sr-summary-card,
        html.theme-glass .sr-section,
        html.theme-glass .sr-empty,
        html.theme-glass .product-header,
        html.theme-glass .store-card,
        html.theme-glass .stores-empty,
        html.theme-glass .workspace-panel,
        html.theme-glass .workspace-step,
        html.theme-glass .workspace-toolbar,
        html.theme-glass .form-section,
        html.theme-glass .form-section-highlight,
        html.theme-glass .immersive-surface,
        html.theme-glass .product-mapping .mapping-panel,
        html.theme-glass .product-mapping .mapping-item {
            border-color: var(--glass-border);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
        }
        html.theme-glass :is(
            .card,
            .hero-card,
            .feature-tile,
            .panel-note,
            .metric-card,
            .surface-card,
            .ab-card,
            .eb-card,
            .bd-card,
            .bd-sidebar-card,
            .as-card,
            .lp-card,
            .br-search-card,
            .br-card,
            .br-empty,
            .dashboard-metric,
            .dashboard-table-card,
            .sr-summary-card,
            .sr-section,
            .sr-empty,
            .product-header,
            .store-card,
            .stores-empty,
            .workspace-panel,
            .workspace-step,
            .workspace-toolbar,
            .form-section,
            .form-section-highlight,
            .immersive-surface,
            .product-mapping .mapping-panel,
            .product-mapping .mapping-item
        ):has([data-custom-select].is-open) {
            position: relative;
            z-index: 4200;
        }
        .hero-card {
            overflow: hidden;
            position: relative;
            background: var(--glass-surface-strong);
        }
        .hero-card::before {
            content: none;
        }
        .hero-card .card-body {
            position: relative;
            z-index: 1;
        }
        .section-label,
        .page-kicker {
            display: inline-block;
            color: var(--primary);
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            font-size: 0.77rem;
        }
        .page-shell {
            display: grid;
            gap: 1.5rem;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .page-summary {
            max-width: 760px;
            color: var(--muted);
            margin-bottom: 0;
            line-height: 1.8;
        }
        .metric-card {
            background: #ffffff;
        }
        html.theme-glass .metric-card {
            background: var(--glass-surface);
        }
        .metric-label {
            color: var(--muted);
            font-size: 0.93rem;
        }
        .metric-value {
            margin-top: 0.75rem;
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.03em;
        }
        .metric-foot {
            margin-top: 0.8rem;
            color: var(--muted);
            font-size: 0.88rem;
        }
        .feature-tile,
        .panel-note {
            height: 100%;
            padding: 1.15rem;
            background: var(--glass-surface);
        }
        .feature-tile h5,
        .panel-note h6 {
            margin-bottom: 0.45rem;
            font-weight: 700;
        }
        .feature-tile p,
        .panel-note p {
            margin-bottom: 0;
            color: var(--muted);
            line-height: 1.75;
            font-size: 0.94rem;
        }
        .assignments-shell .page-header {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem 1.5rem;
        }
        .assignments-shell .info-strip {
            margin-top: 0.35rem;
        }
        .assignments-shell .card {
            border-radius: 22px;
        }
        .assignments-shell .employee-picker {
            background: #fbfdff;
            border: 1px solid rgba(214, 225, 236, 0.9);
            border-radius: 18px;
            padding: 0.85rem;
        }
        .assignments-shell .assignment-form-card,
        .assignments-shell .assignment-form-card .card-body {
            overflow: visible;
        }
        .assignments-shell .custom-select {
            position: relative;
            z-index: 10;
        }
        .assignments-shell .custom-select.is-open {
            z-index: 3000;
        }
        .assignments-shell .employee-picker,
        .assignments-shell .employee-select-shell {
            position: relative;
            z-index: 5;
        }
        .assignments-shell .employee-picker-toolbar {
            padding: 0.35rem 0.2rem;
        }
        .assignments-shell .assignment-request-toasts {
            position: fixed;
            left: 50%;
            right: auto;
            top: 50%;
            transform: translate(-50%, -50%);
            z-index: 6000;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            width: min(560px, calc(100vw - 32px));
            pointer-events: none;
        }
        .assignments-shell .assignment-request-toast {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: start;
            gap: 0.75rem;
            padding: 0.95rem 1rem;
            border-radius: 18px;
            border: 1px solid rgba(14, 116, 144, 0.22);
            background: var(--glass-surface-strong);
            box-shadow: 0 18px 40px rgba(2, 21, 32, 0.18);
            color: var(--ink);
            transform: translateY(-10px) scale(0.98);
            opacity: 0;
            pointer-events: auto;
            transition: opacity 180ms ease, transform 180ms ease;
            will-change: transform, opacity;
        }
        html.theme-glass .assignments-shell .assignment-request-toast {
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
        }
        html.theme-glass .assignments-shell .assignment-request-toast {
            border-color: var(--glass-border);
            box-shadow: var(--shadow-lg);
        }
        html.theme-glass .assignments-shell .assignment-request-toast__icon {
            background: rgba(255, 255, 255, 0.08);
            color: rgba(226, 232, 240, 0.92);
            border-color: rgba(255, 255, 255, 0.16);
        }
        html.theme-glass .assignments-shell .assignment-request-toast.is-add .assignment-request-toast__icon {
            background: rgba(16, 185, 129, 0.18);
            color: #6ee7b7;
            border-color: rgba(110, 231, 183, 0.35);
        }
        html.theme-glass .assignments-shell .assignment-request-toast.is-transfer .assignment-request-toast__icon {
            background: rgba(148, 163, 184, 0.16);
            color: rgba(226, 232, 240, 0.92);
            border-color: rgba(148, 163, 184, 0.28);
        }
        html.theme-glass .assignments-shell .assignment-request-toast.is-remove .assignment-request-toast__icon {
            background: rgba(239, 68, 68, 0.18);
            color: rgba(254, 202, 202, 0.95);
            border-color: rgba(248, 113, 113, 0.35);
        }
        .assignments-shell .assignment-request-toast.is-visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        .assignments-shell .assignment-request-toast__icon {
            width: 38px;
            height: 38px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: rgba(14, 116, 144, 0.10);
            color: #0e7490;
            border: 1px solid rgba(14, 116, 144, 0.18);
        }
        .assignments-shell .assignment-request-toast__icon svg {
            display: none;
        }
        .assignments-shell .assignment-request-toast.is-add .assignment-request-toast__icon-check,
        .assignments-shell .assignment-request-toast.is-transfer .assignment-request-toast__icon-check {
            display: block;
        }
        .assignments-shell .assignment-request-toast.is-remove .assignment-request-toast__icon-x {
            display: block;
        }
        .assignments-shell .assignment-request-toast.is-add .assignment-request-toast__icon {
            background: rgba(16, 185, 129, 0.12);
            color: #047857;
            border-color: rgba(16, 185, 129, 0.22);
        }
        .assignments-shell .assignment-request-toast.is-transfer .assignment-request-toast__icon {
            background: rgba(100, 116, 139, 0.14);
            color: #334155;
            border-color: rgba(100, 116, 139, 0.22);
        }
        .assignments-shell .assignment-request-toast.is-remove .assignment-request-toast__icon {
            background: rgba(239, 68, 68, 0.12);
            color: #b91c1c;
            border-color: rgba(239, 68, 68, 0.22);
        }
        .assignments-shell .assignment-request-toast__content {
            min-width: 0;
        }
        .assignments-shell .assignment-request-toast__title {
            font-weight: 900;
            margin-bottom: 0.15rem;
            color: var(--ink);
        }
        .assignments-shell .assignment-request-toast__text {
            color: var(--muted);
            font-weight: 650;
            line-height: 1.7;
        }
        .assignments-shell .assignment-request-toast__close {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.35);
            background: var(--surface-soft);
            color: var(--muted);
            display: grid;
            place-items: center;
            font-size: 1.1rem;
            line-height: 1;
            padding: 0;
            cursor: pointer;
        }
        .assignments-shell .assignment-request-toast__close:hover,
        .assignments-shell .assignment-request-toast__close:focus {
            background: var(--surface);
        }
        @media (max-width: 576px) {
            .assignments-shell .assignment-request-toasts {
                width: calc(100vw - 24px);
            }
            .assignments-shell .assignment-request-toast {
                padding: 0.9rem 0.9rem;
            }
        }
        .assignments-shell .form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
        }
        .assignments-shell .assignments-label {
            font-weight: 700;
            color: var(--ink);
        }
        .access-status-card {
            padding: 1.6rem;
            border-radius: 22px;
            background: var(--glass-surface-strong);
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-md);
        }
        html.theme-glass .access-status-card {
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
        }
        .access-status-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 1.5rem;
        }
        .access-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: rgba(245, 158, 11, 0.14);
            color: var(--accent);
            font-weight: 700;
            font-size: 0.9rem;
            border: 1px solid rgba(245, 158, 11, 0.22);
            white-space: nowrap;
        }
        .access-status-code {
            min-width: 110px;
            height: 110px;
            border-radius: 22px;
            display: grid;
            place-items: center;
            background: var(--surface-soft);
            color: var(--primary);
            font-weight: 800;
            font-size: 2rem;
            border: 1px solid var(--line);
        }
        .access-status-title {
            margin-bottom: 0.5rem;
            font-weight: 800;
        }
        .access-status-copy {
            margin: 0;
            color: var(--muted);
            line-height: 1.8;
        }
        .assignments-shell .assignments-select {
            height: 46px;
            border-radius: 14px;
            border-color: rgba(15, 118, 110, 0.25);
            background: var(--surface-soft);
        }
        .assignments-shell .assignments-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12);
        }
        .assignments-shell .assignments-select option {
            background-color: var(--surface-soft);
            color: var(--ink);
            font-weight: 600;
        }
        .assignments-shell .custom-select-menu {
            border-radius: 16px;
            border: 1px solid var(--glass-border);
            background: var(--glass-surface-strong);
            box-shadow: var(--shadow-lg);
            z-index: 3001;
        }
        html.theme-glass .assignments-shell .custom-select-menu {
            background: #0b1220;
            border-color: rgba(255, 255, 255, 0.18);
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
        }
        .assignments-shell .custom-select-option {
            padding: 0.85rem 1rem;
            padding-inline-end: 3.8rem;
            border-radius: 14px;
            margin: 0.2rem 0.35rem;
            transition: 0.18s ease;
        }
        .assignments-shell .custom-select-option:hover,
        .assignments-shell .custom-select-option:focus {
            border-color: rgba(15, 118, 110, 0.16);
            background: rgba(15, 118, 110, 0.06);
            color: var(--primary-deep);
        }
        .assignments-shell .custom-select-option.is-selected {
            border-color: rgba(15, 118, 110, 0.22);
            background: rgba(15, 118, 110, 0.08);
            color: var(--primary-deep);
            font-weight: 700;
        }
        .assignments-shell .custom-select-option.is-selected::after {
            content: "✓";
            position: absolute;
            top: 50%;
            inset-inline-end: 0.9rem;
            transform: translateY(-50%);
            color: var(--primary-deep);
            font-size: 0.9rem;
            font-weight: 700;
            line-height: 1;
        }
        .assignments-shell .custom-select-option .custom-option-meta {
            margin-inline-start: auto;
        }
        .soft-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .soft-list li {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            padding: 0.8rem 0;
            border-bottom: 1px solid var(--line);
            color: var(--ink);
        }
        .soft-list li::before {
            content: "";
            width: 10px;
            height: 10px;
            margin-top: 0.45rem;
            border-radius: 50%;
            flex: none;
            background: var(--primary);
            box-shadow: 0 0 0 5px rgba(15, 118, 110, 0.12);
        }
        .soft-list li:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }
        .people-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 0.4rem;
        }
        .people-item {
            padding: 0.75rem 0.75rem;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: var(--glass-surface-strong);
        }
        .people-item--manager {
            border-color: rgba(15, 118, 110, 0.22);
            background: var(--surface-soft);
        }
        .people-text {
            display: grid;
            gap: 0.45rem;
        }
        .people-role {
            color: var(--muted);
            font-weight: 700;
            font-size: 0.84rem;
        }
        .people-line {
            display: flex;
            align-items: center;
            justify-content: flex-end; 
            gap: 0.55rem;
        }
        .people-name {
            font-weight: 800;
            color: var(--ink);
            direction: ltr;
            text-align: left;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .people-badge {
            flex: 0 0 auto;
            padding: 0.18rem 0.6rem;
            border-radius: 999px;
            border: 1px solid rgba(15, 118, 110, 0.18);
            background: rgba(15, 118, 110, 0.06);
            color: var(--primary-deep);
            font-size: 0.78rem;
            font-weight: 800;
            white-space: nowrap;
            direction: ltr;
        }
        .people-empty {
            color: var(--muted);
            font-weight: 700;
            padding: 0.6rem 0.2rem;
        }
        .info-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
        }
        .info-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.55rem 0.9rem;
            border-radius: 999px;
            background: #f5f9fc;
            border: 1px solid var(--line);
            color: var(--ink);
            font-size: 0.92rem;
        }
        .info-pill strong {
            color: var(--primary-deep);
        }
        .form-label {
            margin-bottom: 0.55rem;
            font-weight: 700;
            color: var(--ink);
        }
        .form-label-subtle {
            font-size: 0.92rem;
            font-weight: 600;
            color: var(--muted);
        }
        .form-control,
        .form-select {
            min-height: 52px;
            border-radius: 15px;
            border-color: var(--line);
            background: var(--surface-soft);
            box-shadow: none;
            color: var(--ink);
        }
        .form-control::placeholder,
        .form-select::placeholder,
        textarea.form-control::placeholder {
            color: var(--placeholder);
        }
        textarea.form-control {
            min-height: 120px;
        }
        .form-control:focus,
        .form-select:focus {
            border-color: rgba(var(--primary-rgb), 0.58);
            box-shadow: 0 0 0 0.25rem rgba(var(--primary-rgb), 0.18);
        }
        .input-hint {
            margin-top: 0.4rem;
            color: var(--muted);
            font-size: 0.84rem;
        }
        .page-edit-store .input-hint,
        .page-edit-store .form-section-title p {
            display: none !important;
        }
        .custom-select {
            position: relative;
        }
        .custom-select.is-open {
            z-index: 3000;
        }
        .custom-select-native {
            position: absolute;
            opacity: 0;
            pointer-events: none;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: 0;
            border: 0;
        }
        .custom-select-trigger {
            width: 100%;
            padding-inline: 1rem 3.2rem;
            font-weight: 700;
            text-align: left;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            cursor: pointer;
        }
        .manager-custom-select .custom-select-trigger {
            padding-inline: 1.35rem 3.6rem;
        }
        .manager-custom-select .custom-select-value {
            padding-inline-end: 0.35rem;
        }
        .custom-select-trigger::before {
            content: "";
            position: absolute;
            top: 50%;
            right: 1rem;
            width: 9px;
            height: 9px;
            transform: translateY(-50%);
            pointer-events: none;
        }
        .custom-select-trigger::before {
            transform: translateY(-50%) rotate(45deg);
            border-bottom: 2px solid var(--primary);
            border-right: 2px solid var(--primary);
            margin-right: 0.35rem;
        }
        .custom-select-menu {
            position: absolute;
            top: calc(100% + 0.55rem);
            left: 0;
            right: 0;
            display: grid;
            gap: 0;
            padding: 0;
            border: 1px solid var(--glass-border);
            border-radius: 0.75rem;
            background: var(--glass-surface-strong);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-6px);
            transition: 0.18s ease;
            z-index: 2001;
            isolation: isolate;
        }
        html.theme-glass .custom-select-menu {
            background: #0b1220;
            border-color: rgba(255, 255, 255, 0.18);
            box-shadow: var(--shadow-lg);
            z-index: 3100;
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
        }
        .custom-select.is-open .custom-select-trigger::before {
            transform: translateY(-50%) rotate(225deg);
        }
        .custom-select-search {
            padding: 0.75rem;
            border-bottom: 1px solid var(--line);
            background: var(--surface-soft);
        }
        html.theme-glass .custom-select-search {
            background: #0b1220;
            border-bottom-color: rgba(255, 255, 255, 0.12);
        }
        .custom-select-search-input {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid var(--line);
            background: var(--surface-soft);
            font-size: 0.875rem;
            color: var(--ink);
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        html.theme-glass .custom-select-search-input {
            border-color: rgba(255, 255, 255, 0.16);
            background: #0f172a;
            color: var(--ink);
        }
        .custom-select-search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.18);
        }
        html.theme-glass .custom-select-search-input:focus {
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.22);
        }
        .custom-select-options {
            --custom-select-option-height: 3rem;
            max-height: calc(var(--custom-select-option-height) * 4);
            overflow-y: auto;
            padding: 0.5rem;
            display: grid;
            gap: 0.5rem;
        }
        .custom-select-empty {
            padding: 1.25rem;
            text-align: center;
            color: var(--muted);
            font-size: 0.875rem;
            display: none;
        }
        .custom-select-menu-scrollable {
            max-height: 320px;
            overflow-y: auto;
            overscroll-behavior: contain;
            scrollbar-width: thin;
            scrollbar-color: rgba(15, 118, 110, 0.42) rgba(216, 225, 236, 0.45);
            contain: content;
            content-visibility: auto;
            contain-intrinsic-size: 320px;
        }
        .custom-select-menu-scrollable::-webkit-scrollbar {
            width: 10px;
        }
        .custom-select-menu-scrollable::-webkit-scrollbar-track {
            background: rgba(216, 225, 236, 0.45);
            border-radius: 999px;
        }
        .custom-select-menu-scrollable::-webkit-scrollbar-thumb {
            background: rgba(15, 118, 110, 0.42);
            border-radius: 999px;
            border: 2px solid transparent;
            background-clip: content-box;
        }
        html.theme-glass .custom-select-menu-scrollable {
            scrollbar-color: rgba(var(--primary-rgb), 0.48) rgba(15, 23, 42, 0.55);
        }
        html.theme-glass .custom-select-menu-scrollable::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.55);
        }
        html.theme-glass .custom-select-menu-scrollable::-webkit-scrollbar-thumb {
            background: rgba(var(--primary-rgb), 0.48);
        }
        .custom-select.is-open .custom-select-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            pointer-events: auto;
        }
        .custom-select-option {
            width: 100%;
            padding: 0.5rem 2.5rem 0.5rem 1rem;
            border: 1px solid var(--line);
            border-radius: 0.75rem;
            background: var(--glass-surface-strong);
            color: var(--ink);
            font-weight: 600;
            text-align: left;
            transition: 0.18s ease;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            min-height: var(--custom-select-option-height);
        }
        html.theme-glass .custom-select-option {
            border-color: rgba(255, 255, 255, 0.14);
            background: #0f172a;
        }
        .employee-option-branch {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--muted);
            white-space: nowrap;
            flex: none;
        }
        .custom-select-option > :first-child {
            min-width: 0;
        }
        .custom-select-option > span,
        .custom-select-option > bdi {
            flex: 1 1 auto;
            min-width: 0;
            text-align: left;
        }
        .employee-option-main {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1 1 auto;
            min-width: 0;
            max-width: 100%;
        }
        .employee-option-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1 1 auto;
            min-width: 0;
        }
        .employee-option-branch-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.18rem 0.55rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            background: rgba(15, 118, 110, 0.12);
            color: var(--primary);
            border: 1px solid rgba(15, 118, 110, 0.2);
            white-space: nowrap;
            flex: none;
        }
        .employee-option-branch-pill.is-vacant {
            background: rgba(148, 163, 184, 0.18);
            color: var(--muted);
            border-color: rgba(148, 163, 184, 0.3);
        }
        .custom-select-option:hover {
            border-color: rgba(var(--primary-rgb), 0.22);
            background: var(--surface-soft);
            color: var(--ink);
        }
        html.theme-glass .custom-select-option:hover {
            border-color: rgba(var(--primary-rgb), 0.32);
            background: rgba(var(--primary-rgb), 0.16);
        }
        .custom-select-option.is-selected {
            border-color: rgba(var(--primary-rgb), 0.35);
            background: rgba(var(--primary-rgb), 0.12);
            color: var(--primary);
        }
        html.theme-glass .custom-select-option.is-selected {
            border-color: rgba(var(--primary-rgb), 0.45);
            background: rgba(var(--primary-rgb), 0.20);
            color: rgba(147, 197, 253, 0.95);
        }
        .custom-select-option.is-selected::after {
            content: "\2713";
            position: absolute;
            top: 50%;
            inset-inline-end: 0.9rem;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 0.9rem;
            font-weight: 700;
            line-height: 1;
        }
        html.theme-glass .custom-select-option.is-selected::after {
            color: rgba(96, 165, 250, 0.95);
        }
        .custom-option-meta {
            display: inline-block;
            margin-inline-start: 0.5rem;
            padding: 0.16rem 0.55rem;
            border-radius: 999px;
            background: rgba(148, 163, 184, 0.18);
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 700;
            vertical-align: middle;
        }
        .custom-select-option .custom-option-meta {
            margin-inline-start: auto;
        }
        .file-upload-input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
        }
        .file-upload-shell {
            position: relative;
        }
        .file-upload-display {
            min-height: 52px;
            padding: 0.55rem 0.7rem;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: var(--surface-soft);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            position: relative;
            z-index: 1;
        }
        .file-upload-display strong {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 118px;
            min-height: 38px;
            padding: 0.55rem 0.95rem;
            border-radius: 14px;
            border: 1px solid var(--primary);
            background: var(--primary);
            color: #ffffff;
            font-size: 0.9rem;
            font-weight: 700;
            transition: 0.2s ease;
        }
        .file-upload-shell:hover .file-upload-display strong {
            color: var(--primary);
            background: var(--glass-surface-strong);
        }
        .file-upload-name {
            font-size: 0.9rem;
        }
        .file-upload-name {
            color: var(--muted);
            font-size: 0.95rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .employee-picker {
            display: grid;
            gap: 0.9rem;
            padding: 1rem;
            border: 1px solid var(--glass-border);
            border-radius: 18px;
            background: var(--glass-surface-strong);
            box-shadow: var(--glass-shadow);
        }
        html.theme-glass .employee-picker {
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
        }
        .employee-picker-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .employee-picker-toolbar strong {
            display: block;
            margin-bottom: 0.15rem;
            font-size: 0.98rem;
        }
        .employee-picker-toolbar span {
            color: var(--muted);
            font-size: 0.9rem;
        }
        .employee-picker-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 84px;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: var(--surface-soft);
            border: 1px solid var(--line);
            color: var(--muted);
            font-weight: 700;
            font-size: 0.84rem;
        }
        .employee-search-shell .form-control {
            min-height: 46px;
            background: var(--surface-soft);
            border-radius: 12px;
            border: 1px solid var(--line);
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.05);
        }
        .employee-select-shell .form-select {
            min-height: 46px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: var(--surface-soft);
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.05);
        }
        .employee-selection-bio {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            column-gap: 0.6rem;
            row-gap: 0.6rem;
            padding-top: 0.35rem;
            max-height: 180px;
            overflow: auto;
            direction: ltr;
            justify-content: end;
            justify-items: end;
            align-items: stretch;
        }
        .employee-selection-empty {
            color: var(--muted);
            font-size: 0.84rem;
        }
        .employee-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.6rem;
            border-radius: 999px;
            background: var(--surface-soft);
            border: 1px solid var(--line);
            color: var(--ink);
            font-size: 0.82rem;
            font-weight: 700;
            direction: ltr;
            justify-content: flex-start;
            width: 100%;
            min-height: 36px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.06);
        }
        .employee-chip-label {
            flex: 1 1 auto;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            text-align: left;
        }
        .employee-chip-remove {
            position: relative;
            display: inline-block;
            flex: 0 0 auto;
            border: none;
            background: rgba(148, 163, 184, 0.22);
            color: var(--muted);
            width: 20px;
            height: 20px;
            border-radius: 50%;
            padding: 0;
            line-height: 1;
            cursor: pointer;
            font-size: 0; 
            font-weight: 800;
            margin-inline-start: auto;
            transition: 0.15s ease;
            box-sizing: border-box;
        }
        .employee-chip-remove::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 10px;
            height: 2px;
            background: currentColor;
            border-radius: 999px;
            transform: translate(-50%, -50%) rotate(45deg);
        }
        .employee-chip-remove::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 10px;
            height: 2px;
            background: currentColor;
            border-radius: 999px;
            transform: translate(-50%, -50%) rotate(-45deg);
        }
        .employee-chip-remove:hover,
        .employee-chip-remove:focus {
            background: rgba(239, 68, 68, 0.18);
            color: var(--danger);
        }
        .employee-custom-select .custom-select-trigger,
        .employee-custom-select .custom-select-value {
            direction: ltr;
            text-align: left;
        }
        .employee-custom-select .custom-select-trigger {
            justify-content: flex-start;
        }
        .employee-custom-select .custom-select-value {
            flex: 1 1 auto;
            width: 100%;
        }
        .employee-custom-select .custom-select-option {
            direction: ltr;
            text-align: left;
        }
        .manager-custom-select .custom-select-value,
        .manager-custom-select .custom-select-option {
            direction: ltr;
            text-align: left;
        }
        .employee-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 0.75rem;
        }
        .employee-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.9rem 1rem;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--glass-surface);
            transition: 0.2s ease;
            cursor: pointer;
        }
        .employee-card:hover {
            border-color: rgba(var(--primary-rgb), 0.28);
            box-shadow: 0 12px 22px rgba(15, 23, 42, 0.08);
        }
        .employee-card.is-selected {
            border-color: rgba(15, 118, 110, 0.45);
            background: rgba(15, 118, 110, 0.10);
            box-shadow: 0 14px 26px rgba(15, 118, 110, 0.10);
        }
        .employee-card-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .employee-card-check {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            border: 1.5px solid var(--line);
            background: var(--glass-surface-strong);
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-inline-start: auto;
        }
        .employee-card.is-selected .employee-card-check {
            border-color: var(--primary);
            background: var(--primary);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.2);
        }
        .employee-card.is-selected .employee-card-check::after {
            content: "";
            width: 9px;
            height: 5px;
            border: 2px solid #ffffff;
            border-top: 0;
            border-left: 0;
            transform: rotate(45deg);
        }
        .employee-card-body {
            display: grid;
            gap: 0.2rem;
            min-width: 0;
        }
        .employee-card-body strong {
            font-size: 0.95rem;
            line-height: 1.5;
            color: var(--ink);
        }
        .employee-card-body small {
            color: var(--muted);
            font-size: 0.82rem;
            line-height: 1.6;
        }
        .dual-list-shell {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
            gap: 1rem;
            align-items: center;
        }
        .dual-list-panel {
            border: 1px solid var(--glass-border);
            border-radius: 18px;
            background: var(--glass-surface-strong);
            overflow: hidden;
        }
        html.theme-glass .dual-list-panel {
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
        }
        .dual-list-header {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--line);
            background: var(--surface-soft);
            font-weight: 800;
        }
        .dual-list-box {
            width: 100%;
            min-height: 260px;
            border: 0;
            padding: 0.65rem;
            background: transparent;
            outline: none;
        }
        .dual-list-actions {
            display: grid;
            gap: 0.75rem;
        }
        .dual-list-actions .btn {
            min-width: 54px;
            padding-inline: 0.9rem;
        }
        .btn {
            border-radius: 14px;
            font-weight: 700;
            padding: 0.75rem 1rem;
        }
        .btn-sm {
            border-radius: 12px;
        }
        .btn-primary {
            background: var(--primary-deep);
            border-color: var(--primary-deep);
        }
        .btn-primary:hover,
        .btn-primary:focus {
            background: var(--primary);
            border-color: var(--primary);
        }
        .btn-outline-primary {
            color: var(--primary);
            border-color: rgba(15, 118, 110, 0.25);
            background: #f1fbf9;
        }
        .btn-outline-primary:hover,
        .btn-outline-primary:focus {
            color: var(--primary-deep);
            background: #d9f5f1;
            border-color: var(--primary);
        }
        .btn-outline-secondary {
            color: #5b6b7f;
            border-color: rgba(98, 116, 138, 0.3);
            background: #f6f8fb;
        }
        .btn-outline-secondary:hover,
        .btn-outline-secondary:focus {
            color: #394558;
            background: #e9eef5;
            border-color: #7b8aa1;
        }
        .btn-outline-danger {
            color: var(--danger);
            border-color: rgba(185, 28, 28, 0.28);
            background: #fff5f5;
        }
        .btn-outline-danger:hover,
        .btn-outline-danger:focus {
            color: #991b1b;
            background: #fee2e2;
            border-color: var(--danger);
        }
        .topbar-secondary-action {
            color: #ffffff;
            background: var(--primary);
            border-color: var(--primary);
        }
        .topbar-secondary-action:hover,
        .topbar-secondary-action:focus {
            color: var(--primary);
            background: var(--surface);
            border-color: var(--primary);
        }
        .action-solid-primary {
            color: #ffffff;
            background: var(--primary);
            border-color: var(--primary);
        }
        .action-solid-primary:hover,
        .action-solid-primary:focus {
            color: var(--primary);
            background: var(--surface);
            border-color: var(--primary);
        }
        .action-outline-primary {
            color: var(--primary);
            background: var(--surface);
            border-color: var(--primary);
        }
        .action-outline-primary:hover,
        .action-outline-primary:focus {
            color: #ffffff;
            background: var(--primary);
            border-color: var(--primary);
        }
        .btn-light {
            background: #eef3f8;
            border-color: #dde6ef;
        }
        .form-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: flex-start;
            align-items: center;
        }
        .form-actions.form-actions-split {
            justify-content: space-between;
        }
        .form-actions .btn {
            min-width: 160px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        @media (max-width: 520px) {
            .form-actions .btn {
                flex: 1 1 100%;
                min-width: 0;
            }
        }
        .table-wrap {
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            overflow: hidden;
            background: var(--surface);
        }
        .table,
        .stores-table {
            margin-bottom: 0;
        }
        .stores-table {
            table-layout: fixed;
            min-width: 1120px;
        }
        .table thead th {
            padding: 0.85rem 0.75rem;
            background: #f3f7fa;
            color: #4b5b70;
            font-size: 0.82rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            border-bottom: 1px solid var(--line);
            text-align: center;
            vertical-align: middle;
        }
        .table tbody td {
            padding: 0.95rem 0.75rem;
            vertical-align: middle;
            border-color: #ebf0f5;
        }
        .table tbody tr:hover {
            background: rgba(15, 118, 110, 0.03);
        }
        html.theme-glass .table-wrap {
            border-color: var(--glass-border);
            background: var(--glass-surface);
            box-shadow: var(--glass-shadow);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
        }
        html.theme-glass .table thead th {
            background: rgba(255, 255, 255, 0.06);
            color: var(--muted);
            border-bottom-color: rgba(255, 255, 255, 0.12);
        }
        html.theme-glass .table tbody td {
            border-color: rgba(255, 255, 255, 0.10);
        }
        html.theme-glass .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .stores-table th,
        .stores-table td {
            word-wrap: break-word;
        }
        .stores-table th:last-child,
        .stores-table td:last-child {
            text-align: center;
        }
        .table-cell-stack {
            display: grid;
            gap: 0.22rem;
        }
        .table-cell-stack--center {
            justify-items: center;
            text-align: center;
        }
        .table-cell-stack--actions {
            justify-items: center;
        }
        .table-title {
            font-weight: 700;
            color: var(--ink);
            line-height: 1.45;
        }
        .table-meta {
            color: var(--muted);
            font-size: 0.86rem;
            line-height: 1.6;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 88px;
            padding: 0.4rem 0.7rem;
            border-radius: 999px;
            font-weight: 700;
            white-space: nowrap;
        }
        .table-actions {
            display: inline-flex;
            flex-wrap: nowrap;
            gap: 0.45rem;
            justify-content: center;
            align-items: center;
            width: 100%;
        }
        .table-actions form {
            margin: 0;
            display: inline-flex;
            align-items: center;
            position: relative;
        }
        .table-actions .btn {
            min-width: 36px;
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }
        .table-actions .btn svg {
            width: 18px;
            height: 18px;
        }
        .brochure-btn {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.1rem;
            min-width: 76px;
            height: 44px;
            padding: 0.3rem 0.6rem;
            font-weight: 700;
            line-height: 1;
            border-radius: 14px;
        }
        .brochure-btn span {
            font-size: 0.78rem;
            color: inherit;
        }
        .brochure-btn strong {
            font-size: 0.85rem;
            letter-spacing: 0.02em;
        }
        .brochure-btn:hover,
        .brochure-btn:focus {
            color: var(--primary-deep);
            background: #dff6f1;
            border-color: var(--primary);
            box-shadow: 0 10px 20px rgba(15, 118, 110, 0.12);
            transform: translateY(-1px);
        }
        .brochure-btn:active {
            transform: translateY(0);
            box-shadow: none;
        }
        .delete-modal-backdrop {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.45);
            z-index: 9999;
        }
        .delete-modal-backdrop.is-visible {
            display: flex;
        }
        .delete-modal {
            width: clamp(360px, 50vw, 640px);
            max-width: 90vw;
            background: var(--surface-strong);
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(216, 225, 236, 0.9);
            padding: 1.5rem 1.6rem;
        }
        .delete-modal-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }
        .delete-modal-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #fff7ed;
            color: #c2410c;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .delete-modal-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--ink);
        }
        .delete-modal-body {
            color: var(--muted);
            font-size: 0.92rem;
            line-height: 1.7;
            margin-bottom: 1.2rem;
        }
        .delete-modal-body strong {
            color: var(--ink);
        }
        .delete-modal-actions {
            display: flex;
            gap: 0.6rem;
            justify-content: flex-end;
        }
        body.modal-open {
            overflow: hidden;
        }
        .btn-solid-danger {
            color: #ffffff;
            background: var(--danger);
            border-color: var(--danger);
        }
        .btn-solid-danger:hover,
        .btn-solid-danger:focus {
            color: #ffffff;
            background: #991b1b;
            border-color: #991b1b;
        }
        .badge-soft-success {
            background: rgba(34, 197, 94, 0.18);
            color: var(--success);
        }
        .badge-soft-secondary {
            background: rgba(148, 163, 184, 0.18);
            color: var(--muted);
        }
        .badge-soft-warning {
            background: rgba(245, 158, 11, 0.18);
            color: var(--accent);
        }
        .badge-soft-pending {
            background: rgba(15, 118, 110, 0.12);
            color: var(--primary);
        }
        .badge-soft-rejected {
            background: rgba(239, 68, 68, 0.16);
            color: var(--danger);
        }
        .badge-soft-approved {
            background: rgba(34, 197, 94, 0.16);
            color: var(--success);
        }
        .empty-state {
            padding: 2rem;
            text-align: center;
            color: var(--muted);
        }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }
        .detail-item {
            padding: 1rem;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: var(--glass-surface-strong);
        }
        html.theme-glass .detail-item {
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
        }
        .detail-item span {
            display: block;
            color: var(--muted);
            font-size: 0.85rem;
            margin-bottom: 0.35rem;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        @media (max-width: 992px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
        }
        .detail-item strong {
            font-size: 1rem;
        }
        .selection-card {
            display: block;
            height: 100%;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: var(--glass-surface-strong);
            transition: 0.2s ease;
            cursor: pointer;
        }
        html.theme-glass .selection-card {
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
        }
        .selection-card:hover {
            transform: translateY(-2px);
            border-color: rgba(15, 118, 110, 0.35);
            box-shadow: var(--shadow-md);
        }
        .selection-card .form-check-input {
            width: 1.4rem;
            height: 1.4rem;
            margin-top: 0.1rem;
        }
        .alert {
            border-radius: 18px;
            border: 0;
            box-shadow: var(--shadow-md);
        }
        .time-picker {
            position: relative;
        }
        .time-picker-input {
            cursor: pointer;
            padding-inline-end: 2.75rem;
            background-color: inherit;
            min-height: 52px;
            border-radius: 15px;
            border-color: var(--line);
            background: var(--surface-soft);
            box-shadow: none;
            font-weight: 600;
        }
        .time-input-wrap {
            position: relative;
        }
        .time-input-wrap input[type="time"] {
            cursor: pointer;
        }
        :root:dir(rtl) .time-picker-input {
            padding-inline-end: 1rem;
            padding-inline-start: 2.75rem;
        }
        .time-picker-input:focus {
            background-color: inherit;
        }
        .time-picker-input::placeholder {
            color: #8a98ad;
        }
        .time-picker-input::after {
            content: '';
        }
        .time-picker-input {
            background-image: none;
        }
        .time-picker::after {
            content: '';
            position: absolute;
            top: 50%;
            inset-inline-end: 0.95rem;
            width: 18px;
            height: 18px;
            transform: translateY(-50%);
            pointer-events: none;
            opacity: 0.85;
            background: currentColor;
            color: var(--primary);
            -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='none' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M12 7v5l3 2'/%3E%3Cpath fill='none' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2s10 4.477 10 10z'/%3E%3C/svg%3E") center/contain no-repeat;
                    mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='none' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M12 7v5l3 2'/%3E%3Cpath fill='none' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2s10 4.477 10 10z'/%3E%3C/svg%3E") center/contain no-repeat;
        }
        :root:dir(rtl) .time-picker::after {
            inset-inline-end: auto;
            inset-inline-start: 0.95rem;
        }
        .time-picker.is-open::after {
            color: var(--primary-deep);
            opacity: 1;
        }
        .time-picker-menu {
            position: absolute;
            inset-inline-start: auto;
            inset-inline-end: 0;
            top: calc(100% + 0.5rem);
            border: 1px solid var(--line);
            border-radius: 18px;
            background: var(--surface-strong);
            box-shadow: var(--shadow-md);
            padding: 0.85rem;
            z-index: 1200;
            display: none;
            width: min(360px, 100%);
        }
        .time-picker.is-open .time-picker-menu {
            display: block;
        }
        .time-picker-surface {
            display: grid;
            gap: 0.65rem;
        }
        .time-picker-head {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
        }
        .time-picker-title {
            font-weight: 900;
            color: var(--ink);
        }
        .time-picker-controls {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            flex: 0 0 auto;
        }
        .time-picker-time-fields {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.4rem;
            border-radius: 14px;
            background: var(--surface-soft);
            border: 1px solid var(--line);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.85);
            height: 42px;
        }
        .time-picker-current {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.2rem 0.5rem;
            border-radius: 14px;
            background: var(--surface-soft);
            border: 1px solid var(--line);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.85);
            height: 40px;
            font-variant-numeric: tabular-nums;
        }
        .time-picker-current-part {
            min-width: 28px;
            text-align: center;
            font-weight: 800;
            color: var(--ink);
        }
        .time-picker-field {
            width: 50px;
            border: 0;
            outline: none;
            background: transparent;
            font-weight: 900;
            color: var(--ink);
            padding: 0.35rem 0.4rem;
            border-radius: 12px;
            text-align: center;
            font-variant-numeric: tabular-nums;
        }
        .time-picker-field:focus {
            background: rgba(var(--primary-rgb), 0.12);
            box-shadow: 0 0 0 0.18rem rgba(var(--primary-rgb), 0.18);
        }
        .time-picker-colon {
            font-weight: 900;
            color: var(--placeholder);
            padding-inline: 0.1rem;
            user-select: none;
        }
        .time-picker-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.5rem;
            padding: 0.25rem;
            background: transparent;
            border: 0;
        }
        .time-picker-list-wrap {
            display: grid;
            gap: 0.25rem;
        }
        .time-picker-list {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 0.25rem;
            max-height: 160px;
            overflow: auto;
            padding: 0.15rem;
            border-radius: 10px;
            background: transparent;
            border: 0;
        }
        .time-picker-item {
            border: 0;
            background: transparent;
            color: #1f2a44;
            border-radius: 8px;
            padding: 0.25rem 0;
            font-weight: 700;
            text-align: center;
            transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
            cursor: pointer;
        }
        .time-picker-item:hover {
            background: rgba(var(--primary-rgb), 0.1);
        }
        .time-picker-item.is-selected {
            background: rgba(var(--primary-rgb), 0.16);
            color: var(--primary-deep);
        }
        .time-picker-item:focus-visible {
            outline: none;
            box-shadow: 0 0 0 0.18rem rgba(var(--primary-rgb), 0.22);
        }
        .time-picker-seg {
            display: inline-flex;
            border-radius: 14px;
            padding: 0.25rem;
            background: #f3f7fa;
            border: 1px solid rgba(216, 225, 236, 0.9);
            gap: 0.25rem;
            flex: 0 0 auto;
            height: 42px;
            align-items: center;
        }
        .time-picker-seg-btn {
            border: 0;
            background: transparent;
            padding: 0.45rem 0.75rem;
            border-radius: 12px;
            font-weight: 900;
            color: #51627a;
            transition: 0.15s ease;
            min-width: 56px;
            height: 34px;
        }
        .time-picker-seg-btn.is-selected {
            background: var(--primary);
            color: #ffffff;
        }
        .time-picker-foot {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-start;
        }
        .time-picker-foot:empty {
            display: none;
        }
        .time-picker-foot .btn {
            min-width: 96px;
            padding: 0.55rem 0.85rem;
            border-radius: 14px;
        }
        .field-note {
            padding: 0.65rem 0.85rem;
            border-radius: 16px;
            font-weight: 800;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--line);
            background: var(--surface);
            color: var(--ink);
        }
        .field-note-danger {
            border-color: rgba(185, 28, 28, 0.25);
            background: #fff5f5;
            color: #991b1b;
        }
        html.theme-glass .field-note {
            border-color: var(--glass-border);
            background: rgba(255, 255, 255, 0.10);
            box-shadow: var(--glass-shadow);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
        }
        html.theme-glass .field-note-danger {
            border-color: rgba(248, 113, 113, 0.35);
            background: rgba(239, 68, 68, 0.12);
            color: rgba(254, 226, 226, 0.95);
        }
        .form-control.is-invalid,
        .form-select.is-invalid,
        .custom-select-trigger.is-invalid,
        .file-upload-shell.is-invalid {
            border-color: rgba(185, 28, 28, 0.55) !important;
            box-shadow: 0 0 0 0.18rem rgba(185, 28, 28, 0.14) !important;
        }
        .employee-picker.is-invalid {
            border-radius: 18px;
            outline: 2px solid rgba(185, 28, 28, 0.35);
            outline-offset: 6px;
        }
        .time-picker-input.is-invalid,
        .time-picker-time-fields.is-invalid {
            border-color: rgba(185, 28, 28, 0.55) !important;
            box-shadow: 0 0 0 0.18rem rgba(185, 28, 28, 0.14) !important;
        }
        .time-picker-input.is-invalid {
            background-image: none !important;
        }
        @media (max-width: 520px) {
            .time-picker-foot .btn {
                flex: 1 1 50%;
                min-width: 0;
            }
            .time-picker-head {
                flex-wrap: wrap;
                justify-content: flex-start;
            }
        }
        .flash-toast {
            --flash-bg: #ecfdf5;
            --flash-border: rgba(6, 95, 70, 0.18);
            --flash-fg: #065f46;
            --flash-accent: #10b981;
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            left: auto;
            transform: none;
            z-index: 2100;
            max-width: min(520px, calc(100vw - 3rem));
            padding: 0.9rem 1.1rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 18px 28px -18px rgba(15, 23, 42, 0.35);
            border: 1px solid var(--flash-border);
            background: var(--flash-bg);
            color: var(--flash-fg);
            transition: opacity 0.25s ease, transform 0.25s ease;
        }
        .flash-toast.is-hidden {
            opacity: 0;
            transform: translateY(-10px);
            pointer-events: none;
        }
        .flash-toast__icon {
            width: 38px;
            height: 38px;
            border-radius: 0.9rem;
            display: grid;
            place-items: center;
            background: var(--flash-accent);
            color: #ffffff;
            flex: 0 0 auto;
        }
        .flash-toast__text {
            font-weight: 800;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .flash-toast--success {
            --flash-bg: #ecfdf5;
            --flash-border: rgba(6, 95, 70, 0.18);
            --flash-fg: #065f46;
            --flash-accent: #10b981;
        }
        .flash-toast--info {
            --flash-bg: #eff6ff;
            --flash-border: rgba(37, 99, 235, 0.18);
            --flash-fg: #1e40af;
            --flash-accent: #2563eb;
        }
        .flash-toast--warning {
            --flash-bg: #fffbeb;
            --flash-border: rgba(245, 158, 11, 0.25);
            --flash-fg: #92400e;
            --flash-accent: #f59e0b;
        }
        .flash-toast--danger {
            --flash-bg: #fee2e2;
            --flash-border: rgba(239, 68, 68, 0.25);
            --flash-fg: #991b1b;
            --flash-accent: #ef4444;
        }
        html.theme-glass .flash-toast {
            --flash-bg: var(--glass-surface-strong);
            --flash-border: var(--glass-border);
            --flash-fg: var(--ink);
            box-shadow: var(--shadow-lg);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
        }
        html.theme-glass .flash-toast--success { --flash-border: rgba(16, 185, 129, 0.38); }
        html.theme-glass .flash-toast--info { --flash-border: rgba(59, 130, 246, 0.38); }
        html.theme-glass .flash-toast--warning { --flash-border: rgba(245, 158, 11, 0.42); }
        html.theme-glass .flash-toast--danger { --flash-border: rgba(248, 113, 113, 0.42); }
        @media (prefers-reduced-motion: reduce) {
            .flash-toast {
                transition: none;
            }
        }
        :is(input, select, textarea).is-invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12) !important;
        }
        .quick-actions-shell {
            padding: 1.25rem;
            border-radius: 30px;
            background: #f1f5f9;
            border: 1px solid #d8e1ec;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
        }
        .quick-actions-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .quick-action-pill {
            min-width: 170px;
            padding: 1.15rem 1.5rem;
            border-radius: 18px;
            font-size: 1.15rem;
            font-weight: 800;
            text-align: center;
            border: 1px solid rgba(15, 118, 110, 0.22);
            transition: 0.2s ease;
        }
        .quick-action-pill:hover {
            transform: translateY(-2px);
        }
        .quick-action-pill.primary {
            background: #1f938b;
            color: #ffffff;
            box-shadow: 0 14px 28px rgba(15, 118, 110, 0.22);
        }
        .quick-action-pill.secondary {
            background: #f6fbfb;
            color: var(--primary);
        }
        .workspace-shell {
            display: grid;
            grid-template-columns: 320px minmax(0, 1fr);
            gap: 1.5rem;
            align-items: start;
        }
        .workspace-side {
            position: sticky;
            top: 6.75rem;
            display: grid;
            gap: 1rem;
        }
        .workspace-panel {
            padding: 1.25rem;
            border-radius: 24px;
            border: 1px solid var(--line);
            background: var(--surface-strong);
            box-shadow: var(--shadow-md);
        }
        .workspace-panel.dark {
            background: #0b2235;
            border-color: rgba(88, 141, 161, 0.28);
            color: #f7fbfd;
        }
        .workspace-panel.dark p,
        .workspace-panel.dark li {
            color: rgba(247, 251, 253, 0.78);
        }
        .workspace-panel h4,
        .workspace-panel h5,
        .workspace-panel h6 {
            margin-bottom: 0.6rem;
            font-weight: 800;
        }
        .workspace-panel p {
            margin-bottom: 0;
            color: var(--muted);
            line-height: 1.8;
        }
        .workspace-main {
            display: grid;
            gap: 1.5rem;
        }
        .workspace-steps {
            display: grid;
            gap: 0.75rem;
            padding: 0;
            margin: 0;
            list-style: none;
        }
        .workspace-step {
            display: grid;
            grid-template-columns: 44px 1fr;
            gap: 0.85rem;
            align-items: start;
            padding: 0.9rem;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: var(--surface);
        }
        .workspace-step-number {
            display: grid;
            place-items: center;
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: #1f938b;
            color: #ffffff;
            font-weight: 800;
        }
        .workspace-step strong {
            display: block;
            margin-bottom: 0.2rem;
        }
        .workspace-step span {
            color: var(--muted);
            font-size: 0.92rem;
            line-height: 1.7;
        }
        .workspace-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 1rem 1.1rem;
            border-radius: 22px;
            background: var(--surface-soft);
            border: 1px solid var(--line);
        }
        .workspace-toolbar h4 {
            margin: 0;
            font-weight: 800;
        }
        .workspace-toolbar p {
            margin: 0.25rem 0 0;
            color: var(--muted);
        }
        .form-section {
            padding: 1.3rem;
            border-radius: 22px;
            border: 1px solid var(--line);
            background: var(--surface-strong);
            box-shadow: var(--shadow-md);
            height: 100%;
            overflow: visible;
        }
        .form-section + .form-section {
            margin-top: 1rem;
        }
        .form-section-title {
            margin-bottom: 1rem;
        }
        .form-section-title h5 {
            margin: 0 0 0.35rem;
            font-weight: 800;
        }
        .form-section-title p {
            margin: 0;
            color: var(--muted);
            line-height: 1.8;
            font-size: 0.92rem;
        }
        .form-section-highlight {
            padding: 1.1rem 1.2rem;
            border-radius: 20px;
            border: 1px solid rgba(15, 118, 110, 0.14);
            background: var(--surface);
        }
        .form-section-highlight h5 {
            margin: 0 0 0.4rem;
            font-weight: 800;
        }
        .form-section-highlight p {
            margin: 0;
            color: var(--muted);
            line-height: 1.9;
        }
        .product-mapping .mapping-panel {
            border-radius: 20px;
            border: 1px solid var(--line);
            background: var(--surface-strong);
            box-shadow: var(--shadow-md);
            display: grid;
            gap: 0.85rem;
            padding: 1rem;
            min-height: 320px;
        }
        .product-mapping .mapping-panel--active {
            border-color: rgba(15, 118, 110, 0.18);
            background: var(--surface-soft);
        }
        .product-mapping .mapping-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .product-mapping .mapping-panel-header h6 {
            margin: 0 0 0.25rem;
            font-weight: 800;
        }
        .product-mapping .mapping-count {
            color: var(--muted);
            font-weight: 700;
            font-size: 0.85rem;
        }
        .product-mapping .mapping-search {
            flex: 1 1 200px;
            max-width: 300px;
        }
        .product-mapping .mapping-search .form-control {
            min-height: 46px;
            border-radius: 14px;
            background: var(--surface-soft);
        }
        .product-mapping .mapping-panel-body {
            display: grid;
            gap: 0.65rem;
            overflow: auto;
            padding-right: 0.2rem;
        }
        .product-mapping .mapping-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            width: 100%;
            padding: 0.85rem 0.9rem;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: var(--surface);
            text-align: left;
            transition: 0.2s ease;
        }
        .product-mapping .mapping-item:hover,
        .product-mapping .mapping-item:focus {
            border-color: rgba(15, 118, 110, 0.32);
            box-shadow: 0 10px 18px rgba(19, 34, 56, 0.08);
        }
        .product-mapping .mapping-item-info {
            display: grid;
            gap: 0.3rem;
        }
        .product-mapping .mapping-item-title {
            font-weight: 800;
            color: var(--ink);
        }
        .product-mapping .mapping-item-meta {
            color: var(--muted);
            font-size: 0.85rem;
        }
        .product-mapping .mapping-item-stores {
            color: var(--muted);
            font-size: 0.82rem;
            display: flex;
            gap: 0.35rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .product-mapping .mapping-item-stores-label {
            color: var(--placeholder);
            font-weight: 800;
        }
        .product-mapping .mapping-store-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.16rem 0.55rem;
            border-radius: 999px;
            background: rgba(15, 118, 110, 0.10);
            color: var(--primary-deep);
            border: 1px solid rgba(15, 118, 110, 0.18);
            font-weight: 800;
            white-space: nowrap;
        }
        .product-mapping .mapping-store-empty {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.16rem 0.55rem;
            border-radius: 999px;
            background: rgba(148, 163, 184, 0.16);
            color: var(--placeholder);
            border: 1px solid rgba(148, 163, 184, 0.28);
            font-weight: 800;
        }
        .product-mapping .mapping-item-action {
            font-weight: 800;
            color: var(--primary-deep);
            background: rgba(15, 118, 110, 0.08);
            border-radius: 999px;
            padding: 0.28rem 0.7rem;
            border: 1px solid rgba(15, 118, 110, 0.18);
            flex: none;
            font-size: 0.85rem;
        }
        .product-mapping .mapping-item-remove {
            background: rgba(185, 28, 28, 0.08);
            color: #9f1239;
            border-color: rgba(185, 28, 28, 0.18);
        }
        .immersive-surface {
            padding: 2rem;
            border-radius: 34px;
            border: 1px solid var(--line);
            background: var(--surface-strong);
            box-shadow: var(--shadow-md);
            overflow: visible;
        }
        @media (max-width: 1200px) {
            .main-sidebar {
                position: relative;
                top: auto;
                min-height: auto;
                grid-template-columns: 1fr;
                align-items: stretch;
                padding: 1rem;
            }
            .topbar-actions {
                justify-content: space-between;
                min-width: 0;
                flex-wrap: wrap;
                width: 100%;
            }
            .topbar-nav {
                justify-content: flex-start;
            }
        }
        @media (max-width: 768px) {
            .main-sidebar {
                padding: 0.95rem;
                gap: 0.85rem;
            }
            .topbar-notifications-menu {
                width: min(92vw, 340px);
            }
            .topbar-brand {
                gap: 0.75rem;
            }
            .topbar-brand-mark {
                width: 48px;
                height: 48px;
                border-radius: 15px;
            }
            .topbar-title {
                font-size: 1.05rem;
            }
            .topbar-actions {
                gap: 0.55rem;
            }
            .topbar-nav {
                justify-content: stretch;
                gap: 0.45rem;
            }
            .topbar-nav-link {
                flex: 1 1 calc(50% - 0.45rem);
                min-height: 38px;
                padding-inline: 0.75rem;
            }
            .topbar-user {
                flex: 1 1 auto;
                justify-content: flex-end;
                gap: 0.55rem;
                min-height: 40px;
                padding: 0;
            }
            .topbar-user-name {
                max-width: 120px;
            }
            .topbar-user-role {
                justify-self: end;
            }
            .topbar-avatar {
                width: 36px;
                height: 36px;
            }
            .topbar-logout-btn {
                min-height: 38px;
                padding-inline: 0.85rem;
                border-radius: 12px;
            }
            .app-main {
                padding: 1rem;
            }
            .workspace-shell {
                grid-template-columns: 1fr;
            }
            .workspace-side {
                position: static;
            }
            .main-sidebar {
                border-radius: 24px;
            }
            .detail-grid {
                grid-template-columns: 1fr;
            }
            .immersive-surface {
                padding: 1.25rem;
                border-radius: 24px;
            }
            .employee-grid {
                grid-template-columns: 1fr;
            }
        }
        .main-sidebar {
            background: #123244;
            padding: 0;
            border: none;
            box-shadow: 0 24px 40px rgba(15, 23, 42, 0.45);
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 0;
            border-radius: 0 !important;
        }
        .main-sidebar::before {
            display: none;
        }
        .main-sidebar .topbar-brand {
            padding: 1.6rem 1.5rem 1.35rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
        }
        .main-sidebar .topbar-brand-mark {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #2563eb;
            border: none;
            box-shadow: 0 18px 30px rgba(37, 99, 235, 0.35);
        }
        .main-sidebar .topbar-brand-mark svg {
            width: 28px;
            height: 28px;
            stroke: #fff;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .main-sidebar .topbar-title {
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: 0.02em;
        }
        .main-sidebar .topbar-subtitle {
            color: rgba(226, 232, 240, 0.6);
            font-weight: 600;
        }
        .main-sidebar .topbar-nav {
            flex-direction: column;
            align-items: stretch;
            justify-content: flex-start;
            gap: 0.6rem;
            padding: 1.1rem 1.25rem;
            flex: 1 1 auto;
            overflow-y: auto;
        }
        .main-sidebar .topbar-nav-link {
            width: 100%;
            justify-content: flex-start;
            padding: 0.85rem 1rem;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            color: rgba(226, 232, 240, 0.9);
            background: transparent;
        }
        .main-sidebar .topbar-nav-link:hover,
        .main-sidebar .topbar-nav-link:focus {
            background: rgba(148, 163, 184, 0.12);
            color: #ffffff;
            transform: translateX(2px);
        }
        .main-sidebar .topbar-nav-link.active {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 14px 26px rgba(37, 99, 235, 0.28);
            border-color: transparent;
            transform: scale(1.02);
        }
        .main-sidebar .topbar-nav-icon {
            width: 20px;
            height: 20px;
            opacity: 0.95;
        }
        .main-sidebar .topbar-nav-icon svg {
            width: 20px;
            height: 20px;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .main-sidebar .topbar-actions {
            margin-top: auto;
            padding: 1.35rem;
            border-top: 1px solid rgba(148, 163, 184, 0.18);
        }
        .main-sidebar .topbar-notifications,
        .main-sidebar .topbar-user {
            display: none;
        }
        .main-sidebar .topbar-logout-form {
            width: 100%;
        }
        .main-sidebar .topbar-logout-btn {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            justify-content: center;
            border-radius: 16px;
            border: 1.5px solid rgba(239, 68, 68, 0.5);
            color: #fca5a5;
            background: rgba(239, 68, 68, 0.06);
            padding: 0.95rem 1rem;
            font-weight: 700;
        }
        .main-sidebar .topbar-logout-btn:hover,
        .main-sidebar .topbar-logout-btn:focus {
            background: rgba(239, 68, 68, 0.16);
            color: #fecaca;
            border-color: rgba(239, 68, 68, 0.6);
        }
        .main-sidebar .topbar-logout-icon {
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .main-sidebar .topbar-logout-icon svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        @media (min-width: 1201px) {
            .app-shell {
                grid-template-columns: 18rem minmax(0, 1fr);
            }
        }
    </style>
</head>
<body data-authenticated="{{ auth()->check() ? '1' : '0' }}">
    <div class="page-loader" data-page-loader aria-hidden="true">
        <span class="page-loader__spinner" aria-hidden="true"></span>
    </div>
    @php
        $errors = $errors ?? new \Illuminate\Support\ViewErrorBag();
        $currentRoute = request()->route()?->getName();
        $currentUser = auth()->user();
        $lockedSystemAdmin = collect((array) config('locked_users', []))
            ->first(fn ($item) => (string) ($item['role'] ?? ($item['job_title'] ?? '')) === 'admin' || (string) ($item['job_title'] ?? '') === 'system administrator');
        $systemAdminName = (string) ($lockedSystemAdmin['name'] ?? '');
        if ($systemAdminName === '') {
            $systemAdminName = \Illuminate\Support\Facades\Cache::remember(
                'layout_system_admin_name_v1',
                now()->addHours(12),
                fn () => \App\Models\User::query()
                    ->where('role', 'admin')
                    ->orderBy('id')
                    ->value('name') ?: 'System Administrator'
            );
        }
        $systemAdminLabel = 'System Administrator';
        $currentUserRole = match($currentUser?->role) {
            'admin' => 'System Administrator',
            'department_manager' => 'Department Manager',
            'store_manager' => 'Store Manager',
            'store_employee' => 'Store Employee',
            default => null,
        };
        $isSystemAdmin = $currentUser
            && (
                $currentUser->role === 'admin'
            );
        $canViewStores = $currentUser?->hasPermission('view_store') ?? false;
        $canViewStoreDetails = $currentUser?->hasPermission('view_store_details') ?? false;
        $canCreateStore = $currentUser?->hasPermission('create_store') ?? false;
        $canEditStore = $currentUser?->hasPermission('edit_store') ?? false;
        $canDeleteStore = $currentUser?->hasPermission('delete_store') ?? false;
        $canManageProducts = $currentUser?->hasPermission('manage_store_products') ?? false;
        $canAssignStaff = $currentUser?->hasPermission('assign_staff_to_store') ?? false;
        $canManageStaff = $currentUser?->hasPermission('manage_store_staff') ?? false;
        $canSearchStores = $currentUser?->hasPermission('search_store') ?? false;
        $assignmentAlerts = collect();
        $unreadAssignmentAlerts = 0;
        $pendingRequests = collect();
        $pendingRequestsCount = 0;
        $myPendingRequests = collect();
        $myPendingRequestsCount = 0;
        $pendingIndicatorCount = 0;
        $storeCacheVersion = (int) \Illuminate\Support\Facades\Cache::get('store_search_version', 1);
        $routeStore = request()->route('store');
        if ($routeStore instanceof \App\Models\Store) {
            $navStore = (int) $routeStore->id;
        } else {
            $navStore = \Illuminate\Support\Facades\Cache::remember(
                'layout_nav_store_id:v2:'.$storeCacheVersion.':user:'.(int) ($currentUser?->id ?? 0),
                now()->addSeconds(60),
                function () use ($currentUser, $isSystemAdmin) {
                    if ($isSystemAdmin) {
                        return (int) (\App\Models\Store::query()->orderBy('id')->value('id') ?? 0);
                    }

                    if (! $currentUser) {
                        return 0;
                    }

                    $managedId = (int) ($currentUser->managedStore()->value('id') ?? 0);
                    if ($managedId > 0) {
                        return $managedId;
                    }

                    $assignedId = (int) ($currentUser->stores()->orderBy('stores.id')->value('stores.id') ?? 0);
                    if ($assignedId > 0) {
                        return $assignedId;
                    }

                    if (! $currentUser->hasPermission('view_store')) {
                        return 0;
                    }

                    return (int) (app(\App\Services\Access\StoreScopeService::class)
                        ->scopeStoreQuery(\App\Models\Store::query(), $currentUser)
                        ->orderBy('id')
                        ->value('id') ?? 0);
                }
            );

            if ((int) $navStore <= 0) {
                $navStore = null;
            }
        }
    @endphp
    <div class="app-shell">
        <aside class="main-sidebar">
            <div class="topbar-brand">
                <span class="topbar-brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"></path>
                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                        <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"></path>
                        <path d="M2 7h20"></path>
                        <path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"></path>
                    </svg>
                </span>
                <div class="topbar-brand-copy">
                    <h2 class="topbar-title">Store</h2>
                    <div class="topbar-subtitle">Branch Management</div>
                </div>
            </div>
            <nav class="topbar-nav" aria-label="Primary navigation">
                <a href="{{ route('dashboard', [], false) }}" class="topbar-nav-link {{ $currentRoute === 'dashboard' ? 'active' : '' }}">
                    <span class="topbar-nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
                        </svg>
                    </span>
                    <span class="topbar-nav-label">Dashboard</span>
                </a>
                @if ($canViewStores || $canViewStoreDetails)
                    <a href="{{ route('stores.index', [], false) }}" class="topbar-nav-link {{ str_starts_with((string) $currentRoute, 'stores.index') || $currentRoute === 'stores.show' ? 'active' : '' }}">
                        <span class="topbar-nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"></path>
                                <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                                <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"></path>
                                <path d="M2 7h20"></path>
                                <path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"></path>
                            </svg>
                        </span>
                        <span class="topbar-nav-label">Branch Directory</span>
                    </a>
                @endif
                @if ($canCreateStore)
                    <a href="{{ route('stores.create', [], false) }}" class="topbar-nav-link {{ $currentRoute === 'stores.create' ? 'active' : '' }}">
                        <span class="topbar-nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M8 12h8"></path>
                                <path d="M12 8v8"></path>
                            </svg>
                        </span>
                        <span class="topbar-nav-label">Add Branch</span>
                    </a>
                @endif
                @if ($navStore && $canManageProducts)
                    <a href="{{ route('stores.products', $navStore, false) }}" class="topbar-nav-link {{ str_starts_with((string) $currentRoute, 'stores.products') ? 'active' : '' }}">
                        <span class="topbar-nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" style="width:20px;height:20px;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                            </svg>
                        </span>
                        <span class="topbar-nav-label">Link Products</span>
                    </a>
                    @if ($canAssignStaff || $canManageStaff)
                        <a href="{{ route('stores.assignments', $navStore, false) }}" class="topbar-nav-link {{ $currentRoute === 'stores.assignments' ? 'active' : '' }}">
                            <span class="topbar-nav-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </span>
                            <span class="topbar-nav-label">Assign Staff</span>
                        </a>
                    @endif
                @endif
            </nav>
            <div class="topbar-actions">
                @include('layouts.partials.topbar-actions')
            </div>
        </aside>
        <main class="app-main">
            <header class="content-topbar" aria-label="Top bar">
                <div class="content-topbar-search-row">
                    @if ($canSearchStores)
                        <form class="content-topbar-search" action="{{ route('search', [], false) }}" method="GET" role="search" novalidate>
                            <div class="content-topbar-search-inner">
                                <span class="content-topbar-search-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="m21 21-4.3-4.3"></path>
                                    </svg>
                                </span>
                                <input
                                    type="search"
                                    name="q"
                                    value="{{ (string) request('q', '') }}"
                                    class="content-topbar-search-input"
                                    placeholder="Search employees, products, branches..."
                                    autocomplete="off"
                                    aria-label="Search"
                                >
                            </div>
                        </form>
                    @endif
                    @include('layouts.partials.topbar-actions', [
                        'showLogout' => false,
                        'showUser' => false,
                    ])
                </div>
                <div class="content-topbar-actions">
                    <button type="button" class="topbar-theme-btn" data-theme-toggle aria-label="Toggle theme">
                        <svg class="topbar-theme-icon topbar-theme-icon--soft" viewBox="0 0 24 24">
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
                        <svg class="topbar-theme-icon topbar-theme-icon--glass" viewBox="0 0 24 24">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3A7 7 0 0 0 21 12.79z"></path>
                        </svg>
                    </button>
                    @include('layouts.partials.topbar-actions', [
                        'showLogout' => false,
                    ])
                </div>
            </header>
            @php
                $flashToasts = [
                    'success' => [
                        'class' => 'flash-toast--success',
                        'icon' => 'check',
                    ],
                    'error' => [
                        'class' => 'flash-toast--danger',
                        'icon' => 'x',
                    ],
                    'info' => [
                        'class' => 'flash-toast--info',
                        'icon' => 'info',
                    ],
                    'warning' => [
                        'class' => 'flash-toast--warning',
                        'icon' => 'warning',
                    ],
                ];
            @endphp
            @foreach ($flashToasts as $flashKey => $meta)
                @if (session($flashKey))
                    <div class="flash-toast {{ $meta['class'] }}" data-flash-toast role="status" aria-live="polite">
                        <div class="flash-toast__icon" aria-hidden="true">
                            @if ($meta['icon'] === 'check')
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 6L9 17l-5-5"></path>
                                </svg>
                            @elseif ($meta['icon'] === 'x')
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 6 6 18"></path>
                                    <path d="M6 6l12 12"></path>
                                </svg>
                            @elseif ($meta['icon'] === 'info')
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 16v-5"></path>
                                    <path d="M12 8h.01"></path>
                                    <circle cx="12" cy="12" r="9"></circle>
                                </svg>
                            @else
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10.3 3.7l-7.7 13a2 2 0 001.7 3h15.4a2 2 0 001.7-3l-7.7-13a2 2 0 00-3.4 0z"></path>
                                    <path d="M12 9v4"></path>
                                    <path d="M12 17h.01"></path>
                                </svg>
                            @endif
                        </div>
                        <div class="flash-toast__text">{{ session($flashKey) }}</div>
                    </div>
                @endif
            @endforeach
            @if (session('transfers'))
                @php
                    $transfers = session('transfers');
                    $transferCount = is_array($transfers) ? count($transfers) : 1;
                    $transferMessage = "Staff assignments updated successfully.";
                @endphp
                <div class="flash-toast flash-toast--info" data-flash-toast role="status" aria-live="polite">
                    <div class="flash-toast__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 16v-5"></path>
                            <path d="M12 8h.01"></path>
                            <circle cx="12" cy="12" r="9"></circle>
                        </svg>
                    </div>
                    <div class="flash-toast__text">{{ $transferMessage }}</div>
                </div>
            @endif
            @if ($errors->any())
                <div class="flash-toast flash-toast--warning" data-flash-toast role="status" aria-live="polite">
                    <div class="flash-toast__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 9v4"></path>
                            <path d="M12 17h.01"></path>
                            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path>
                        </svg>
                    </div>
                    <div class="flash-toast__text">{{ $errors->first() }}</div>
                </div>
            @endif
            <div class="delete-modal-backdrop" id="delete-modal" aria-hidden="true">
                <div class="delete-modal" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
                    <div class="delete-modal-header">
                        <div class="delete-modal-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 9v4"></path>
                                <path d="M12 17h.01"></path>
                                <path d="M10.3 3.7l-7.7 13a2 2 0 001.7 3h15.4a2 2 0 001.7-3l-7.7-13a2 2 0 00-3.4 0z"></path>
                            </svg>
                        </div>
                        <div class="delete-modal-title" id="delete-modal-title">Confirm deletion</div>
                    </div>
                    <div class="delete-modal-body">
                        Do you want to delete <strong id="delete-modal-store-name">this store</strong>?
                        This action is permanent and cannot be undone.
                    </div>
                    <form method="POST" id="delete-modal-form">
                        @csrf
                        @method('DELETE')
                        <div class="delete-modal-actions">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="delete-modal-cancel">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-solid-danger">Confirm deletion</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="content-shell">
                @yield('content')
            </div>
        </main>
    </div>
    <script>
        (function () {
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
            function markShareIntent(tabId) {
                try {
                    localStorage.setItem(SHARE_KEY, JSON.stringify({ tabId: tabId, ts: Date.now() }));
                } catch (e) {
                }
            }
            function resolveTabId() {
                const currentUrl = new URL(window.location.href);
                const fromUrl = sanitizeTabId(currentUrl.searchParams.get(TAB_PARAM));
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
                return tabId;
            }
            const tabId = resolveTabId();
            if (!tabId) return;
            const url = new URL(window.location.href);
            const currentParam = sanitizeTabId(url.searchParams.get(TAB_PARAM));
            if (currentParam !== tabId) {
                url.searchParams.set(TAB_PARAM, tabId);
                window.location.replace(url.toString());
                return;
            }
            window.__rgTabId = tabId;
            function withTab(inputUrl) {
                if (!inputUrl) return inputUrl;
                let parsed;
                try {
                    parsed = new URL(inputUrl, window.location.origin);
                } catch (e) {
                    return inputUrl;
                }
                if (parsed.protocol !== 'http:' && parsed.protocol !== 'https:') return inputUrl;
                if (parsed.origin !== window.location.origin) return inputUrl;
                if (parsed.searchParams.has(TAB_PARAM)) return parsed.toString();
                parsed.searchParams.set(TAB_PARAM, tabId);
                return parsed.toString();
            }
            window.__rgWithTab = withTab;
            document.querySelectorAll('a[data-keep-tab]').forEach(function (anchor) {
                const updated = withTab(anchor.href);
                if (updated && updated !== anchor.href) {
                    anchor.href = updated;
                }
            });
            function getAuthToken() {
                return sessionStorage.getItem(AUTH_TOKEN_KEY) || '';
            }
            function setAuthToken(token) {
                if (token) {
                    sessionStorage.setItem(AUTH_TOKEN_KEY, token);
                } else {
                    sessionStorage.removeItem(AUTH_TOKEN_KEY);
                }
            }
            function attachAuthHeader(headers) {
                const token = getAuthToken();
                if (!token) return;
                if (!headers.has('Authorization')) {
                    headers.set('Authorization', 'Bearer ' + token);
                }
            }
            function bootstrapAuthToken() {
                const body = document.body;
                if (!body || body.dataset.authenticated !== '1') {
                    return;
                }
                if (getAuthToken()) {
                    return;
                }
                const now = Date.now();
                const last = Number(sessionStorage.getItem(AUTH_TOKEN_CHECK_KEY) || 0);
                if (now - last < 1500) {
                    return;
                }
                sessionStorage.setItem(AUTH_TOKEN_CHECK_KEY, String(now));
                const tokenUrl = withTab("{{ route('auth.token.issue', [], false) }}");
                fetch(tokenUrl, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Tab-Id': tabId,
                    },
                })
                    .then((response) => (response && response.ok ? response.json() : null))
                    .then((payload) => {
                        if (payload && payload.token) {
                            setAuthToken(payload.token);
                        }
                    })
                    .catch(() => {});
            }
            bootstrapAuthToken();
            if (typeof window.fetch === 'function') {
                const originalFetch = window.fetch.bind(window);
                window.fetch = function (input, init) {
                    const headers = new Headers((init && init.headers) || {});
                    headers.set('X-Tab-Id', tabId);
                    attachAuthHeader(headers);
                    if (typeof input === 'string') {
                        return originalFetch(withTab(input), Object.assign({}, init, { headers }));
                    }
                    if (input instanceof Request) {
                        const requestWithTab = new Request(withTab(input.url), input);
                        const finalRequest = new Request(requestWithTab, { headers });
                        return originalFetch(finalRequest);
                    }
                    return originalFetch(input, Object.assign({}, init, { headers }));
                };
            }
            if (window.jQuery) {
                window.jQuery.ajaxPrefilter(function (options, originalOptions, jqXHR) {
                    if (!options || !options.url) return;
                    options.url = withTab(options.url);
                    jqXHR.setRequestHeader('X-Tab-Id', tabId);
                    const token = getAuthToken();
                    if (token) {
                        jqXHR.setRequestHeader('Authorization', 'Bearer ' + token);
                    }
                });
            }
            function isSameOrigin(href) {
                try {
                    const parsed = new URL(href, window.location.origin);
                    return parsed.origin === window.location.origin;
                } catch (e) {
                    return false;
                }
            }
            function shouldAppendTab(anchor, event) {
                if (!anchor) return false;
                if (anchor.hasAttribute('data-no-tab')) return false;
                const href = anchor.getAttribute('href') || '';
                if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:')) {
                    return false;
                }
                if (!isSameOrigin(anchor.href)) return false;
                const opensNewTab = anchor.target === '_blank' || event.metaKey || event.ctrlKey || event.shiftKey || event.button === 1;
                if (opensNewTab && !anchor.hasAttribute('data-keep-tab')) {
                    return false;
                }
                return true;
            }
            function handleAnchor(event) {
                const anchor = event.target.closest('a');
                if (!anchor) return;
                const opensNewTab = anchor.target === '_blank' || event.metaKey || event.ctrlKey || event.shiftKey || event.button === 1;
                if (opensNewTab && anchor.hasAttribute('data-keep-tab')) {
                    markShareIntent(tabId);
                }
                if (!shouldAppendTab(anchor, event)) return;
                const updated = withTab(anchor.href);
                if (updated && updated !== anchor.href) {
                    anchor.href = updated;
                }
            }
            document.addEventListener('click', handleAnchor, true);
            document.addEventListener('auxclick', handleAnchor, true);
            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!form || !(form instanceof HTMLFormElement)) return;
                const action = form.getAttribute('action') || window.location.href;
                const updatedAction = withTab(action);
                if (updatedAction && updatedAction !== action) {
                    form.setAttribute('action', updatedAction);
                }
                const actionUrl = action ? new URL(action, window.location.origin) : null;
                if (actionUrl && actionUrl.pathname === '/logout') {
                    sessionStorage.removeItem(AUTH_TOKEN_KEY);
                    sessionStorage.removeItem(AUTH_TOKEN_CHECK_KEY);
                }
            }, true);
        })();
        $(function () {
            const $flashes = $('[data-flash-toast]');
            if ($flashes.length) {
                $flashes.each(function () {
                    const $toast = $(this);
                    const hide = function () {
                        $toast.addClass('is-hidden');
                    };
                    setTimeout(hide, 3200);
                });
            }
            function showClientToast(type, message) {
                const existing = document.querySelector('[data-client-toast]');
                if (existing) existing.remove();
                const toast = document.createElement('div');
                toast.className = 'flash-toast flash-toast--' + (type || 'warning');
                toast.setAttribute('data-flash-toast', '');
                toast.setAttribute('data-client-toast', '1');
                toast.setAttribute('role', 'status');
                toast.setAttribute('aria-live', 'polite');
                const icon = document.createElement('div');
                icon.className = 'flash-toast__icon';
                icon.setAttribute('aria-hidden', 'true');
                icon.innerHTML =
                    type === 'success'
                        ? '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>'
                        : type === 'danger'
                            ? '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="M6 6l12 12"></path></svg>'
                            : '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4"></path><path d="M12 17h.01"></path><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path></svg>';
                const text = document.createElement('div');
                text.className = 'flash-toast__text';
                text.textContent = message || 'Please complete the required information to continue.';
                toast.appendChild(icon);
                toast.appendChild(text);
                document.body.appendChild(toast);
                setTimeout(function () {
                    toast.classList.add('is-hidden');
                }, 3200);
                setTimeout(function () {
                    toast.remove();
                }, 4200);
            }
            function isElementVisible(el) {
                if (!el) return false;
                if (el.disabled) return false;
                if (el.type === 'hidden') return false;
                const rect = el.getBoundingClientRect();
                return (rect.width > 0 && rect.height > 0);
            }
            function markInvalid(el) {
                if (!el) return;
                el.classList.add('is-invalid');
                el.setAttribute('aria-invalid', 'true');
                const customSelect = el.closest('[data-custom-select]');
                const trigger = customSelect?.querySelector('[data-custom-select-trigger]');
                if (trigger) {
                    trigger.classList.add('is-invalid');
                }
            }
            function unmarkInvalid(el) {
                if (!el) return;
                el.classList.remove('is-invalid');
                el.removeAttribute('aria-invalid');
                const customSelect = el.closest('[data-custom-select]');
                const trigger = customSelect?.querySelector('[data-custom-select-trigger]');
                if (trigger) {
                    trigger.classList.remove('is-invalid');
                }
            }
            document.addEventListener('input', function (event) {
                const el = event.target;
                if (!el || !el.classList || !el.classList.contains('is-invalid')) return;
                if (el.required && el.willValidate && !el.validity.valueMissing) {
                    unmarkInvalid(el);
                }
            }, true);
            document.addEventListener('change', function (event) {
                const el = event.target;
                if (!el || !el.classList || !el.classList.contains('is-invalid')) return;
                if (el.required && el.willValidate && !el.validity.valueMissing) {
                    unmarkInvalid(el);
                }
            }, true);
            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!form || !(form instanceof HTMLFormElement)) return;
                const fields = Array.from(form.querySelectorAll('input, select, textarea'));
                const missing = fields.filter(function (el) {
                    if (!el.required) return false;
                    if (!el.willValidate) return false;
                    if (!isElementVisible(el)) return false;
                    return el.validity && el.validity.valueMissing;
                });
                if (!missing.length) return;
                missing.forEach(markInvalid);
                if (form.noValidate) {
                    event.preventDefault();
                    showClientToast('warning', 'Please complete the required information to continue.');
                    missing[0]?.focus?.();
                }
            }, true);
            function initCustomSelects(scope) {
                const root = scope || document;
                const customSelects = Array.from(root.querySelectorAll('[data-custom-select]')).filter(function (el) {
                    return !el.dataset.customSelectReady;
                });
                if (!customSelects.length) return;
                customSelects.forEach(function (selectRoot) {
                    selectRoot.dataset.customSelectReady = 'true';
                    const trigger = selectRoot.querySelector('[data-custom-select-trigger]');
                    const input = selectRoot.querySelector('[data-custom-select-input]');
                    const value = selectRoot.querySelector('[data-custom-select-value]');
                    const options = Array.from(selectRoot.querySelectorAll('.custom-select-option'));
                    const searchInput = selectRoot.querySelector('[data-custom-select-search]');
                    const emptyState = selectRoot.querySelector('[data-custom-select-empty]');
                    const placeholderLabel = value ? String(value.textContent || '').trim() : '';
                    let lastPointerToggleAt = 0;
                    function filterOptions(query) {
                        if (!searchInput) return;
                        const term = (query || '').trim().toLowerCase();
                        let visibleCount = 0;
                        options.forEach(function (option) {
                            const label = (option.dataset.label || option.textContent || '').trim().toLowerCase();
                            const isMatch = term === '' || label.includes(term);
                            option.style.display = isMatch ? '' : 'none';
                            if (isMatch) {
                                visibleCount += 1;
                            }
                        });
                        if (emptyState) {
                            emptyState.style.display = visibleCount ? 'none' : 'block';
                        }
                    }
                    function updateMenuPosition() {
                        selectRoot.classList.remove('is-dropup');
                    }
                    function syncSelectionFromInput() {
                        if (!input) return;
                        const currentValue = String(input.value || '');
                        let selectedOption = null;
                        options.forEach(function (option) {
                            const optionValue = String(option.dataset.value || '');
                            const isSelected = optionValue === currentValue;
                            option.classList.toggle('is-selected', isSelected);
                            if (isSelected) {
                                selectedOption = option;
                            }
                        });
                        if (value) {
                            if (selectedOption) {
                                value.textContent = selectedOption.dataset.label || selectedOption.textContent.trim();
                            } else if (placeholderLabel) {
                                value.textContent = placeholderLabel;
                            }
                        }
                    }
                    function toggleMenu() {
                        if (selectRoot.classList.contains('is-open')) {
                            closeMenu();
                        } else {
                            document.querySelectorAll('[data-custom-select].is-open').forEach(function (openSelect) {
                                if (openSelect !== selectRoot) {
                                    openSelect.classList.remove('is-open');
                                    openSelect.querySelector('[data-custom-select-trigger]')?.setAttribute('aria-expanded', 'false');
                                }
                            });
                            openMenu();
                        }
                    }
                    function closeMenu() {
                        selectRoot.classList.remove('is-open');
                        selectRoot.classList.remove('is-dropup');
                        trigger?.setAttribute('aria-expanded', 'false');
                        if (searchInput) {
                            searchInput.value = '';
                            filterOptions('');
                        }
                    }
                    function openMenu() {
                        selectRoot.classList.add('is-open');
                        trigger?.setAttribute('aria-expanded', 'true');
                        requestAnimationFrame(updateMenuPosition);
                        if (searchInput) {
                            searchInput.value = '';
                            filterOptions('');
                            requestAnimationFrame(function () {
                                searchInput.focus();
                            });
                        }
                    }
                    trigger?.addEventListener('pointerdown', function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        lastPointerToggleAt = Date.now();
                        toggleMenu();
                    });
                    trigger?.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        if (Date.now() - lastPointerToggleAt < 350) {
                            return;
                        }
                        toggleMenu();
                    });
                    options.forEach(function (option) {
                        option.addEventListener('click', function (event) {
                            event.preventDefault();
                            const selectedLabel = option.dataset.label || option.textContent.trim();
                            const selectedValue = option.dataset.value || '';
                            if (input) {
                                input.value = selectedValue;
                                input.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                            if (value) {
                                value.textContent = selectedLabel;
                            }
                            options.forEach(function (item) {
                                item.classList.remove('is-selected');
                            });
                            option.classList.add('is-selected');
                            closeMenu();
                        });
                    });
                    input?.addEventListener('change', syncSelectionFromInput);
                    syncSelectionFromInput();
                    if (searchInput) {
                        searchInput.addEventListener('input', function () {
                            filterOptions(searchInput.value);
                        });
                    }
                    window.addEventListener('resize', function () {
                        if (selectRoot.classList.contains('is-open')) {
                            updateMenuPosition();
                        }
                    });
                });
                document.addEventListener('click', function (event) {
                    document.querySelectorAll('[data-custom-select].is-open').forEach(function (selectRoot) {
                        if (!selectRoot.contains(event.target)) {
                            selectRoot.classList.remove('is-open');
                            selectRoot.querySelector('[data-custom-select-trigger]')?.setAttribute('aria-expanded', 'false');
                        }
                    });
                });
            }
            initCustomSelects(document);
            function normalizePhoneValue(value) {
                const digits = String(value || '').replace(/\D+/g, '');
                if (!digits) return '';
                let normalized = digits;
                if (normalized.startsWith('963')) {
                    normalized = normalized.slice(3);
                }
                if (normalized.startsWith('09')) {
                    return normalized;
                }
                if (normalized.startsWith('9')) {
                    return '0' + normalized;
                }
                if (normalized.startsWith('0')) {
                    normalized = normalized.slice(1);
                }
                if (!normalized) {
                    return '';
                }
                return '09' + normalized;
            }
            function applyPhoneNormalization(scope) {
                const root = scope || document;
                const fields = Array.from(root.querySelectorAll('input[name="phone"]'));
                if (!fields.length) return;
                fields.forEach(function (field) {
                    if (field.dataset.phoneNormalized === '1') return;
                    field.dataset.phoneNormalized = '1';
                    const apply = function () {
                        const normalized = normalizePhoneValue(field.value);
                        if (normalized) {
                            field.value = normalized;
                        }
                    };
                    apply();
                    field.addEventListener('blur', apply);
                    field.addEventListener('change', apply);
                });
            }
            applyPhoneNormalization(document);
            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!form || !(form instanceof HTMLFormElement)) return;
                form.querySelectorAll('input[name="phone"]').forEach(function (field) {
                    const normalized = normalizePhoneValue(field.value);
                    if (normalized) {
                        field.value = normalized;
                    }
                });
            }, true);
            const $modal = $('#delete-modal');
            const $form = $('#delete-modal-form');
            const $name = $('#delete-modal-store-name');
            function openDeleteModal(action, name) {
                $form.attr('action', action);
                $name.text(name || 'this store');
                $modal.addClass('is-visible').attr('aria-hidden', 'false');
                $('body').addClass('modal-open');
            }
            function closeDeleteModal() {
                $modal.removeClass('is-visible').attr('aria-hidden', 'true');
                $('body').removeClass('modal-open');
            }
            $(document).on('click', '.js-delete-open', function (event) {
                event.preventDefault();
                const action = $(this).data('action');
                const name = $(this).data('name');
                if (action) {
                    openDeleteModal(action, name);
                }
            });
            $('#delete-modal-cancel').on('click', function (event) {
                event.preventDefault();
                closeDeleteModal();
            });
            $modal.on('click', function (event) {
                if ($(event.target).is('#delete-modal')) {
                    closeDeleteModal();
                }
            });
            $(document).on('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeDeleteModal();
                }
            });
            const pageLoader = document.querySelector('[data-page-loader]');
            if (pageLoader) {
                let loaderTimer = null;
                const showLoader = () => {
                    if (loaderTimer) return;
                    loaderTimer = window.setTimeout(() => {
                        pageLoader.classList.add('is-visible');
                    }, 150);
                };
                const hideLoader = () => {
                    if (loaderTimer) {
                        window.clearTimeout(loaderTimer);
                        loaderTimer = null;
                    }
                    pageLoader.classList.remove('is-visible');
                };
                window.addEventListener('pageshow', hideLoader);
                window.addEventListener('load', () => {
                    requestAnimationFrame(hideLoader);
                });
                document.addEventListener('DOMContentLoaded', hideLoader);
                document.addEventListener('click', function (event) {
                    const link = event.target.closest('a');
                    if (!link) return;
                    if (link.target === '_blank' || link.hasAttribute('download')) return;
                    if (link.dataset.keepTab !== undefined || link.dataset.noLoader !== undefined) return;
                    const href = link.getAttribute('href');
                    if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
                    const url = new URL(link.href, window.location.href);
                    if (url.origin !== window.location.origin) return;
                    showLoader();
                });
                document.addEventListener('submit', function (event) {
                    if (event.defaultPrevented) {
                        hideLoader();
                        return;
                    }
                    showLoader();
                    setTimeout(() => {
                        if (event.defaultPrevented) {
                            hideLoader();
                        }
                    }, 0);
                });
                document.addEventListener('invalid', function () {
                    hideLoader();
                }, true);
                window.addEventListener('beforeunload', showLoader);
            }
            const themeButtons = Array.from(document.querySelectorAll('[data-theme-toggle]'));
            function setUiTheme(theme) {
                const next = theme === 'glass' ? 'glass' : 'soft';
                try {
                    localStorage.setItem('ui_theme', next);
                } catch (e) {}
                document.documentElement.dataset.uiTheme = next;
                document.documentElement.classList.toggle('theme-glass', next === 'glass');
                document.documentElement.classList.toggle('theme-soft', next === 'soft');
                themeButtons.forEach((btn) => {
                    btn.setAttribute('aria-pressed', next === 'glass' ? 'true' : 'false');
                    btn.setAttribute('title', next === 'glass' ? 'Glass Blur' : 'Soft Light');
                });
            }
            themeButtons.forEach((btn) => {
                btn.addEventListener('click', () => {
                    const isGlass = document.documentElement.classList.contains('theme-glass');
                    setUiTheme(isGlass ? 'soft' : 'glass');
                });
            });
            if (themeButtons.length) {
                const isGlass = document.documentElement.classList.contains('theme-glass');
                themeButtons.forEach((btn) => {
                    btn.setAttribute('aria-pressed', isGlass ? 'true' : 'false');
                    btn.setAttribute('title', isGlass ? 'Glass Blur' : 'Soft Light');
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
