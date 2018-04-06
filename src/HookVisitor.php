<?php

namespace Psssst;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\ClassMethod;

class HookVisitor extends NodeVisitorAbstract
{
    public static $hooks = [];

    public function leaveNode(Node $node) {
        if ($node instanceof ClassMethod) {
            $methodName = $node->name->name;
            if (strpos($methodName, 'hook') !== false) {
                self::$hooks[] = lcfirst(substr($methodName, 4));
            }
        }
    }
}