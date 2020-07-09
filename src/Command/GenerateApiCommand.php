<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Command;

use Gorynych\Resource\ResourceApiGenerator;
use HaydenPierce\ClassFinder\ClassFinder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateApiCommand extends Command
{
    protected static $defaultName = 'gorynych:generate-api';

    private ResourceApiGenerator $resourceApiGenerator;

    public function __construct(ResourceApiGenerator $resourceApiGenerator)
    {
        parent::__construct();

        $this->resourceApiGenerator = $resourceApiGenerator;
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
        $resourceApiGenerator = $this->resourceApiGenerator;
        $resources = ClassFinder::getClassesInNamespace($input->getArgument('resourceNamespace'), ClassFinder::RECURSIVE_MODE);

        array_walk(
            $resources,
            static function (string $resource) use ($resourceApiGenerator): void {
                $resourceReflection = new \ReflectionClass($resource);

                if (true === $resourceReflection->isInterface() || true === $resourceReflection->isAbstract()) {
                    return;
                }

                $resourceApiGenerator->generate($resourceReflection);
            }
        );

        $output->writeln('API generated');

        return 0;
    }
}
