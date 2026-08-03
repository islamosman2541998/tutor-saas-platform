<x-layouts.guest :title="'قيد المراجعة'">
    <h1 class="mb-1 text-xl font-bold login-heading">بياناتك قيد المراجعة</h1>
    <p class="mb-6 text-sm login-text">
        شكرًا لتسجيلك! فريق المنصة بيراجع بياناتك دلوقتي، وهيتم تفعيل حسابك بعد الموافقة عليه. هنبلغك فور تفعيل الحساب.
    </p>

    @auth
        <form method="POST" action="{{ route('tenant.logout', ['tenant' => $currentTenant->slug]) }}">
            @csrf
            <x-ui.button type="submit" variant="secondary">تسجيل الخروج</x-ui.button>
        </form>
    @endauth
</x-layouts.guest>
