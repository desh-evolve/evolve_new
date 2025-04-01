<?php

namespace App\Models\Core;

class I18Nv2_Language {
    public function __construct($language, $encoding) {
        // Initialization logic
    }

    public function getAllCodes() {
        return [
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
            'da' => 'Danish',
            'zh' => 'Chinese'
        ];
    }
}

?>
