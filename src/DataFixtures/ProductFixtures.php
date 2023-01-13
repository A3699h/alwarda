<?php


namespace App\DataFixtures;


use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $category = new Category();
        $category->setName('categgory 1');
        $manager->persist($category);

        $category2 = new Category();
        $category2->setName('categgory 2');
        $manager->persist($category2);

        $product1 = new Product();
        $product1->setSKU('1111');
        $product1->setName('Product1');
        $product1->setBenefit(20);
        $product1->setCategory($category);
        $product1->setColor('purple');
        $product1->setCost(150);
        $product1->setDescription('lorem ipsum dolor');
        $product1->setType(Product::PRODUCT_TYPES[0]);
        $product1->setViews(0);
        $manager->persist($product1);

        $product2 = new Product();
        $product2->setSKU('2222');
        $product2->setName('Product2');
        $product2->setBenefit(20);
        $product2->setCategory($category2);
        $product2->setColor('pink');
        $product2->setCost(150);
        $product2->setDescription('lorem ipsum dolor');
        $product2->setType(Product::PRODUCT_TYPES[0]);
        $product2->setViews(0);
        $manager->persist($product2);

        $product3 = new Product();
        $product3->setSKU('3333');
        $product3->setName('Product3');
        $product3->setBenefit(20);
        $product3->setCategory($category2);
        $product3->setColor('orange');
        $product3->setCost(150);
        $product3->setDescription('lorem ipsum dolor');
        $product3->setType(Product::PRODUCT_TYPES[0]);
        $product3->setViews(0);
        $manager->persist($product3);

        $manager->flush();
    }
}
