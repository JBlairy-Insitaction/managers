<?php

namespace Insitaction\ManagersBundle\Manager\Import;

interface ImportableEntityInterface
{
    public function getUniqueIdentifier(): string;
}
