<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $settings->site_name ?: $tenant->teacher_name }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 px-4 font-sans antialiased">
    <div class="max-w-md text-center">
        @if ($settings->maintenanceImageUrl())
            <img src="{{ $settings->maintenanceImageUrl() }}" alt="" class="mx-auto mb-6 h-40 w-40 object-contain">
        @endif

        <h1 class="text-xl font-bold text-slate-900">
            {{ $tenant->website_status === 'maintenance' ? 'الموقع تحت الصيانة' : 'الموقع غير متاح حاليًا' }}
        </h1>

        <p class="mt-3 text-sm text-slate-500">
            {{ $settings->maintenance_message ?: 'برجاء المحاولة مرة أخرى لاحقًا.' }}
        </p>
    </div>
</body>
</html>
