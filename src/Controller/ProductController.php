<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/api/products', name:'products')]
// #[isGranted]
class ProductController extends AbstractController
{

    function __construct(private readonly SerializerInterface $serializer)
    {
        
    }

    #[Route('', name: '', methods:['GET'])]
    // #GROUP dans le controlleur en question
    // #[IsGranted('ROLE_USER')]
    public function getProducts(ProductRepository $productRepository): JsonResponse
    {
        $productList = $productRepository->findAll();
        $jsonProductList = $this->serializer->serialize($productList, 'json', ['groups' => 'getProducts']);

        return $this->json(
            $jsonProductList,
            // $customerList,
            Response::HTTP_OK);
    }

    #[Route('/{id}', name: '_one', methods:['GET'])]
    public function getProduct(Product $product): JsonResponse
    {
        $jsonProduct = $this->serializer->serialize($product, 'json', ['groups' => 'getProducts']);
        return $this->json(
            $jsonProduct,
            Response::HTTP_OK
        );
        
        // return new JsonResponse($jsonProduct, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}
