<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Command;

use Gorynych\Generator\ApiGenerator;
use HaydenPierce\ClassFinder\ClassFinder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateApiCommand extends Command
{
    protected static $defaultName = 'gorynych:generate-api';

    private ApiGenerator $apiGenerator;

    public function __construct(ApiGenerator $apiGenerator)
    {
        parent::__construct();

        $this->apiGenerator = $apiGenerator;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generates basic API for existing resources.')
            ->addArgument('resourceNamespace', InputArgument::REQUIRED, 'Namespace of application resources');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $apiGenerator = $this->apiGenerator;
        $resources = ClassFinder::getClassesInNamespace($input->getArgument('resourceNamespace'), ClassFinder::RECURSIVE_MODE);
        $progressBar = new ProgressBar($output, count($resources));

        array_walk(
            $resources,
            static function (string $resource) use ($apiGenerator, $progressBar): void {
                $resourceReflection = new \ReflectionClass($resource);

                if (true === $resourceReflection->isInterface() || true === $resourceReflection->isAbstract()) {
                    return;
                }

                $apiGenerator->generate($resourceReflection);
                $progressBar->advance();
            }
        );

        $progressBar->finish();
        $output->writeln(['', '<comment>API generation done</comment>']);

        return 0;
    }
}
