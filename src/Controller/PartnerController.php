<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Partner;
use App\Entity\Customer;
use App\Repository\PartnerRepository;
use App\Repository\CustomerRepository;
use App\Repository\ProductRepository;
use App\Service\PotentialActionSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/partners')]
class PartnerController extends AbstractController
{
    function __construct(private readonly SerializerInterface $serializer, private readonly PotentialActionSerializer $potentialActionSerializer)
    {
        
    }

    #[Route('', name: 'partners', methods:['GET'])]
    public function getPartners(PartnerRepository $partnerRepository): JsonResponse
    {
        $partnerList = $partnerRepository->findAll();
        $jsonPartnerList = $this->potentialActionSerializer->generate($partnerList, 'getPartners');

        return $this->json([
            'partners' => $jsonPartnerList,
            // 'link' => '/api/partners/{id}'
            // 'potential_action' => $nvellefunction
        ],
            Response::HTTP_OK
        );
    }

    #[Route('/{id}', name: 'partners_one', methods:['GET'])]
    public function getPartner(Partner $partner, CustomerRepository $customerRepository): JsonResponse
    {
        $id = $partner ->getId();

        $customerList = $customerRepository->findBy(['partner' => $id]);

        $jsonPartner = $this->potentialActionSerializer->generate($partner, 'getPartners');
        $jsonCustomerList = $this->potentialActionSerializer->generate($customerList, 'getCustomers');

        //  Marche pas + MODIF CETTE ROUTE 
        // if(!$partner){
        //     return $this->json([
        //         'message' => 'Partner not found.'
        //     ],
        //     Response::HTTP_NOT_FOUND);
        // }
        // $jsonProduct = $this->potentialActionSerializer->generate($partner, 'getPartners');
        return $this->json([
            "partner" => $jsonPartner,
            'customers' => $jsonCustomerList,
            // 'link' => '/api/partners/{id}/customers'
        ],
            Response::HTTP_OK
        );
    }

    #[Route('/{id}/customers', name: 'customers_add', methods:['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function addCustomersListFromPartner(int $id, Request $request, PartnerRepository $partnerRepository, ProductRepository $productRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

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

        // $jsonCustomer = $this->potentialActionSerializer->generate($customer, 'getProducts');
        $serializedCustomer = $this->serializer->serialize($customer, 'json', ['groups' => ['getCustomers', 'getProducts']]);

        return $this->json(
            $serializedCustomer,
            Response::HTTP_CREATED
        );
    }

    #[Route('/{partner_id}/customers/{customer_id}', name: 'customers_one', methods:['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getCustomerFromPartner(int $customer_id, CustomerRepository $customerRepository): JsonResponse
    {
        // $jsonCustomer = $this->potentialActionSerializer->generate($customer, ['getCustomers', 'getProducts']);
        $customer = $customerRepository->findBy(['id' => $customer_id]);
        $jsonCustomer = $this->potentialActionSerializer->generate($customer, ['getCustomers', 'getProducts']);
        return $this->json(
            $jsonCustomer,
            Response::HTTP_OK
        );
    }

    #[Route('/{partner_id}/customers/{customer_id}', name: 'customers_delete', methods:['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
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
