<?php

declare(strict_types=1);

namespace SpecShaper\GdprBundle\Doctrine\ORM;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;

class GdprEntityManager extends EntityManagerDecorator
{
    protected $encryptor;

    public function __construct(EntityManagerInterface $wrapped, EncryptorInterface $encryptor)
    {
        $this->encryptor = $encryptor;
        parent::__construct($wrapped);
    }


}