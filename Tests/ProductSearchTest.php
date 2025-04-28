<?php

namespace FacturaScripts\Plugins\BuscadorExtension\Tests;

use FacturaScripts\Plugins\BuscadorExtension\Controller\ProductSearch;
use FacturaScripts\Plugins\BuscadorExtension\Service\ProductService;
use FacturaScripts\Plugins\BuscadorExtension\Service\ImageService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ReflectionClass;

class ProductSearchTest extends TestCase
{
    protected ProductSearch $controller;
    protected ProductService $mockProductService;
    protected ImageService $mockImageService;

    protected function setUp(): void
    {
        // Crear mocks de los servicios
        $this->mockProductService = $this->createMock(ProductService::class);
        $this->mockImageService = $this->createMock(ImageService::class);

        // Instanciar el controlador
        $this->controller = new ProductSearch('ProductSearch', '/api/3/product-search');

        // Inyectar los mocks de los servicios
        $this->controller->setServices($this->mockProductService, $this->mockImageService);
    }

    /**
     * Método auxiliar para establecer la propiedad request en el controlador
     */
    protected function injectRequest(Request $request): void
    {
        $reflection = new ReflectionClass($this->controller);
        $property = $reflection->getProperty('request');
        $property->setAccessible(true);
        $property->setValue($this->controller, $request);
    }

    /**
     * Método auxiliar para establecer la propiedad response en el controlador
     */
    protected function createResponseProperty(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $property = $reflection->getProperty('response');
        $property->setAccessible(true);
        if ($property->getValue($this->controller) === null) {
            $property->setValue($this->controller, new Response());
        }
    }

    public function testRunResourceReturnsProducts(): void
    {
        // Simular datos de productos
        $mockProducts = [
            (object) [
                'idproducto' => 1,
                'referencia' => 'REF001',
                'descripcion' => 'Producto 1',
                'precio' => 100.0,
                'observaciones' => 'Descripción del producto 1',
            ],
        ];

        // Simular respuesta del servicio de productos
        $this->mockProductService
            ->method('getProducts')
            ->with('Producto', 1)
            ->willReturn($mockProducts);

        // Simular respuesta del servicio de imágenes
        $this->mockImageService
            ->method('getProductImages')
            ->with(1)
            ->willReturn(['image1.jpg']);

        // Crear una petición simulada
        $request = new Request(['search' => 'Producto', 'page' => 1]);
        $this->injectRequest($request);
        $this->createResponseProperty();

        // Ejecutar el controlador
        $this->controller->runResource();

        // Obtener la respuesta
        $reflection = new ReflectionClass($this->controller);
        $property = $reflection->getProperty('response');
        $property->setAccessible(true);
        $response = $property->getValue($this->controller);

        // Verificar que la respuesta sea JSON
        $this->assertInstanceOf(Response::class, $response);
        $this->assertJson($response->getContent());

        // Verificar el contenido de la respuesta
        $expectedResponse = json_encode([
            [
                'id' => 1,
                'referencia' => 'REF001',
                'nombre' => 'Producto 1',
                'precio' => 100.0,
                'descripcion' => 'Descripción del producto 1',
                'imagenes' => ['image1.jpg'],
            ],
        ]);
        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function testRunResourceWithEmptySearch(): void
    {
        // Simular datos de productos para búsqueda vacía
        $mockProducts = [
            (object) [
                'idproducto' => 1,
                'referencia' => 'REF001',
                'descripcion' => 'Producto 1',
                'precio' => 100.0,
                'observaciones' => 'Descripción del producto 1',
            ],
            (object) [
                'idproducto' => 2,
                'referencia' => 'REF002',
                'descripcion' => 'Producto 2',
                'precio' => 200.0,
                'observaciones' => 'Descripción del producto 2',
            ],
        ];

        // Simular respuesta del servicio de productos
        $this->mockProductService
            ->method('getProducts')
            ->with('', 1)
            ->willReturn($mockProducts);

        // Simular respuesta del servicio de imágenes
        $this->mockImageService
            ->method('getProductImages')
            ->willReturnMap([
                [1, ['image1.jpg']],
                [2, ['image2.jpg']]
            ]);

        // Crear una petición simulada sin término de búsqueda
        $request = new Request(['page' => 1]);
        $this->injectRequest($request);
        $this->createResponseProperty();

        // Ejecutar el controlador
        $this->controller->runResource();

        // Obtener la respuesta
        $reflection = new ReflectionClass($this->controller);
        $property = $reflection->getProperty('response');
        $property->setAccessible(true);
        $response = $property->getValue($this->controller);

        // Verificar que la respuesta sea JSON
        $this->assertInstanceOf(Response::class, $response);
        $this->assertJson($response->getContent());

        // Verificar el contenido de la respuesta
        $expectedResponse = json_encode([
            [
                'id' => 1,
                'referencia' => 'REF001',
                'nombre' => 'Producto 1',
                'precio' => 100.0,
                'descripcion' => 'Descripción del producto 1',
                'imagenes' => ['image1.jpg'],
            ],
            [
                'id' => 2,
                'referencia' => 'REF002',
                'nombre' => 'Producto 2',
                'precio' => 200.0,
                'descripcion' => 'Descripción del producto 2',
                'imagenes' => ['image2.jpg'],
            ],
        ]);
        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function testRunResourceWithPagination(): void
    {
        // Simular datos de productos para la página 2
        $mockProducts = [
            (object) [
                'idproducto' => 3,
                'referencia' => 'REF003',
                'descripcion' => 'Producto 3',
                'precio' => 300.0,
                'observaciones' => 'Descripción del producto 3',
            ],
        ];

        // Simular respuesta del servicio de productos
        $this->mockProductService
            ->method('getProducts')
            ->with('', 2)
            ->willReturn($mockProducts);

        // Simular respuesta del servicio de imágenes
        $this->mockImageService
            ->method('getProductImages')
            ->with(3)
            ->willReturn(['image3.jpg']);

        // Crear una petición simulada para la página 2
        $request = new Request(['page' => 2]);
        $this->injectRequest($request);
        $this->createResponseProperty();

        // Ejecutar el controlador
        $this->controller->runResource();

        // Obtener la respuesta
        $reflection = new ReflectionClass($this->controller);
        $property = $reflection->getProperty('response');
        $property->setAccessible(true);
        $response = $property->getValue($this->controller);

        // Verificar que la respuesta sea JSON
        $this->assertInstanceOf(Response::class, $response);
        $this->assertJson($response->getContent());

        // Verificar el contenido de la respuesta
        $expectedResponse = json_encode([
            [
                'id' => 3,
                'referencia' => 'REF003',
                'nombre' => 'Producto 3',
                'precio' => 300.0,
                'descripcion' => 'Descripción del producto 3',
                'imagenes' => ['image3.jpg'],
            ],
        ]);
        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function testRunResourceWithOptionsMethod(): void
    {
        
        $request = new Request();
        $request->setMethod('OPTIONS');
        $this->injectRequest($request);
        $this->createResponseProperty();

        
        $this->controller->runResource();

       
        $reflection = new ReflectionClass($this->controller);
        $property = $reflection->getProperty('response');
        $property->setAccessible(true);
        $response = $property->getValue($this->controller);

        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
    }
}
