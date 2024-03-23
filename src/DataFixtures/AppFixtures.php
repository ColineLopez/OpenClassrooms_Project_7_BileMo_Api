<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\Partner;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Customer list creation.
        $customerList = [];
        for ($i = 0; $i < 10; $i++) {
            // Client creation.
            $customer = new Partner();
            $customer->setName("Name " . $i);
            $manager->persist($customer);
            $customerList[] = $customer;
        }

        $productList = [];
        // Initialize 20 fakes products
        for ($i=0; $i<20; $i++) {
            $product = new Product();
            $product->setTitle('Phone '.$i);
            $product->setPrice(mt_rand(10000,100000)/100);
            $manager->persist($product);
            $productList[] = $product;
        }

        for ($i = 0; $i < 20; $i++) {
            // Product user creation.
            $productUser = new Customer();
            $productUser->setName('Name '.$i);
            $productUser->setPartner($customerList[array_rand($customerList)]);
            $productUser->setProduct($productList[array_rand($productList)]);
            $manager->persist($productUser);
        }

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword($this->passwordHasher->hashPassword($user,'password'));
        $manager->persist($user);
        // $user->setRoles(['ROLE_USER']);

        $manager->flush();
    }
}
