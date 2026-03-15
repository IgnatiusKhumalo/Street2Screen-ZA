<?php
/**
 * ============================================
 * TRANSLATE CLASS - MULTI-LANGUAGE SUPPORT
 * ============================================
 * Usage: t('key') or t('key', ['var' => 'value'])
 * Example: t('welcome') or t('hello_user', ['name' => 'John'])
 * ============================================
 */

class Translate {
    private static $translations = [];
    private static $currentLang = 'en';
    
    /**
     * Load translations for a specific language
     */
    public static function load($langCode = 'en') {
        self::$currentLang = $langCode;
        $langFile = __DIR__ . '/../lang/' . $langCode . '.php';
        
        if (file_exists($langFile)) {
            self::$translations = require $langFile;
        } else {
            // Fallback to English if language file not found
            $langFile = __DIR__ . '/../lang/en.php';
            if (file_exists($langFile)) {
                self::$translations = require $langFile;
            }
        }
    }
    
    /**
     * Get translation for a key
     */
    public static function get($key, $variables = []) {
        // Get translation or return key if not found
        $translation = self::$translations[$key] ?? $key;
        
        // Replace variables if any
        if (!empty($variables)) {
            foreach ($variables as $var => $value) {
                $translation = str_replace('{' . $var . '}', $value, $translation);
            }
        }
        
        return $translation;
    }
    
    /**
     * Get current language code
     */
    public static function getCurrentLang() {
        return self::$currentLang;
    }
}

/**
 * Helper function for translations
 * Usage: t('home') or t('hello_user', ['name' => 'John'])
 */
function t($key, $variables = []) {
    return Translate::get($key, $variables);
}
?>
