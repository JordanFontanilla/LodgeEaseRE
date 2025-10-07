{{-- Favicon Component --}}
{{-- Usage: @include('components.favicon') --}}

<!-- Primary Favicon with cache busting -->
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}?v={{ time() }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon.png') }}?v={{ time() }}">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">

<!-- Apple Touch Icons -->
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/LodgeEaseLogo.png') }}?v={{ time() }}">
<link rel="apple-touch-icon" sizes="152x152" href="{{ asset('images/LodgeEaseLogo.png') }}?v={{ time() }}">
<link rel="apple-touch-icon" sizes="144x144" href="{{ asset('images/LodgeEaseLogo.png') }}?v={{ time() }}">
<link rel="apple-touch-icon" sizes="120x120" href="{{ asset('images/LodgeEaseLogo.png') }}?v={{ time() }}">
<link rel="apple-touch-icon" sizes="114x114" href="{{ asset('images/LodgeEaseLogo.png') }}?v={{ time() }}">
<link rel="apple-touch-icon" sizes="76x76" href="{{ asset('images/LodgeEaseLogo.png') }}?v={{ time() }}">
<link rel="apple-touch-icon" sizes="72x72" href="{{ asset('images/LodgeEaseLogo.png') }}?v={{ time() }}">
<link rel="apple-touch-icon" sizes="60x60" href="{{ asset('images/LodgeEaseLogo.png') }}?v={{ time() }}">
<link rel="apple-touch-icon" sizes="57x57" href="{{ asset('images/LodgeEaseLogo.png') }}?v={{ time() }}">

<!-- Android Chrome Icons -->
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/LodgeEaseLogo.png') }}?v={{ time() }}">
<link rel="icon" type="image/png" sizes="128x128" href="{{ asset('images/LodgeEaseLogo.png') }}?v={{ time() }}">

<!-- Windows Metro Tiles -->
<meta name="msapplication-TileImage" content="{{ asset('images/LodgeEaseLogo.png') }}?v={{ time() }}">
<meta name="msapplication-TileColor" content="#667eea">

<!-- Theme Colors -->
<meta name="theme-color" content="#667eea">
<meta name="msapplication-navbutton-color" content="#667eea">
<meta name="apple-mobile-web-app-status-bar-style" content="#667eea">
