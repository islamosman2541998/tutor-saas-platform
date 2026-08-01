<x-layouts.guest :title="'انتهى الاشتراك'">
    <h1 class="mb-1 text-xl font-bold login-title">اشتراكك انتهى</h1>
    <p class="mb-6 text-sm login-text">
        باقة اشتراكك في المنصة انتهت أو غير مفعّلة حاليًا. تواصل مع فريق الدعم الفني لتجديد الاشتراك واستعادة الوصول لحسابك.
    </p>

    @auth
        <form method="POST" action="{{ route('tenant.logout', ['tenant' => $currentTenant->slug]) }}">
            @csrf
            <x-ui.button type="submit" variant="secondary">تسجيل الخروج</x-ui.button>
        </form>
    @endauth
</x-layouts.guest>
