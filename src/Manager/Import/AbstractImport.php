<?php

namespace Insitaction\ManagersBundle\Manager\Import;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Insitaction\ManagersBundle\Enum\ImportPersistTypeEnum;

abstract class AbstractImport implements ImportInterface
{
    private ImportManager $importManager;

    private bool $skipErrors;

    public function __construct(private EntityManagerInterface $em)
    {
        $this->skipErrors = false;
    }

    public function support(string $className): bool
    {
        $array = explode('\\', static::class);

        if ($className === end($array)) {
            return true;
        }

        return false;
    }

    /** @param array<int, array<int, string>> $datas */
    public function run(array $datas): void
    {
        /* @phpstan-ignore-next-line */
        if (!new ($this->getClass())() instanceof ImportableEntityInterface) {
            throw new Exception($this->getClass() . ' must be of type ' . ImportableEntityInterface::class);
        }

        switch ($this->setMode()) {
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
            ->select('entity')
            ->from($this->getClass(), 'entity')
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
