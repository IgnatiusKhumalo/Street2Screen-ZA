<?php
class Language {
    private static $languages = [
        'en' => 'English', 
        'af' => 'Afrikaans', 
        'zu' => 'isiZulu', 
        'xh' => 'isiXhosa',
        'st' => 'Sesotho', 
        'nso' => 'Sepedi', 
        'tn' => 'Setswana', 
        'ss' => 'siSwati',
        'nr' => 'isiNdebele', 
        've' => 'Tshivenda', 
        'ts' => 'Xitsonga'
    ];
    
    public static function getLanguages() {
        return self::$languages;
    }
    
    public static function getCurrentLanguage() {
        return $_SESSION['language'] ?? 'en';
    }
    
    public static function setLanguage($code) {
        if (array_key_exists($code, self::$languages)) {
            $_SESSION['language'] = $code;
            
            // Load translations immediately
            require_once __DIR__.'/Translate.php';
            Translate::load($code);
            
            return true;
        }
        return false;
    }
    
    /**
     * Get translations for current language
     * LEGACY COMPATIBILITY - kept for backward compatibility
     */
    public static function getTranslations($langCode = 'en') {
        $langFile = __DIR__ . '/../lang/' . $langCode . '.php';
        if (file_exists($langFile)) {
            return require $langFile;
        }
        return [];
    }
}
?>
