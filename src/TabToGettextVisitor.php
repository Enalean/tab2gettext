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

        if (!$node->args[0]->value instanceof Node\Scalar\String_ || strpos($node->args[0]->value->value, $this->primarykey) !== 0) {
            return null;
        }

        if (!$node->args[1]->value instanceof Node\Scalar\String_) {
            return null;
        }

        $this->collector->add(
            $node->args[0]->value->value,
            $node->args[1]->value->value
        );

        $sentence_to_translate = $this->dictionary->get($node->args[0]->value->value, $node->args[1]->value->value);
        $nb_substitutions = SprintfSubstitution::countSubstitutions($sentence_to_translate);

        $sentence = SprintfSubstitution::convertFromTabFormat($sentence_to_translate);

        $gettext_call = new Node\Expr\FuncCall(
            new Node\Name('dgettext'),
            [
                new Node\Scalar\String_($this->domain),
                new Node\Scalar\String_($sentence)
            ]
        );
        if ($nb_args <= 2) {
            $this->checkNbSubstitutions($node, $sentence, $nb_substitutions, 0);
            return $gettext_call;
        }

        $args = [$gettext_call];
        for ($i = 2; $i < $nb_args; $i++) {
            if ($node->args[$i]->value instanceof Node\Expr\Array_) {
                $this->checkNbSubstitutions($node, $sentence, $nb_substitutions, count($node->args[$i]->value->items));
                array_push($args, ...($node->args[$i]->value->items));
            } else {
                $this->checkNbSubstitutions($node, $sentence, $nb_substitutions, 1);
                $args[] = $node->args[$i];
            }
        }

        return new Node\Expr\FuncCall(
            new Node\Name('sprintf'),
            $args
        );
    }

    private function checkNbSubstitutions(Node $node, string $sentence, int $expected_count, int $actual_count): void
    {
        if ($actual_count !== $expected_count) {
            $line = $node->getAttribute('startLine');
            throw new MismatchSubstitutionCountException("Expected substitution count differs (expected: $expected_count, actual: $actual_count) for: «${sentence}» in: $this->filepath at L$line");
        }
    }
}
