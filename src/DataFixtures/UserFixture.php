<?php

namespace App\DataFixtures;

use App\Entity\Token;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends Fixture
{
    const TEST_USERNAME = 'john';
    const TEST_USERNAME_OTHER = 'otherJohn';
    const TEST_USER_PASSWORD = 'maxsecure';
    protected $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }


    public function load(ObjectManager $manager)
    {
        $users = [
            [static::TEST_USERNAME, static::TEST_USER_PASSWORD],
            [static::TEST_USERNAME_OTHER, static::TEST_USER_PASSWORD],
        ];
        foreach ($users as $userData) {
            $user = new User();
            $user->setUsername($userData[0]);
            $user->setPassword($this->encoder->encodePassword($user, $userData[1]));
            $manager->persist($user);
        }
         
        $manager->flush();
    }
}
