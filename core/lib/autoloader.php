<?php

spl_autoload_register(function($className) {
});

/**
 * * Auto-chargement des librairies de classes de PluXml.
 *
 * Le nom de la class doit commencer par plx, suivi d'une lettre majuscule.
 * Exception avec PlxTemplate
 * */
spl_autoload_register(
        function($aClass) {
                if(preg_match('@^[pP]lx([A-Z]\w+)$@', $aClass, $matches)) {
                        $filename = __DIR__ . '/class.plx.' . strtolower($matches[1]) . '.php';
                        return (file_exists($filename) and include_once $filename);
                }

                return false;
        },
        true, # ignoré à partir de PHP 8.0.0 - Laissé à true
        true  # La fonction anonyme est chargé en début de pile de l'auto-loader
);

