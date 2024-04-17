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
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

#[Route('/api/partners')]
class PartnerController extends AbstractController
{
    private $entityManager; 

    public function __construct(private readonly PotentialActionSerializer $potentialActionSerializer, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager; 
    }



    /**
     * List all the partners.
     */
    #[Route('', name: 'partners', methods:['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the partners\' list.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Partner::class, groups: ['getPartners']))
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
    #[OA\Tag(name: 'Partners')]
    #[Security(name: 'Bearer')]
    public function getPartners(PartnerRepository $partnerRepository, Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $offset = ($page - 1) * $limit;

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


    /**
     * Get a partner's detail.
     */
    #[Route('/{id}', name: 'partners_one', methods:['GET'])]    
    #[OA\Response(
        response: 200,
        description: 'Returns one partner identified by its id.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Partner::class, groups: ['getPartners']))
        )
    )]
    #[OA\Tag(name: 'Partners')]
    #[Security(name: 'Bearer')]
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


    /**
     * List all the customers from a partner.
     */
    #[Route('/{id}/customers', name: 'customers', methods:['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the customers\' list from a partner identified by its id.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Customer::class, groups: ['getCustomers']))
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
    #[OA\Tag(name: 'Customers')]
    #[Security(name: 'Bearer')]
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


    /**
     * Add a customer from a partner.
     */
    #[Route('/{id}/customers', name: 'customers_add', methods:['POST'])]
    #[OA\Response(
        response: 201,
        description: 'Add a customers from a partner identified by its id.',
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request. Invalid input data.',
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: Customer::class,
            example: [
                "name" => "name",
                "product" => ["title" => "product title"]
            ]
        )
    )]
    #[OA\Tag(name: 'Customers')]
    #[Security(name: 'Bearer')]
    #[IsGranted('ROLE_ADMIN')]
    public function addCustomersListFromPartner(SerializerInterface $serializer, Request $request, ValidatorInterface $validator) : JsonResponse
    {
        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');
        $id = $request->get('id'); 
        $partner = $this->entityManager->getRepository(Partner::class)->find($id);
        $product = $this->entityManager->getRepository(Product::class)->findOneBy(['title' => $customer->getProduct()->getTitle()]);

        $customer->setProduct($product);
        $customer->setPartner($partner);

        $errors = $validator->validate($customer);
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

    /**
     * Get a customer's detail.
     */
    #[Route('/{partner_id}/customers/{customer_id}', name: 'customers_one', methods:['GET'])]   
    #[OA\Response(
        response: 200,
        description: 'Returns one customer identified by its id and its partner id.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Customer::class, groups: ['getCustomers', 'getProducts']))
        )
    )]
    #[OA\Tag(name: 'Customers')]
    #[Security(name: 'Bearer')]
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

    /**
     * Delete a customer.
     */
    #[Route('/{partner_id}/customers/{customer_id}', name: 'customers_delete', methods:['DELETE'])]  
    #[OA\Response(
        response: 404,
        description: 'Delete a customer from a partner identified by its id and its partner id.',
    )]
    #[OA\Tag(name: 'Customers')]
    #[Security(name: 'Bearer')]
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
