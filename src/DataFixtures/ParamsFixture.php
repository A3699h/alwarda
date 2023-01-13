<?php

namespace App\DataFixtures;

use App\Entity\Params;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ParamsFixture extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        $params = [];

        $params[] = (new Params())->setName('Address line 1')->setSlug('paramAddr1');
        $params[] = (new Params())->setName('Address line 2')->setSlug('paramAddr2');
        $params[] = (new Params())->setName('Work hours')->setSlug('paramWorkHours');
        $params[] = (new Params())->setName('Work days')->setSlug('paramWorkDays');
        $params[] = (new Params())->setName('Phone number')->setSlug('paramPhoneNumber');
        $params[] = (new Params())->setName('Email address')->setSlug('paramEmailAddr');
        $params[] = (new Params())->setName('IOS App Link')->setSlug('paramIosLink');
        $params[] = (new Params())->setName('Android App Link')->setSlug('paramAndroidLink');

        forEach ($params as $param) {
            $manager->persist($param);
        }
        $manager->flush();
    }

    /**
     * This method must return an array of groups
     * on which the implementing class belongs to
     *
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['prod'];
    }
}
