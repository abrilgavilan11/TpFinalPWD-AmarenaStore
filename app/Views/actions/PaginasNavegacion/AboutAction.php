<?php

namespace App\Views\Actions;

class AboutAction
{
    /**
     * Prepara datos para la página "Sobre Nosotros"
     */
    public function prepareData()
    {
        return [
            'title' => 'Sobre Nosotros - Amarena Store',
            'pageCss' => 'about'
        ];
    }
}
