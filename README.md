![Insitaction](https://www.insitaction.com/assets/img/logo_insitaction.png)
# Manager bundle

Manager bundle is a symfony bundle.

## Installation:
```bash
composer require insitaction/managers-bundle
```

## Usage:
RequestManager
```php
<?php

namespace Insitaction\ManagersBundle\Manager\Request\Adapter;

use App\Entity\TestCase;

class TestCaseRequestAdapter extends AbstractRequestAdapter implements RequestAdapterInterface
{
    /**
     * @return class-string
     */
    public function entityClassname(): string
    {
        return TestCase::class;
    }

    public function setGroups(): array
    {
        return ['test'];
    }

    public function multiple(): bool
    {
        return true; // Or false
    }

    /**
     * @param array<mixed, mixed> $data
     */
    public function validation(array $data): bool
    {
        //TODO add validation
        
        return true;
    }
}
```

ImportManager
```php
<?php

namespace App\Import;

use App\DBAL\EnumUserRoleType;
use App\Entity\User;
use Insitaction\ManagersBundle\Manager\Import\ImportInterface;
use Insitaction\ManagersBundle\Manager\Import\ImportableEntityInterface;
use Insitaction\ManagersBundle\Manager\Import\AbstractImport;

class UserRolesImport extends AbstractImport implements ImportInterface
{
    public function getClass(): string
    {
        return User::class;
    }

    public function getColumnIdentifier(): int
    {
        return 3; // the id of the column in the imported file
    }

    public function getPropertyIdentifier(): string
    {
        return 'email'; // the entity field name
    }

    public function getOffset(): int
    {
        return 0;
    }

    /** @param array<int, string> $row */
    public function loadEntityFromArray(array $row, ImportableEntityInterface $user): void
    {
        // your own logic
    }
}
```