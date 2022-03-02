<?php

namespace Insitaction\ManagersBundle\Manager\Import;

use Doctrine\ORM\EntityManagerInterface;

interface ImportInterface
{
    public function __construct(EntityManagerInterface $em);

    public function support(string $className): bool;

    public function setManager(ImportManager $manager): void;

    public function setSkipErrors(): void;

    /**
     * @return class-string<ImportableEntityInterface>
     */
    public function getClass(): string;

    /** @param array<int, array<int, string>> $data */
    public function create(array $data): void;

    /** @param array<int, array<int, string>> $data */
    public function update(array $data): void;

    /** @param array<int, string> $row */
    public function loadEntityFromArray(array $row, ImportableEntityInterface $entity): void;

    public function getOffset(): int;

    public function getPropertyIdentifier(): string;

    public function getColumnIdentifier(): int;
}
