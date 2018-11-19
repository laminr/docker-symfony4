<?php

namespace App\Service;

use App\Entity\Source;
use App\Repository\SourceRepository;
use Doctrine\Common\Persistence\ObjectManager;

class SourceService extends BaseService
{

    private $repository;

    /**
     * SourceService constructor.
     * @param ObjectManager $em
     * @param SourceRepository $sourceRepository
     */
    public function __construct(ObjectManager $em, SourceRepository $sourceRepository)
    {
        parent::__construct($em);
        $this->repository = $sourceRepository;
    }

    public function findAll($connected = false, $last = null): array
    {

        $options = [];
        if (!$connected) {
            $options['public'] = !$connected;
        }

        $data = [];
        if ($last == null) {
            $data = $this->repository->findBy($options, ["sortIndex" => "ASC"]);

        } else {
            $data = $this->repository->findAllWithDate($last);
        }

        return $data;
    }

    public function findById($id): ?Source
    {
        return $this->repository->findOneBy(['id' => $id], ['sortIndex' => 'ASC']);
    }
}
