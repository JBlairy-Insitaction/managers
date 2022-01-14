<?php

namespace Insitaction\ManagersBundle\Manager\Request\Adapter;

use Doctrine\ORM\EntityManagerInterface;
use Insitaction\ManagersBundle\Manager\Request\Entity\RequestEntityInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractRequestAdapter implements RequestAdapterInterface
{
    /** @var class-string */
    private string $entityClassName;

    /** @var RequestEntityInterface|RequestEntityInterface[] */
    private RequestEntityInterface|array $convertedEntity;

    /** @var string[] */
    private array $groups;

    public function __construct(
        private SerializerInterface $serializer,
        private EntityManagerInterface $em
    ) {
        $this->entityClassName = $this->getEntityClassname();
        $this->groups = $this->setGroups();
    }

    public function process(Request $request): self
    {
        if (true === $this->multiple()) {
            $entities = [];
            foreach (json_decode($request->getContent(), true) as $entity) {
                $this->validation($entity);
                $entities[] = $this->serialize(json_encode($entity, JSON_THROW_ON_ERROR), $this->getObject($entity));
            }
        } else {
            $this->validation(json_decode($request->getContent()));
            $entities = $this->serialize($request->getContent(), $this->getObject(json_decode($request->getContent())));
        }

        $this->convertedEntity = $entities;

        return $this;
    }

    private function serialize(string $data, ?RequestEntityInterface $entity): RequestEntityInterface
    {
        $context = [
            'groups' => $this->groups,
        ];

        if (null !== $entity) {
            $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $entity;
        }

        return $this->serializer->deserialize($data, $this->entityClassName, 'json', $context);
    }

    /** @return RequestEntityInterface|RequestEntityInterface[] */
    public function getEntity(): RequestEntityInterface|array
    {
        return $this->convertedEntity;
    }

    /**
     * @param array<mixed, mixed> $data
     */
    private function getObject(array $data): ?RequestEntityInterface
    {
        if (!isset($data['id'])) {
            return null;
        }

        $entity = $this->em->getRepository($this->entityClassName)->find($data['id']);

        if (!$entity instanceof RequestEntityInterface) {
            return null;
        }

        return $entity;
    }

    /** @return RequestEntityInterface|RequestEntityInterface[] */
    public function save(): RequestEntityInterface|array
    {
        if (is_array($this->convertedEntity)) {
            foreach ($this->convertedEntity as $entity) {
                $this->processSave($entity);
            }
        } else {
            $this->processSave($this->convertedEntity);
        }

        return $this->convertedEntity;
    }

    private function processSave(RequestEntityInterface $entity): void
    {
        $this->em->persist($entity);
        $this->em->flush();
    }

    /**
     * @return class-string
     */
    private function getEntityClassname(): string
    {
        $classname = $this->entityClassname();
        if (!new $classname() instanceof RequestEntityInterface) {
            throw new RuntimeException(sprintf('The entity "%s" must implement RequestEntityInterface.', $this->entityClassname()));
        }

        return $this->entityClassname();
    }
}
