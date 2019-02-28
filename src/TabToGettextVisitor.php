<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
 */

namespace Tab2Gettext;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Psr\Log\LoggerInterface;

/**
 * Class SimpleTestToMockeryVisitor
 *
 * @package Reflector
 */
class TabToGettextVisitor extends NodeVisitorAbstract
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var string
     */
    private $filepath;
    private $primarykey;
    private $domain;
    /**
     * @var Dictionary
     */
    private $dictionary;
    /**
     * @var ConvertedKeysCollector
     */
    private $collector;

    public function __construct(
        LoggerInterface $logger,
        $filepath,
        $primarykey,
        $domain,
        Dictionary $dictionary,
        ConvertedKeysCollector $collector
    ) {
        $this->logger = $logger;
        $this->filepath = $filepath;
        $this->primarykey = $primarykey;
        $this->domain = $domain;
        $this->dictionary = $dictionary;
        $this->collector = $collector;
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

        if (count($node->args) !== 2) {
            return null;
        }

        foreach ($node->args as $arg) {
            if (!$arg->value instanceof Node\Scalar\String_) {
                return null;
            }
        }

        if ($node->args[0]->value->value !== $this->primarykey) {
            return null;
        }

        $this->collector->add(
            $node->args[0]->value->value,
            $node->args[1]->value->value
        );
        return new Node\Expr\FuncCall(
            new Node\Name('dgettext'), [
                new Node\Scalar\String_($this->domain),
                new Node\Scalar\String_(
                    $this->dictionary->get($this->primarykey, $node->args[1]->value->value)
                )
            ]
        );
    }
}
