<?php

namespace FacturaScripts\Plugins\BuscadorExtension;

use FacturaScripts\Core\Base\InitClass;
use FacturaScripts\Core\Kernel;
use FacturaScripts\Core\Controller\ApiRoot;

class Init extends InitClass
{
    public function init(): void
    {
        try {
            // Registrar el recurso API
            Kernel::addRoute('/api/3/product-search', 'ProductSearch', -1);
            ApiRoot::addCustomResource('productsearch');
        } catch (\Exception $e) {
            echo 'Error al registrar el recurso: ' . $e->getMessage();
        }
    }

    public function update(): void
    {
        // Código para actualizar el plugin si es necesario
    }

    public function uninstall(): void
    {
        // Código para desinstalar el plugin si es necesario
    }
}
