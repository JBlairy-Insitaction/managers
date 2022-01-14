<?php

namespace Insitaction\ManagersBundle\Manager\Request\Adapter;

use Doctrine\ORM\EntityManagerInterface;
use Insitaction\ManagersBundle\Manager\Request\Entity\RequestEntityInterface;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

interface RequestAdapterInterface
{
    public function __construct(SerializerInterface $serializer, EntityManagerInterface $em);

    /**
     * @return class-string
     */
    public function entityClassname(): string;

    /**
     * @return RequestEntityInterface|RequestEntityInterface[]
     */
    public function getEntity(): RequestEntityInterface|array;

    /**
     * @return string[]
     */
    public function setGroups(): array;

    /**
     * @param array<mixed, mixed> $data
     */
    public function validation(array|stdClass $data): bool;

    public function process(Request $request): self;

    public function multiple(): bool;

    /**
     * @param array<string, mixed> $extraFields
     */
    public function addExtraFields(array $extraFields): self;

    /**
     * @return RequestEntityInterface|RequestEntityInterface[]
     */
    public function save(): RequestEntityInterface|array;
}
