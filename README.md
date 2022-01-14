![Insitaction](https://www.insitaction.com/assets/img/logo_insitaction.png)
# Manager bundle

Manager bundle is a symfony bundle.

## Installation:
```bash
composer require insitaction/managers-bundle
```

## Usage:
RequestBundle
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