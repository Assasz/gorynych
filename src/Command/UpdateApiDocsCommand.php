<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Command;

use Gorynych\Generator\FileWriter;
use Gorynych\Util\EnvAccess;
use Gorynych\Util\OpenApiScanner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class UpdateApiDocsCommand extends Command
{
    protected static $defaultName = 'gorynych:update-api-docs';

    private FileWriter $fileWriter;
    private OpenApiScanner $docsScanner;

    public function __construct(FileWriter $fileWriter, OpenApiScanner $docsScanner)
    {
        parent::__construct();

        $this->fileWriter = $fileWriter;
        $this->docsScanner = $docsScanner;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Updates Open API documentation.')
            ->addArgument('outputPath', InputArgument::OPTIONAL, 'API documentation file output path');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = EnvAccess::get('PROJECT_DIR') . ($input->getArgument('outputPath') ?? '/openapi/openapi.yaml');

        $this->fileWriter->forceOverwrite()->write($path, $this->docsScanner->scan()->toYaml());

        $io->success("Docs updated at path {$path}");

        return 0;
    }
}
