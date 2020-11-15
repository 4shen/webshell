<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 * (c) Rezo Zero / Ambroise Maupate
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\CommonMark\Extension\Footnote\Renderer;

use League\CommonMark\Configuration\ConfigurationAwareInterface;
use League\CommonMark\Configuration\ConfigurationInterface;
use League\CommonMark\Extension\Footnote\Node\FootnoteContainer;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class FootnoteContainerRenderer implements NodeRendererInterface, ConfigurationAwareInterface
{
    /** @var ConfigurationInterface */
    private $config;

    /**
     * {@inheritDoc}
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (! ($node instanceof FootnoteContainer)) {
            throw new \InvalidArgumentException('Incompatible node type: ' . \get_class($node));
        }

        $attrs = $node->getData('attributes', []);

        $attrs['class'] = $attrs['class'] ?? $this->config->get('footnote/container_class', 'footnotes');
        $attrs['role']  = 'doc-endnotes';

        $contents = new HtmlElement('ol', [], $childRenderer->renderNodes($node->children()));
        if ($this->config->get('footnote/container_add_hr', true)) {
            $contents = [new HtmlElement('hr', [], null, true), $contents];
        }

        return new HtmlElement('div', $attrs, $contents);
    }

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }
}
