<?php

namespace App\DataFixtures;

use App\Entity\DeliveryAddress;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $admin = new User();
        $admin->setEmail('admin@admin.com');
        $admin->setFullName('admin');
        $admin->setPhone('0123456789');
        $admin->setActive(true);
        $admin->setPassword($this->encoder->encodePassword($admin, 'admin'));
        $admin->setRole(User::USER_ROLES['admin']);

        $superAdmin = new User();
        $superAdmin->setEmail('super@super.com');
        $superAdmin->setFullName('super');
        $superAdmin->setPhone('0123456789');
        $superAdmin->setActive(true);
        $superAdmin->setPassword($this->encoder->encodePassword($superAdmin, 'superAdmin'));
        $superAdmin->setRole(User::USER_ROLES['superAdmin']);

        for ($i = 1; $i <= 20; $i++) {
            $client = new User();
            $client->setEmail('client' . $i . '@client.com');
            $client->setFullName('client ' . $i);
            $client->setPhone('0123456789');
            $client->setActive(array_rand([true, false]));
            $client->setPassword($this->encoder->encodePassword($client, 'client'));
            $client->setRole(User::USER_ROLES['client']);

            $address = new DeliveryAddress();
            $address->setRecieverFullAddress("client $i delivery adress");

            $client->addDeliveryAdress($address);
            $manager->persist($client);
        }

        for ($i = 1; $i <= 20; $i++) {
            $shop = new User();
            $shop->setEmail('shop' . $i . '@shop.com');
            $shop->setFullName('shop' . $i);
            $shop->setPhone('0123456789');
            $shop->setActive(array_rand([true, false]));
            $shop->setPassword($this->encoder->encodePassword($shop, 'shop'));
            $shop->setRole(User::USER_ROLES['shop']);
            $manager->persist($shop);
        }

        $manager->persist($superAdmin);
        $manager->persist($admin);
        $manager->flush();
    }
}
