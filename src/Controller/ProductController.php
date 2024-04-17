<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\PotentialActionSerializer;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

#[Route('/api/products', name:'products')]
class ProductController extends AbstractController
{

    public function __construct(private readonly PotentialActionSerializer $potentialActionSerializer)
    {
    }

    /**
     * List all the products.
     */
    #[Route('', name: '', methods:['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the products\' list.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Product::class, groups: ['getProducts']))
        )
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'The field used to select page.',
        schema: new OA\Schema(type: 'int')
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'The field used to select limit product through page.',
        schema: new OA\Schema(type: 'int')
    )]
    #[OA\Tag(name: 'Products')]
    #[Security(name: 'Bearer')]
    public function getProducts(ProductRepository $productRepository, Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $offset = ($page -1) * $limit;

        $productList = $productRepository->findBy(
            [],
            [],
            $limit,
            $offset,
        );

        $jsonProductList = $this->potentialActionSerializer->generate($productList, 'getProducts');

        return $this->json([
            'products' => $jsonProductList,
        ],
            Response::HTTP_OK);
    }


    /**
     * Get a product's detail.
     */
    #[Route('/{id}', name: '_one', methods:['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns one products identified by its id.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Product::class, groups: ['getProducts']))
        )
    )]
    #[OA\Tag(name: 'Products')]
    #[Security(name: 'Bearer')]
    public function getProduct(Product $product): JsonResponse
    {

        $jsonProduct = $this->potentialActionSerializer->generate($product, 'getProducts');

        return $this->json(
            $jsonProduct,
            Response::HTTP_OK
        );  
    }
}
