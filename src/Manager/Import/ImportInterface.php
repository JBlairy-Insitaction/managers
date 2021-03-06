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

    /** @param array<int, array<int, string>> $data */
    public function run(array $data): void;

    /** @param array<int, string> $row */
    public function loadEntityFromArray(array $row, ImportableEntityInterface $entity): void;

    public function getOffset(): int;

    /** @param array<int, string> $row */
    public function queryBuilder(QueryBuilder $queryBuilder, array $row): void;

    public function getPropertyIdentifier(): ?string;

    public function getColumnIdentifier(): ?int;

    public function setMode(): string;
}
