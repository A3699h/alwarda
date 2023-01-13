<?php


namespace App\DataFixtures;


use App\Entity\DiscountVoucher;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VoucherFixtures extends Fixture
{

    /**
     * Load data fixtures with the passed EntityManager
     */
    public function load(ObjectManager $manager)
    {
        $voucher = new DiscountVoucher();
        $voucher->setCode('111');
        $voucher->setClientMaxUse(1);
        $voucher->setDiscountPercentage(10);
        $voucher->setEndDate(new \DateTime('2020-05-25'));
        $voucher->setStartDate(new \DateTime('2020-05-20'));
        $voucher->setMaxUse(10);
        $manager->persist($voucher);

        $voucher2 = new DiscountVoucher();
        $voucher2->setCode('222');
        $voucher2->setClientMaxUse(1);
        $voucher2->setDiscountPercentage(20);
        $voucher2->setEndDate(new \DateTime('2020-05-17'));
        $voucher2->setStartDate(new \DateTime('2020-05-15'));
        $voucher2->setMaxUse(20);
        $manager->persist($voucher2);

        $voucher3 = new DiscountVoucher();
        $voucher3->setCode('333');
        $voucher3->setClientMaxUse(1);
        $voucher3->setDiscountPercentage(30);
        $voucher3->setEndDate(new \DateTime('2020-05-25'));
        $voucher3->setStartDate(new \DateTime('2020-05-17'));
        $voucher3->setMaxUse(30);
        $manager->persist($voucher3);

        $manager->flush();
    }
}
