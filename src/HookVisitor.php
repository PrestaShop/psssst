<?php

namespace Psssst;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\ClassMethod;

class HookVisitor extends NodeVisitorAbstract
{
    public static $hooks = [];

    public static $module = null;

    public static function reset()
    {
        self::$hooks = [];
        self::$module = null;
    }

    public function enterNode(Node $node) {
        if ($node instanceof Node\Stmt\Class_
            && (string)$node->extends === 'Module'
        ) {
            self::$module = $node->name->name;

            return;
        }

        if ($node instanceof ClassMethod) {
            if (null === self::$module) {
                return;
            }

            $methodName = $node->name->name;
            if (strpos($methodName, 'hook') !== false) {
                self::$hooks[] = lcfirst(substr($methodName, 4));
            }
        }
    }
}