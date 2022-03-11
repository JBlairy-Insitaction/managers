<?php

namespace Insitaction\ManagersBundle\Manager\Import;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Exception;
use ImportPersistTypeEnum;

abstract class AbstractImport implements ImportInterface
{
    private ImportManager $importManager;

    private bool $skipErrors;

    private ?string $mode;

    public function __construct(private EntityManagerInterface $em)
    {
        $this->skipErrors = false;
        $this->mode = null;
    }

    public function support(string $className): bool
    {
        $array = explode('\\', static::class);

        if ($className === end($array)) {
            return true;
        }

        return false;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function create(): void
    {
        $this->mode = ImportPersistTypeEnum::CREATE;
    }

    public function update(): void
    {
        $this->mode = ImportPersistTypeEnum::UPDATE;
    }

    public function createOrUpdate(): void
    {
        $this->mode = ImportPersistTypeEnum::CREATE_AND_UPDATE;
    }

    /** @param array<int, array<int, string>> $datas */
    public function run(array $datas): void
    {
        /* @phpstan-ignore-next-line */
        if (!new ($this->getClass())() instanceof ImportableEntityInterface) {
            throw new Exception($this->getClass() . ' must be of type ' . ImportableEntityInterface::class);
        }

        if (null === $this->mode) {
            throw new Exception('You need to define a mode first');
        }

        switch ($this->mode) {
            case ImportPersistTypeEnum::CREATE:
                $this->createMode($datas);
                break;
            case ImportPersistTypeEnum::UPDATE:
                $this->updateMode($datas);
                break;
            case ImportPersistTypeEnum::CREATE_AND_UPDATE:
                $this->createAndUpdateMode($datas);
                break;
        }

        $this->em->flush();
    }

    public function queryBuilder(QueryBuilder $queryBuilder): void
    {
    }

    /** @param array<int, string> $row */
    private function getEntity(array $row): ?ImportableEntityInterface
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select($this->getClass())
            ->addCriteria(
                Criteria::create()->where(
                    Criteria::expr()->eq($this->getPropertyIdentifier(), $row[$this->getColumnIdentifier()])
                )
            )
            ;

        $this->queryBuilder($queryBuilder);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /** @param array<int, array<int, string>> $datas */
    private function createAndUpdateMode(array $datas): void
    {
        foreach ($datas as $data) {
            $entity = $this->getEntity($data);
            if (!$entity instanceof ImportableEntityInterface) {
                $entity = new ($this->getClass())();
            }

            $this->loadEntityFromArray($data, $entity);
            $this->em->persist($entity);
        }
    }

    /** @param array<int, array<int, string>> $datas */
    private function createMode(array $datas): void
    {
        foreach ($datas as $data) {
            $entity = new ($this->getClass())();

            $this->loadEntityFromArray($data, $entity);
            $this->em->persist($entity);
        }
    }

    /** @param array<int, array<int, string>> $datas */
    private function updateMode(array $datas): void
    {
        foreach ($datas as $data) {
            $entity = $this->getEntity($data);

            if (!$entity instanceof ImportableEntityInterface) {
                $message = 'Cant find ' . $this->getClass() . ' entity with ' . $this->getPropertyIdentifier() . ' = ' . $data[$this->getColumnIdentifier()];

                if ($this->skipErrors) {
                    $this->importManager->log($message);
                    continue;
                }

                throw new Exception($message);
            }

            $this->loadEntityFromArray($data, $entity);
        }
    }

    public function setManager(ImportManager $manager): void
    {
        $this->importManager = $manager;
    }

    public function getOffset(): int
    {
        return 0;
    }

    public function setSkipErrors(): void
    {
        $this->skipErrors = true;
    }
}
