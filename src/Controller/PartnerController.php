<?php

namespace App\Controller;

use App\Entity\Partner;
use App\Entity\Customer;
use App\Entity\Product;
use App\Repository\PartnerRepository;
use App\Repository\CustomerRepository;
use App\Repository\ProductRepository;
use App\Service\PotentialActionSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/partners')]
class PartnerController extends AbstractController
{
    private $entityManager; 

    public function __construct(private readonly PotentialActionSerializer $potentialActionSerializer, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager; 
    }

    #[Route('', name: 'partners', methods:['GET'])]
    public function getPartners(PartnerRepository $partnerRepository, Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $offset = ($page -1) * $limit;

        $partnerList = $partnerRepository->findBy(
            [],
            [],
            $limit,
            $offset,
        );

        $jsonPartnerList = $this->potentialActionSerializer->generate($partnerList, 'getPartners');

        return $this->json([
            'partners' => $jsonPartnerList
        ],
            Response::HTTP_OK
        );
    }

    #[Route('/{id}', name: 'partners_one', methods:['GET'])]
    public function getPartner(Request $request): JsonResponse
    {
        $id = $request->get('id');
        $partner = $this->entityManager->getRepository(Partner::class)->find($id);
        $jsonPartner = $this->potentialActionSerializer->generate($partner, 'getPartners');

        return $this->json([
            "partner" => $jsonPartner,
        ],
            Response::HTTP_OK
        );
    }

    #[Route('/{id}/customers', name: 'customers', methods:['GET'])]
    public function getCustomers(CustomerRepository $customerRepository, Request $request): JsonResponse
    {

        $id = $request->get('id');
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $offset = ($page -1) * $limit;

        $customerList = $customerRepository->findBy(
            ['partner' => $id],
            [],
            $limit,
            $offset,
        );

        $jsonCustomerList = $this->potentialActionSerializer->generate($customerList, 'getCustomers');

        return $this->json([
            'customers' => $jsonCustomerList,
        ],
            Response::HTTP_OK
        );
    }

    #[Route('/{id}/customers', name: 'customers_add', methods:['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function addCustomersListFromPartner(SerializerInterface $serializer, ProductRepository $productRepository, Request $request, ValidatorInterface $validator) : JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');
        // var_dump($test);die;
        // dd($data);die;
        $id = $request->get('id'); 
        // var_dump($customer->getProduct()->getTitle());die;
        $partner = $this->entityManager->getRepository(Partner::class)->find($id);
        $product = $this->entityManager->getRepository(Product::class)->findOneBy(['title' => $customer->getProduct()->getTitle()]);

        $customer->setProduct($product);
        $customer->setPartner($partner);

        $errors = $validator->validate($customer);
        // var_dump($errors->count());die;
        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        

        $this->entityManager->persist($customer); 
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Customer added successfully',
        ],
            Response::HTTP_CREATED
        );
    }

    #[Route('/{partner_id}/customers/{customer_id}', name: 'customers_one', methods:['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getCustomerFromPartner(Request $request): JsonResponse
    {
        $id = $request->get('customer_id');
        $customer = $this->entityManager->getRepository(Customer::class)->find($id);
        $jsonCustomer = $this->potentialActionSerializer->generate($customer, ['getCustomers', 'getProducts']);
        return $this->json(
            $jsonCustomer,
            Response::HTTP_OK
        );
    }

    #[Route('/{partner_id}/customers/{customer_id}', name: 'customers_delete', methods:['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteCustomerFromPartner(Request $request): JsonResponse
    {
        $id = $request->get('customer_id');
        $customer = $this->entityManager->getRepository(Customer::class)->find($id);

        if (!$customer) {
            return $this->json([
                'message' => 'Customer not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($customer);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while deleting the customer.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        return $this->json(
            ['message' => 'Customer deleted successfully'], 
            Response::HTTP_NO_CONTENT
        );
    }

}
