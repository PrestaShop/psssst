<?php

namespace Psssst;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

            return 0;
        }

        $io->title('Psssst, the amazing PrestaShop module parser!');

        foreach ($moduleData as $module) {
            $io->section(
                sprintf(
                    'Module %s',
                    $module['name']
                )
            );
            $io->definitionList(
                ['DisplayName' => $module['displayName']],
                ['Author' => $module['author']],
                ['Version' => $module['version']],
                ['Min' => $module['versionCompliancyMin']],
                ['Max' => $module['versionCompliancyMax']],
                ['Description' => $module['description']],
                ['Tab' => $module['tab']]
            );

            $io->text('Hooks:');
            $io->newLine();
            if (!empty($module['hooks'])) {
                $io->listing($module['hooks']);
            }
        }

        $io->success('Analysis done with success.');

        return 0;
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
