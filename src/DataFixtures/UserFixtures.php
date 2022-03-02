<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Faker;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordEncoder,
        private SluggerInterface $slugger
    ){}

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@test.fr');
        $admin->setLastname('Dmin');
        $admin->setFirstname('A');
        $admin->setBirthdate(new \DateTime('12-01-1991'));
        $admin->setInterests('les licornes et la couture');
        $admin->setPassword(
            $this->passwordEncoder->hashPassword($admin, 'admin')
        );
        $admin->setRoles(['ROLE_ADMIN']);

        $manager->persist($admin);

        $faker = Faker\Factory::create('fr_FR');

        for($usr = 1; $usr <= 70; $usr++){

            $user = new User();
            $user->setEmail($faker->email);
            $user->setLastname($faker->lastname);
            $user->setFirstname($faker->firstname);
            $user->setBirthdate($faker->dateTime());
            $user->setInterests($faker->text(20));
            $user->setPassword(
                $this->passwordEncoder->hashPassword($user, 'secret')
            );
            $user->setRoles(['ROLE_USER']);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
