<form method="POST" action="{{ url('/admin/locale') }}" class="flex items-center gap-2 px-3">
    @csrf
    <select name="locale" onchange="this.form.submit()" class="text-sm border border-gray-300 rounded-lg px-2 py-1 bg-white text-gray-700 focus:ring-2 focus:ring-amber-500 outline-none cursor-pointer">
        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>English</option>
        <option value="fr" {{ app()->getLocale() === 'fr' ? 'selected' : '' }}>Français</option>
        <option value="es" {{ app()->getLocale() === 'es' ? 'selected' : '' }}>Español</option>
        <option value="de" {{ app()->getLocale() === 'de' ? 'selected' : '' }}>Deutsch</option>
        <option value="pt" {{ app()->getLocale() === 'pt' ? 'selected' : '' }}>Português</option>
        <option value="it" {{ app()->getLocale() === 'it' ? 'selected' : '' }}>Italiano</option>
        <option value="nl" {{ app()->getLocale() === 'nl' ? 'selected' : '' }}>Nederlands</option>
        <option value="pl" {{ app()->getLocale() === 'pl' ? 'selected' : '' }}>Polski</option>
        <option value="ru" {{ app()->getLocale() === 'ru' ? 'selected' : '' }}>Русский</option>
        <option value="zh" {{ app()->getLocale() === 'zh' ? 'selected' : '' }}>中文</option>
        <option value="ja" {{ app()->getLocale() === 'ja' ? 'selected' : '' }}>日本語</option>
        <option value="ko" {{ app()->getLocale() === 'ko' ? 'selected' : '' }}>한국어</option>
        <option value="ar" {{ app()->getLocale() === 'ar' ? 'selected' : '' }}>العربية</option>
        <option value="tr" {{ app()->getLocale() === 'tr' ? 'selected' : '' }}>Türkçe</option>
    </select>
</form>
