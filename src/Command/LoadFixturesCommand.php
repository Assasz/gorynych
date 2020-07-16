<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Command;

use Gorynych\Adapter\FixturesLoaderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class LoadFixturesCommand extends Command
{
    protected static $defaultName = 'gorynych:load-fixtures';

    private FixturesLoaderInterface $fixturesLoader;

    public function __construct(FixturesLoaderInterface $fixturesLoader)
    {
        parent::__construct();

        $this->fixturesLoader = $fixturesLoader;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Loads fixtures into database for specified environment (dev by default).')
            ->addOption('env', null, InputArgument::OPTIONAL);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $env = $input->getOption('env') ?? 'dev';

        $io = new SymfonyStyle($input, $output);
        $io->confirm("Using {$env} envirenment. Continue?", true);

        $loadedFixtures = $this->fixturesLoader->loadFixtures(["_{$env}.yaml"]);
        $io->success("Fixtures loaded: {$loadedFixtures}");

        return 0;
    }
}
