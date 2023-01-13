<?php

namespace App\DataFixtures;

use App\Entity\Area;
use App\Entity\DeliveryAddress;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminUserFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $superAdmin = new User();
        $superAdmin->setEmail('super@super.com');
        $superAdmin->setFullName('super');
        $superAdmin->setPhone('0123456789');
        $superAdmin->setActive(true);
        $superAdmin->setPassword($this->encoder->encodePassword($superAdmin, 'superAdmin'));
        $superAdmin->setRole(User::USER_ROLES['superAdmin']);

        $area = new Area();
        $area->setActive(true);
        $area->setMapName(Area::MAP_NAMES[0]);
        $area->setNameEn('Area1');
        $area->setNameAr('Area1');

        $area2 = new Area();
        $area2->setActive(true);
        $area2->setMapName(Area::MAP_NAMES[4]);
        $area2->setNameEn('Area2');
        $area2->setNameAr('Area2');
        $manager->persist($area);
        $manager->persist($area2);

        for ($i = 1; $i <= 20; $i++) {
            $client = new User();
            $client->setEmail('client' . $i . '@client.com');
            $client->setFullName('client ' . $i);
            $client->setPhone('0123456789');
            $client->setActive(array_rand([true, false]));
            $client->setPassword($this->encoder->encodePassword($client, 'client'));
            $client->setRole(User::USER_ROLES['client']);

            $address = new DeliveryAddress();
            $address->setRecieverArea([$area, $area2][array_rand([$area, $area2])]);
            $address->setRecieverFullAddress("client $i delivery adress");

            $client->addDeliveryAdress($address);
            $manager->persist($client);
        }

        $manager->persist($superAdmin);
        $manager->flush();
    }
}
