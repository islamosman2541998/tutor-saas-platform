<div>
    <h1 class="mb-1 text-xl font-bold login-title">إنشاء حسابك كمدرس</h1>
    <p class="mb-6 text-sm login-text">ابدأ إدارة دروسك الخصوصية في دقائق</p>

    <form wire:submit="register" class="space-y-4">
        <x-ui.input
            name="teacher_name"
            label="اسم المدرس"
            wire:model="teacher_name"
            :error="$errors->first('teacher_name')"
            autofocus
        />

        <x-ui.input
            name="activity_name"
            label="اسم النشاط أو السنتر"
            wire:model="activity_name"
            :error="$errors->first('activity_name')"
        />

        <x-ui.input
            name="email"
            type="email"
            label="البريد الإلكتروني"
            wire:model="email"
            :error="$errors->first('email')"
            autocomplete="username"
        />

        <x-ui.input
            name="phone"
            label="رقم الهاتف (اختياري)"
            wire:model="phone"
            :error="$errors->first('phone')"
        />

        <x-ui.input
            name="password"
            type="password"
            label="كلمة المرور"
            wire:model="password"
            :error="$errors->first('password')"
            autocomplete="new-password"
        />

        <x-ui.input
            name="password_confirmation"
            type="password"
            label="تأكيد كلمة المرور"
            wire:model="password_confirmation"
            autocomplete="new-password"
        />

        <x-ui.button type="submit" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="register">إنشاء الحساب</span>
            <span wire:loading wire:target="register">جارٍ الإنشاء...</span>
        </x-ui.button>
    </form>

    <p class="mt-6 text-center text-sm login-text">
        لديك حساب بالفعل؟ استخدم رابط الدخول الخاص بمركزك.
    </p>
</div>
