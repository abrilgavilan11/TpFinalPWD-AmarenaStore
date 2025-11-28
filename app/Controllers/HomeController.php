<?php

namespace App\Controllers;

use App\Models\Product;

class HomeController extends BaseController
{
    /**
     * Muestra la página de inicio con productos destacados
     */
    public function index()
    {
        // Obtener los productos destacados del modelo.
        $productModel = new Product();
        $featuredProducts = $productModel->getFeatured(4);
        
        // Mostrar la vista 'vistas.tienda.home' y le pasamos los datos necesarios.
        $this->view('vistas.tienda.home', [
            'title' => 'Amarena Store - Moda para todas',
            'pageCss' => 'home',
            'featuredProducts' => $featuredProducts
        ]);
    }
}
