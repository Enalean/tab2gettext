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

    public function __construct(LoggerInterface $logger, $filepath)
    {
        $this->logger   = $logger;
        $this->filepath = $filepath;
    }

    /**
     * @param Node $node
     * @return int|null|Node|Node[]|Node\Expr\FuncCall|Node\Expr\MethodCall|Node\Expr\New_|Node\Expr\StaticCall
     * @throws \Exception
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Expr\MethodCall && (string) $node->name === 'getText') {
            return new Node\Expr\FuncCall(
                new Node\Name('dgettext'), [
                    new Node\Scalar\String_('tuleap-tracker'),
                    new Node\Scalar\String_('toto')
                ]
            );
        }
    }
}
