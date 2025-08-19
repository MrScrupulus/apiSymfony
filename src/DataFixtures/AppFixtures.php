<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Book;
use App\Entity\Author;  

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
      $listAuthor = [];
      for($i = 0; $i < 10; $i++){
        $author = new Author();
        $author->setFirstName('Prénom ' . $i);
        $author->setLastName('Nom ' . $i);
        $manager->persist($author);
        $listAuthor[] = $author;
      }

        for ($i = 0; $i < 10; $i++) {
            $livre = new Book;
            $livre->setTitle('Livre ' . $i);
            $livre->setCoverText('Quatrième de couverture numéro :' . $i);
            $livre->setAuthor($listAuthor[array_rand($listAuthor)]);
            $manager->persist($livre);
        }
        $manager->flush();
    }
}