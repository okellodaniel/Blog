<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminFixtures extends Fixture
{
    private $encoder;
    private $em;

    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $entityManager)
    {
        $this->encoder  = $encoder;
        $this->em = $entityManager;
    }
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $adminsData = [
            0 => [
                'FirstName' => 'Daniel',
                'LastName' => 'Okello',
                'username' => 'okellodaniel',
                'Email' => 'okello@gamil.com',
                'role' => ['ROLE_ADMIN'],
                'password' => 12345
            ]
        ];

        foreach($adminsData as $admin)
        {
            $newAdmin = new Admin();
            $newAdmin->setEmail($admin['Email']);
            $newAdmin->setPassword($this->encoder->encodePassword($newAdmin, $admin['password']));
            $newAdmin->setRoles($admin['role']);
            $newAdmin->setUsername($admin['username']);
            $this->em->persist($newAdmin);
        }
        $this->em->flush();
    }
}
