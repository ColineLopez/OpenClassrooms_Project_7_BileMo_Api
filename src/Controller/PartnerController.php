<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Partner;
use App\Entity\Customer;
use App\Repository\PartnerRepository;
use App\Repository\CustomerRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/partners')]
class PartnerController extends AbstractController
{
    function __construct(private readonly SerializerInterface $serializer)
    {
        
    }

    #[Route('', name: 'partners', methods:['GET'])]
    public function getPartners(PartnerRepository $partnerRepository): JsonResponse
    {
        $partnerList = $partnerRepository->findAll();
        $jsonPartnerList = $this->serializer->serialize($partnerList, 'json', ['groups' => 'getPartners']);

        return $this->json(
            $jsonPartnerList,
            // $customerList,
            Response::HTTP_OK
        );
    }

    #[Route('/{id}', name: 'partners_one', methods:['GET'])]
    public function getPartner(Partner $partner): JsonResponse
    {
        $jsonProduct = $this->serializer->serialize($partner, 'json', ['groups' => 'getPartners']);
        return $this->json(
            $jsonProduct,
            Response::HTTP_OK
        );
    }

    #[Route('/{id}/customers', name: 'customers', methods:['GET'])]
    public function getCustomersListFromPartner(int $id, CustomerRepository $customerRepository): JsonResponse
    {
        $customerList = $customerRepository->findBy(['partner' => $id]);
        $jsonCustomer = $this->serializer->serialize($customerList, 'json', ['groups' => 'getCustomers']);
        return $this->json(
            $jsonCustomer,
            Response::HTTP_OK
        );
    }

    #[Route('/{id}/customers', name: 'customers_add', methods:['POST'])]
    public function addCustomersListFromPartner(int $id, Request $request, PartnerRepository $partnerRepository, ProductRepository $productRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // var_dump($data);die;
        $partner = $partnerRepository->find($id);

        if (!$partner) {
            return $this->json([
                'message' => 'Partner not found.'
            ],
            Response::HTTP_NOT_FOUND);
        }

        $customer = new Customer();
        $customer->setName($data['name']);

        $product = $productRepository->find($data['product_id']);

        if (!$product) {
            return $this->json([
                'message' => 'Product not found.'
            ],
            Response::HTTP_NOT_FOUND);
        }

        $customer->setProduct($product);
        $customer->setPartner($partner);

        $entityManager->persist($customer); 
        $entityManager->flush();

        $serializedCustomer = $this->serializer->serialize($customer, 'json', ['groups' => 'getProducts']);

        return $this->json(
            $serializedCustomer,
            Response::HTTP_CREATED
        );
    }

    #[Route('/{partner_id}/customers/{customer_id}', name: 'customers_one', methods:['GET'])]
    public function getCustomerFromPartner(int $customer_id, CustomerRepository $customerRepository): JsonResponse
    {
        $customerList = $customerRepository->findBy(['id' => $customer_id]);
        $jsonCustomer = $this->serializer->serialize($customerList, 'json', ['groups' => 'getCustomers']);
        return $this->json(
            $jsonCustomer,
            Response::HTTP_OK
        );
    }

    #[Route('/{partner_id}/customers/{customer_id}', name: 'customers_delete', methods:['DELETE'])]
    public function deleteCustomerFromPartner(int $customer_id, CustomerRepository $customerRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $customer = $customerRepository->find($customer_id);

        if (!$customer) {
            return $this->json([
                'message' => 'Customer not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $entityManager->remove($customer);
            $entityManager->flush();
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while deleting the customer.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        return $this->json(
            null, 
            Response::HTTP_NO_CONTENT
        );
    }




}
