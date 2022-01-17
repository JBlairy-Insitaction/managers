<?php

namespace Insitaction\ManagersBundle\Manager\Request\Adapter;

use Doctrine\ORM\EntityManagerInterface;
use Insitaction\ManagersBundle\Manager\Request\ProcessedEntity\ProcessedEntityInterface;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

interface RequestAdapterInterface
{
    public function __construct(SerializerInterface $serializer, EntityManagerInterface $em, ProcessedEntityInterface $processedEntity);

    /**
     * @return class-string
     */
    public function entityClassname(): string;

    /**
     * @return string[]
     */
    public function setGroups(): array;

    /**
     * @param array<mixed, mixed> $data
     */
    public function validation(array|stdClass $data): bool;

    public function process(Request $request): ProcessedEntityInterface;

    public function multiple(): bool;

    /**
     * @param array<string, mixed> $extraFields
     */
    public function addExtraFields(array $extraFields): self;
}
