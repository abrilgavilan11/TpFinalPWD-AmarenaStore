<?php

namespace App\Views\Actions;

use App\Models\Product;

class HomeAction
{
    /**
     * Prepara los datos para la página de inicio
     */
    public function prepareData()
    {
        $productModel = new Product();
        $featuredProducts = $productModel->getFeatured(4);
        
        return [
            'title' => 'Amarena Store - Moda para todas',
            'pageCss' => 'home',
            'featuredProducts' => $featuredProducts
        ];
    }
}
