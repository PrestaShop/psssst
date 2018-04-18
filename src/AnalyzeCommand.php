<?php

namespace Psssst;

use DateTime;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Psssst\ModuleParser;

final class AnalyzeCommand extends Command
{

    /**
     * @var SymfonyStyle
     */
    private $io;

    protected static $defaultName = 'psssst:analyze';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('A simple CLI tool to analyze a PrestaShop module.')
            ->setHelp($this->getCommandHelp())
            ->addArgument('path', InputArgument::REQUIRED, 'PrestaShop module root path')
            ->addOption('export', 'e', InputOption::VALUE_NONE, 'Export output to JSON')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('path');
        $moduleData = (new ModuleParser())->parseModule($path);

        if ($input->getOption('export')) {
            $io->text(json_encode($moduleData));

            return;
        }

        $io->title('Psssst, the amazing PrestaShop module parser!');
        
        foreach ($moduleData as $module => $hooks) {
            $io->section("Detecting hooks in module $module");
            $io->listing($hooks);
        }  

        $io->success('Analysis done with success.');
    }

    /**
     * The command help is usually included in the configure() method, but when
     * it's too long, it's better to define a separate method to maintain the
     * code readability.
     */
    private function getCommandHelp()
    {
        return <<<'HELP'
The <info>%command.name%</info> analyze a PrestaShop module:

  <info>php %command.full_name%</info> <comment>path/to/module(s)/</comment>

You can also export results in JSON:
  <info>php %command.full_name%</info> <comment>path/to/module(s)/</comment> <info>--format=json</info>
HELP;
    }
}