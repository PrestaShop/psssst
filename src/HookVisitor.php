<?php

namespace Psssst;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt;

class HookVisitor extends NodeVisitorAbstract
{
    public static $hooks = [];

    public static $module = null;

    public static $version = null;

    public static function reset()
    {
        self::$hooks = [];
        self::$module = null;
        self::$version = null;
    }

    public function enterNode(Node $node)
    {
        if ($this->nodeHasProperty($node, 'name')) {
            self::$module = $node->expr->value;
        }

        if ($this->nodeHasProperty($node, 'version')) {
            self::$version = $node->expr->value;
        }

        if ($node instanceof Stmt\ClassMethod) {
            $methodName = $node->name->name;
            if (strpos($methodName, 'hook') !== false) {
                self::$hooks[] = lcfirst(substr($methodName, 4));
            }
        }
    }

    /**
     * Check if node is a property of a class
     *
     * @param Node   $node Node
     * @param string $name Name of the property
     *
     * @return boolean
     */
    protected function nodeHasProperty($node, $name)
    {
        return $node instanceof Node\Expr\Assign &&
            $node->var instanceof Node\Expr\PropertyFetch &&
            $node->var->var instanceof Node\Expr\Variable &&
            $node->var->var->name === 'this' &&
            $node->var->name instanceof Node\Identifier &&
            $node->var->name->name === $name &&
            $node->expr instanceof Node\Scalar\String_;
    }
}
