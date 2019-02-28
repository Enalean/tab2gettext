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

        $nb_args = count($node->args);
        if ($nb_args < 2) {
            return null;
        }

        if (!$node->args[0]->value instanceof Node\Scalar\String_ || $node->args[0]->value->value !== $this->primarykey) {
            return null;
        }

        if (!$node->args[1]->value instanceof Node\Scalar\String_) {
            return null;
        }

        $this->collector->add(
            $node->args[0]->value->value,
            $node->args[1]->value->value
        );
        $gettext_call = new Node\Expr\FuncCall(
            new Node\Name('dgettext'),
            [
                new Node\Scalar\String_($this->domain),
                new Node\Scalar\String_(
                    SprintfSubstitution::convertFromTabFormat(
                        $this->dictionary->get($this->primarykey, $node->args[1]->value->value)
                    )
                )
            ]
        );
        if ($nb_args <= 2) {
            return $gettext_call;
        }

        $args = [$gettext_call];
        for ($i = 2; $i < $nb_args; $i++) {
            if ($node->args[$i]->value instanceof Node\Expr\Array_) {
                array_push($args, ...($node->args[$i]->value->items));
            } else {
                $args[] = $node->args[$i];
            }
        }

        return new Node\Expr\FuncCall(
            new Node\Name('sprintf'),
            $args
        );
    }
}
