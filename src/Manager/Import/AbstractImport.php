<?php

namespace Insitaction\ManagersBundle\Manager\Import;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Insitaction\ManagersBundle\Enum\ImportPersistTypeEnum;

abstract class AbstractImport implements ImportInterface
{
    public const ALIAS = 'entity';

    private ImportManager $importManager;

    private bool $skipErrors;

    public function __construct(protected EntityManagerInterface $em)
    {
        $this->skipErrors = false;
    }

    public function getColumnIdentifier(): ?int
    {
        return null;
    }

    public function getPropertyIdentifier(): ?string
    {
        return null;
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

    /** @param array<int, string> $row */
    public function queryBuilder(QueryBuilder $queryBuilder, array $row): void
    {
        if (null === $this->getColumnIdentifier() || null === $this->getPropertyIdentifier()) {
            throw new Exception('You need to implement getColumnIdentifier and getPropertyIdentifier or override queryBuilder.');
        }

        $queryBuilder->addCriteria(
            Criteria::create()->where(
                Criteria::expr()->eq($this->getPropertyIdentifier(), $row[$this->getColumnIdentifier()])
            )
        );
    }

    /** @param array<int, string> $row */
    private function getEntity(array $row): ?ImportableEntityInterface
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select(self::ALIAS)
            ->from($this->getClass(), self::ALIAS)
        ;

        $this->queryBuilder($queryBuilder, $row);

        try {
            $result = $queryBuilder->getQuery()->getOneOrNullResult();
        } catch (Exception) {
            $ids = [];
            /** @var ImportableEntityInterface $result */
            foreach ($queryBuilder->getQuery()->getResult() as $result) {
                $ids[] = $result->getUniqueIdentifier();
            }
            $this->importManager->log('Multiple results found for identifiers : ' . json_encode($ids));
            throw new Exception('Multiple results found for identifiers : ' . json_encode($ids));
        }

        return $result;
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
            $this->importManager->log('Created or updated ' . $this->getClass() . ' identified by ' . $entity->getUniqueIdentifier());
        }
    }

    /** @param array<int, array<int, string>> $datas */
    private function createMode(array $datas): void
    {
        foreach ($datas as $data) {
            $entity = new ($this->getClass())();

            $this->loadEntityFromArray($data, $entity);
            $this->em->persist($entity);
            $this->importManager->log('Created ' . $this->getClass() . ' identified by ' . $entity->getUniqueIdentifier());
        }
    }

    /** @param array<int, array<int, string>> $datas */
    private function updateMode(array $datas): void
    {
        foreach ($datas as $data) {
            $entity = $this->getEntity($data);

            if (!$entity instanceof ImportableEntityInterface) {
                $message = 'Cant find ' . $this->getClass() . ' entity with given data.';

                if ($this->skipErrors) {
                    $this->importManager->log($message);
                    continue;
                }

                throw new Exception($message);
            }

            $this->loadEntityFromArray($data, $entity);
            $this->importManager->log('Updated ' . $this->getClass() . ' identified by ' . $entity->getUniqueIdentifier());
        }
    }

    public function setManager(ImportManager $manager): void
    {
        $this->importManager = $manager;
    }

    public function getManager(): ImportManager
    {
        return $this->importManager;
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
