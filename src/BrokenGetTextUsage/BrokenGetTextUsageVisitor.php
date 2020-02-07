<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */

namespace Tab2Gettext\BrokenGetTextUsage;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Psr\Log\LoggerInterface;

/**
 * Class SimpleTestToMockeryVisitor
 *
 * @package Reflector
 */
class BrokenGetTextUsageVisitor extends NodeVisitorAbstract
{
    /**
     * @var string
     */
    private $filepath;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var string|null
     */
    private $primary_key;

    public function __construct(string $filepath, LoggerInterface $logger, ?string $primary_key)
    {
        $this->filepath = $filepath;
        $this->logger = $logger;
        $this->primary_key = $primary_key;
    }

    /**
     * @param Node $node
     * @return int|null|Node|Node[]|Node\Expr\FuncCall|Node\Expr\MethodCall|Node\Expr\New_|Node\Expr\StaticCall
     * @throws \Exception
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Expr\MethodCall) {
            return null;
        }

        if ($node->name instanceof Node\Expr\Variable) {
            return null;
        }

        if ($node->name instanceof Node\Expr\PropertyFetch) {
            return null;
        }

        if ((string)$node->name !== 'getText') {
            return null;
        }

        $nb_args = count($node->args);
        if ($nb_args < 2) {
            return null;
        }

        if ($this->primary_key !== null) {
            if (!$node->args[0]->value instanceof Node\Scalar\String_ || strpos($node->args[0]->value->value, $this->primary_key) !== 0) {
                return null;
            }
        }

        if (!$node->args[0]->value instanceof Node\Scalar\String_ || !$node->args[1]->value instanceof Node\Scalar\String_) {
            $this->logger->warning("Bad usage of getText in {$this->filepath}:{$node->getStartLine()}");
            $message = '->getText(' . $this->debug($node->args[0]->value) .', '. $this->debug($node->args[1]->value);
            if (count($node->args) > 2) {
                $message .= ', …';
            }
            $message .= ')';
            $this->logger->notice($message);
        }
    }

    private function debug(Node $node): string
    {
        if ($node instanceof Node\Scalar\String_) {
            return $this->debugString($node);
        }

        if ($node instanceof Node\Expr\BinaryOp\Concat) {
            return $this->debug($node->left) .' . '. $this->debug($node->right);
        }

        if ($node instanceof Node\Expr\Variable) {
            return $this->debugVariable($node);
        }

        return get_class($node);
    }

    private function debugVariable(Node $node): string
    {
        return '$' . $node->name;
    }

    private function debugString(Node $node): string
    {
        return "'{$node->value}'";
    }

}
