<?php

namespace Psssst;

use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Symfony\Component\Finder\Finder;

use Psssst\HookVisitor;

final class ModuleParser
{
    /**
     * PHP Parser.
     *
     * @var \PhpParser\Parser
     */
    private $phpParser;

    /**
     * PHP Traverser.
     *
     * @var \PhpParser\NodeTraverser
     */
    private $phpTraverser;

    /**
     * PHP Hook Visitor.
     *
     * @var \Psssst\HookVisitor
     */
    private $hookVisitor;

    public function __construct()
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);

        $this->phpParser = $parser;
        $this->phpTraverser = new NodeTraverser();
        $this->hookVisitor = new HookVisitor();
        $this->phpTraverser->addVisitor($this->hookVisitor);
    }

    public function parseModule(string $modulePath) : array
    {
        $finder = new Finder();
        $files = $finder->files()->name('*.php')->in($modulePath);
        $hooks = [];

        foreach ($files as $file) {
            try {
                $stmts = $this->phpParser->parse($file->getContents());

                $stmts = $this->phpTraverser->traverse($stmts);

                if (!empty($this->hookVisitor::$version) &&
                    !empty($this->hookVisitor::$module)
                ) {
                    $hooks[] = [
                        'name' => $this->hookVisitor::$module,
                        'displayName' => $this->hookVisitor::$displayName,
                        'version' => $this->hookVisitor::$version,
                        'description' => $this->hookVisitor::$description,
                        'versionCompliancyMin' => $this->hookVisitor::$versionsCompliancyMin,
                        'versionCompliancyMax' => $this->hookVisitor::$versionsCompliancyMax,
                        'hooks' => $this->hookVisitor::$hooks,
                    ];
                }
            } catch (Error $e) {
                echo 'Parse Error: ', $e->getMessage();
            }
            $this->hookVisitor::reset();
        }

        return $hooks;
    }
}
