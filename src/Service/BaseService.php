<?php

namespace App\Service;

use Doctrine\Common\Persistence\ObjectManager;

abstract class BaseService
{
    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * BaseService constructor.
     * @param ObjectManager $em
     */
    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    protected function persistAndFlush($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
    }
}