<?php

namespace FacturaScripts\Plugins\BuscadorExtension\Controller;

use FacturaScripts\Core\Template\ApiController;
use FacturaScripts\Plugins\BuscadorExtension\Service\ProductService;
use FacturaScripts\Plugins\BuscadorExtension\Service\ImageService;

class ProductSearch extends ApiController
{
    protected ProductService $productService;
    protected ImageService $imageService;

    public function __construct(string $className, string $url = '')
    {
        parent::__construct($className, $url);
        $this->productService = new ProductService();
        $this->imageService = new ImageService();
    }

    public function setServices(ProductService $productService, ImageService $imageService): void
    {
        $this->productService = $productService;
        $this->imageService = $imageService;
    }    
   
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function runResource(): void
    {
        if ($this->request->getMethod() === 'OPTIONS') {
            $this->response->setStatusCode(200);
            $this->response->setContent('');
            return;
        }

        $searchTerm = $this->request->get('search', '');
        $page = (int) $this->request->get('page', 1);

        $products = $this->productService->getProducts($searchTerm, $page);
        $formattedProducts = array_map([$this, 'formatProductData'], $products);

        $this->response->setContent(json_encode($formattedProducts));
    }

    private function formatProductData($producto): array
    {
        return [
            'id' => $producto->idproducto,
            'referencia' => $producto->referencia,
            'nombre' => $producto->descripcion,
            'precio' => $producto->precio,
            'descripcion' => $producto->observaciones,
            'imagenes' => $this->imageService->getProductImages($producto->idproducto),
        ];
    }
}