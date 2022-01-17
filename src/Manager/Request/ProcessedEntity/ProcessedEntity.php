<?php

namespace Insitaction\ManagersBundle\Manager\Request\ProcessedEntity;

use Doctrine\ORM\EntityManagerInterface;
use Insitaction\ManagersBundle\Manager\Request\Entity\RequestEntityInterface;

class ProcessedEntity implements ProcessedEntityInterface
{
    /** @var RequestEntityInterface|RequestEntityInterface[] */
    private array|RequestEntityInterface $entities;

    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    /** @return RequestEntityInterface|RequestEntityInterface[] */
    public function getEntity(): RequestEntityInterface|array
    {
        return $this->entities;
    }

    /**
     * @param RequestEntityInterface|RequestEntityInterface[] $entities
     */
    public function setAdaptedEntity(RequestEntityInterface|array $entities): void
    {
        $this->entities = $entities;
    }

    /** @return RequestEntityInterface|RequestEntityInterface[] */
    public function save(): RequestEntityInterface|array
    {
        if (is_array($this->entities)) {
            foreach ($this->entities as $entity) {
                $this->processSave($entity);
            }
        } else {
            $this->processSave($this->entities);
        }

        return $this->entities;
    }

    private function processSave(RequestEntityInterface $entity): void
    {
        $this->em->persist($entity);
        $this->em->flush();
    }
}
