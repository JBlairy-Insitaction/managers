<?php

namespace Insitaction\ManagersBundle\Manager\Import;

use Doctrine\ORM\EntityManagerInterface;
use Exception;

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
    public function create(array $datas): void
    {
        foreach ($datas as $data) {
            $entity = new ($this->getClass())();

            /* @phpstan-ignore-next-line */
            if (!$entity instanceof ImportableEntityInterface) {
                throw new Exception($this->getClass() . ' must be of type ' . ImportableEntityInterface::class);
            }

            $this->loadEntityFromArray($data, $entity);
            $this->em->persist($entity);
        }

        $this->em->flush();
    }

    /** @param array<int, array<int, string>> $datas */
    public function update(array $datas): void
    {
        foreach ($datas as $data) {
            $entity = $this->em->getRepository($this->getClass())->findOneBy([$this->getPropertyIdentifier() => $data[$this->getColumnIdentifier()]]);

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

        $this->em->flush();
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
