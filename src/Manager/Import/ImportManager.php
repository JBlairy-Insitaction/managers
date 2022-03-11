<?php

namespace Insitaction\ManagersBundle\Manager\Import;

use Exception;
use League\Csv\Reader;
use Psr\Log\LoggerInterface;
use Shuchkin\SimpleXLSX;
use Symfony\Component\Console\Style\SymfonyStyle;
use Traversable;

final class ImportManager
{
    private ?ImportInterface $import;

    private ?SymfonyStyle $io;

    /** @var array<int, array<int, string>> */
    private array $datas;

    /**
     * @param ImportInterface[] $imports
     */
    public function __construct(private iterable $imports, private LoggerInterface $importLogger)
    {
        $this->imports = $imports instanceof Traversable ? iterator_to_array($imports) : $imports;
        $this->import = null;
        $this->io = null;
        $this->datas = [];
    }

    public function init(string $className): self
    {
        foreach ($this->imports as $import) {
            if ($import->support($className)) {
                $this->import = $import;
            }
        }

        if (null === $this->import) {
            throw new Exception('Cant find an import class named ' . $className . ' who implements ImportInterface.');
        }

        $this->import->setManager($this);

        return $this;
    }

    public function setIo(SymfonyStyle $io): self
    {
        $this->io = $io;

        return $this;
    }

    public function log(string $message): void
    {
        if (null !== $this->io) {
            $this->io->info($message);
        }

        $this->importLogger->info($message);
    }

    public function getData(string $filePath): self
    {
        if (null === $this->import) {
            throw new Exception('You need to init manager first.');
        }

        switch (mime_content_type($filePath)) {
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                $datas = SimpleXLSX::parse($filePath)->rows();
                break;
            case 'text/csv':
            case 'application/csv':
            $datas = Reader::createFromPath($filePath, 'r')->jsonSerialize();
                break;
            default:
                throw new Exception('You need to implement getData method for mimeType : ' . mime_content_type($filePath));
        }

        array_splice($datas, 0, $this->import->getOffset());
        $this->datas = $datas;

        return $this;
    }

    public function run(): void
    {
        if (null === $this->import) {
            throw new Exception('You need to init manager first.');
        }

        $this->import->run($this->datas);
    }

    public function skipErrors(): self
    {
        if (null === $this->import) {
            throw new Exception('You need to init manager first.');
        }

        $this->import->setSkipErrors();

        return $this;
    }
}
