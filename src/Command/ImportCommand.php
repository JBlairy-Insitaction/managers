<?php

namespace App\Command;

use Insitaction\ManagersBundle\Manager\Import\ImportManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'insitaction:import',
    description: ' ',
)]
class ImportCommand extends Command
{
    public function __construct(
        private ImportManager $importManager,
    ) {
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('className', InputArgument::REQUIRED, 'className of Entity.')
            ->addArgument('path', InputArgument::REQUIRED, 'Path of file.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->importManager
            ->init($input->getArgument('className'))
            ->setIo($io)
            ->skipErrors()
            ->getData($input->getArgument('path'))
            ->update()
        ;
        $io->success('Import Complete.');

        return Command::SUCCESS;
    }
}
