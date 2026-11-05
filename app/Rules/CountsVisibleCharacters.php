<?php

namespace App\Rules;

trait CountsVisibleCharacters
{
    // caracteres que ve el usuario en el texto del editor: sin etiquetas y con las entidades como un carácter
    protected function visibleLength($value): int
    {
        return mb_strlen(trim(html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }
}
