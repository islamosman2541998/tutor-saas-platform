<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white font-sans text-slate-900 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 text-center">
        <h1 class="mb-4 text-3xl font-bold text-indigo-600">{{ config('app.name') }}</h1>
        <p class="mb-8 max-w-md text-slate-500">
            منصة متكاملة لإدارة المدرسين الذين يقدمون دروسًا خصوصية — حساب مستقل، طلاب، مجموعات، حضور، مدفوعات، وموقع تعريفي لكل مدرس.
        </p>
        <a href="{{ route('register') }}" class="rounded-lg bg-indigo-600 px-6 py-3 font-semibold text-white hover:bg-indigo-700">
            سجّل كمدرس مجانًا
        </a>
    </div>
</body>
</html>
