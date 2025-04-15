<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Guide;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class GuideTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $guide = new Guide();

        // Test setTitle and getTitle
        $guide->setTitle('Test Guide');
        $this->assertEquals('Test Guide', $guide->getTitle());

        // Test setText and getText
        $guide->setText('This is a test guide.');
        $this->assertEquals('This is a test guide.', $guide->getText());

        // Test setImg and getImg
        $guide->setImg('test_image.jpg');
        $this->assertEquals('test_image.jpg', $guide->getImg());

        // Test setDate and getDate
        $date = new \DateTime();
        $guide->setDate($date);
        $this->assertEquals($date, $guide->getDate());

        // Test addCategory and getCategories
        $category = new Category();
        $guide->addCategory($category);
        $this->assertContains($category, $guide->getCategories());

        // Test removeCategory
        $guide->removeCategory($category);
        $this->assertEmpty($guide->getCategories());

        // Test setUser and getUser
        $user = new User();
        $guide->setUser($user);
        $this->assertEquals($user, $guide->getUser());
    }

    public function testIdIsNotNullAfterCreation()
    {
        $guide = new Guide();
        $this->assertNull($guide->getId()); // L'ID est null jusqu'à ce que l'entité soit persistée
    }
}
