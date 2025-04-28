<?php

namespace FacturaScripts\Plugins\BuscadorExtension\Service;

use FacturaScripts\Core\Model\Producto;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

class ProductService
{
    private const PAGE_LIMIT = 50; // Límite de productos por página

    public function getProducts(string $searchTerm, int $page): array
    {
        $offset = ($page - 1) * self::PAGE_LIMIT;
        $productoModel = new Producto();

        // Filtrar productos según el término de búsqueda
        $where = [];
        if (!empty($searchTerm)) {
            $where[] = new DataBaseWhere('referencia', '%' . $searchTerm . '%', 'LIKE');
            $where[] = new DataBaseWhere('descripcion', '%' . $searchTerm . '%', 'LIKE', 'OR'); // Combinar con OR
        }

        // Obtener productos paginados
        return $productoModel->all($where, [], $offset, self::PAGE_LIMIT);
    }
}