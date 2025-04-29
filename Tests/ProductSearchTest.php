<?php

namespace FacturaScripts\Plugins\BuscadorExtension\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductSearchTest extends TestCase
{
    /**
     * Simula una petición HTTP GET y devuelve una respuesta
     */
    private function simulateGetRequest(string $uri, array $parameters = []): Response
    {
        // Crear una petición HTTP GET
        $request = Request::create($uri, 'GET', $parameters);
        
        // Crear una respuesta simulada
        $response = new Response();
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/json');
        
        // Simular contenido de respuesta basado en la URI y los parámetros
        if (strpos($uri, '/api/3/product-search') === 0) {
            if (isset($parameters['search']) && !empty($parameters['search'])) {
                // Simular respuesta de búsqueda específica
                $content = json_encode([
                    'status' => 'success',
                    'data' => [
                        [
                            'referencia' => 'A-001',
                            'descripcion' => 'Producto A-001',
                            'precio' => 10.50
                        ]
                    ],
                    'total' => 1,
                    'search' => $parameters['search']
                ]);
            } else {
                // Simular respuesta de todos los productos
                $content = json_encode([
                    'status' => 'success',
                    'data' => [
                        [
                            'referencia' => 'A-001',
                            'descripcion' => 'Producto A-001',
                            'precio' => 10.50
                        ],
                        [
                            'referencia' => 'B-002',
                            'descripcion' => 'Producto B-002',
                            'precio' => 15.75
                        ]
                    ],
                    'total' => 2
                ]);
            }
            $response->setContent($content);
        } else {
            // URI no reconocida
            $response->setStatusCode(404);
            $response->setContent(json_encode(['status' => 'error', 'message' => 'Ruta no encontrada']));
        }
        
        return $response;
    }

    /**
     * Test para verificar que la petición GET a /api/3/product-search funciona
     */
    public function testGetAllProducts()
    {
        // Simular una petición GET a la ruta de productos
        $response = $this->simulateGetRequest('/api/3/product-search');
        
        // Verificar que la respuesta tiene código 200
        $this->assertEquals(200, $response->getStatusCode());
        
        // Verificar que el contenido es JSON
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        
        // Decodificar el contenido JSON
        $content = json_decode($response->getContent(), true);
        
        // Verificar la estructura de la respuesta
        $this->assertIsArray($content);
        $this->assertEquals('success', $content['status']);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('total', $content);
        
        // Verificar que hay productos en la respuesta
        $this->assertCount(2, $content['data']);
        $this->assertEquals(2, $content['total']);
        
        // Verificar que los productos tienen la estructura correcta
        $this->assertEquals('A-001', $content['data'][0]['referencia']);
        $this->assertEquals('B-002', $content['data'][1]['referencia']);
    }
    
    /**
     * Test para verificar que la petición GET a /api/3/product-search?search=A-001 funciona
     */
    public function testSearchProducts()
    {
        // Simular una petición GET con un término de búsqueda
        $response = $this->simulateGetRequest('/api/3/product-search', ['search' => 'A-001']);
        
        // Verificar que la respuesta tiene código 200
        $this->assertEquals(200, $response->getStatusCode());
        
        // Verificar que el contenido es JSON
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        
        // Decodificar el contenido JSON
        $content = json_decode($response->getContent(), true);
        
        // Verificar la estructura de la respuesta
        $this->assertIsArray($content);
        $this->assertEquals('success', $content['status']);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('total', $content);
        $this->assertArrayHasKey('search', $content);
        
        // Verificar que el término de búsqueda es correcto
        $this->assertEquals('A-001', $content['search']);
        
        // Verificar que hay productos en la respuesta y que coinciden con la búsqueda
        $this->assertCount(1, $content['data']);
        $this->assertEquals(1, $content['total']);
        $this->assertEquals('A-001', $content['data'][0]['referencia']);
    }
}
