<form method="POST" action="{{ url('/admin/locale') }}" class="flex items-center gap-1 sm:gap-2 px-1 sm:px-3">
    @csrf
    <select name="locale" onchange="this.form.submit()" class="text-xs sm:text-sm border border-gray-300 rounded-lg px-1 sm:px-2 py-1 bg-white text-gray-700 focus:ring-2 focus:ring-amber-500 outline-none cursor-pointer w-14 sm:w-auto max-w-[160px] sm:max-w-none">
        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }} data-full="English">EN</option>
        <option value="fr" {{ app()->getLocale() === 'fr' ? 'selected' : '' }} data-full="Français">FR</option>
        <option value="es" {{ app()->getLocale() === 'es' ? 'selected' : '' }} data-full="Español">ES</option>
        <option value="de" {{ app()->getLocale() === 'de' ? 'selected' : '' }} data-full="Deutsch">DE</option>
        <option value="pt" {{ app()->getLocale() === 'pt' ? 'selected' : '' }} data-full="Português">PT</option>
        <option value="it" {{ app()->getLocale() === 'it' ? 'selected' : '' }} data-full="Italiano">IT</option>
        <option value="nl" {{ app()->getLocale() === 'nl' ? 'selected' : '' }} data-full="Nederlands">NL</option>
        <option value="pl" {{ app()->getLocale() === 'pl' ? 'selected' : '' }} data-full="Polski">PL</option>
        <option value="ru" {{ app()->getLocale() === 'ru' ? 'selected' : '' }} data-full="Русский">RU</option>
        <option value="zh" {{ app()->getLocale() === 'zh' ? 'selected' : '' }} data-full="中文">ZH</option>
        <option value="ja" {{ app()->getLocale() === 'ja' ? 'selected' : '' }} data-full="日本語">JA</option>
        <option value="ko" {{ app()->getLocale() === 'ko' ? 'selected' : '' }} data-full="한국어">KO</option>
        <option value="ar" {{ app()->getLocale() === 'ar' ? 'selected' : '' }} data-full="العربية">AR</option>
        <option value="tr" {{ app()->getLocale() === 'tr' ? 'selected' : '' }} data-full="Türkçe">TR</option>
    </select>
</form>
