import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import en from './locales/en.json';
import fr from './locales/fr.json';
import es from './locales/es.json';
import de from './locales/de.json';
import pt from './locales/pt.json';
import it from './locales/it.json';
import nl from './locales/nl.json';
import pl from './locales/pl.json';
import ru from './locales/ru.json';
import zh from './locales/zh.json';
import ja from './locales/ja.json';
import ko from './locales/ko.json';
import ar from './locales/ar.json';
import tr from './locales/tr.json';

i18n.use(initReactI18next).init({
    resources: {
        en: { translation: en }, fr: { translation: fr }, es: { translation: es }, de: { translation: de },
        pt: { translation: pt }, it: { translation: it }, nl: { translation: nl }, pl: { translation: pl },
        ru: { translation: ru }, zh: { translation: zh }, ja: { translation: ja }, ko: { translation: ko },
        ar: { translation: ar }, tr: { translation: tr },
    },
    lng: 'en',
    fallbackLng: 'en',
    interpolation: { escapeValue: false },
});

export default i18n;
