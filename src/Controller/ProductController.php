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

#[Route('/api/products', name:'products')]
class ProductController extends AbstractController
{

    public function __construct(private readonly PotentialActionSerializer $potentialActionSerializer)
    {
    }

    #[Route('', name: '', methods:['GET'])]
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

    #[Route('/{id}', name: '_one', methods:['GET'])]
    public function getProduct(Product $product): JsonResponse
    {

        $jsonProduct = $this->potentialActionSerializer->generate($product, 'getProducts');

        return $this->json(
            $jsonProduct,
            Response::HTTP_OK
        );  
    }
}
