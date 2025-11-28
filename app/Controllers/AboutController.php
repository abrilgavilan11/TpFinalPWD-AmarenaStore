<?php

namespace App\Controllers;

class AboutController extends BaseController
{
    /**
     * Página "Sobre Nosotros"
     */
    public function index()
    {
        $this->view('vistas.tienda.about', [
            'title' => 'Sobre Nosotros - Amarena Store',
            'pageCss' => 'about'
        ]);
    }
}
