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

    public static $displayName = null;

    public static $version = null;

    public static $author = null;

    public static $tab = null;

    public static $description = null;

    public static $versionsCompliancyMin = null;

    public static $versionsCompliancyMax = null;

    public static function reset()
    {
        self::$hooks = [];
        self::$module = null;
        self::$version = null;
        self::$author = null;
        self::$tab = null;
        self::$displayName = null;
        self::$versionsCompliancyMin = null;
        self::$versionsCompliancyMax = null;
        self::$description = null;
    }

    public function enterNode(Node $node)
    {
        if ($this->nodeHasProperty($node, 'name')) {
            self::$module = $node->expr->value;
        }

        if ($this->nodeHasProperty($node, 'version')) {
            self::$version = $node->expr->value;
        }

        if ($this->nodeHasProperty($node, 'author')) {
            self::$author = $node->expr->value;
        }

        if ($this->nodeHasProperty($node, 'tab')) {
            self::$tab = $node->expr->value;
        }

        if ($this->nodeHasProperty($node, 'displayName')) {
            self::$displayName = $this->getString($node);
        }

        if ($this->nodeHasProperty($node, 'description')) {
            self::$description = $this->getString($node);
        }

        if ($this->nodeHasProperty($node, 'ps_versions_compliancy')) {
            self::$versionsCompliancyMin = $this->getArrayValue($node->expr->items, 'min');
            self::$versionsCompliancyMax = $this->getArrayValue($node->expr->items, 'max');
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
            (
                $node->expr instanceof Node\Scalar\String_ ||
                $node->expr instanceof Node\Expr\Array_ ||
                $node->expr instanceof Node\Expr\MethodCall &&
                $node->expr->name->name === 'trans'
            );
    }

    protected function getArrayValue($items, $key)
    {
        foreach ($items as $item) {
            if (
                $item instanceof Node\Expr\ArrayItem &&
                $item->key->value === $key &&
                (
                    $item->value instanceof Node\Scalar\String_ ||
                    $item->value instanceof Node\Expr\ConstFetch
                )
            ) {
                return $item->value instanceof Node\Expr\ConstFetch ? (string) $item->value->name : $item->value->value;
            }
        }

        return null;
    }

    protected function getString($node)
    {
        if ($node->expr instanceof Node\Expr\MethodCall && $node->expr->name->name === 'trans') {
            return $node->expr->args[0]->value->value;
        } else if ($node->expr instanceof Node\Scalar\String_) {
            return $node->expr->value;
        }

        return null;
    }
}
