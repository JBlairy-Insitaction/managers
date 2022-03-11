<?php

namespace Insitaction\ManagersBundle\Manager\Import;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

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

    public function create(): void;

    public function update(): void;

    public function createOrUpdate(): void;

    /** @param array<int, array<int, string>> $data */
    public function run(array $data): void;

    /** @param array<int, string> $row */
    public function loadEntityFromArray(array $row, ImportableEntityInterface $entity): void;

    public function getOffset(): int;

    public function queryBuilder(QueryBuilder $queryBuilder): void;

    public function getPropertyIdentifier(): string;

    public function getColumnIdentifier(): int;

    public function getMode(): ?string;
}
