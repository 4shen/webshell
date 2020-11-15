<?php

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Extension\TableOfContents;

use League\CommonMark\Exception\InvalidOptionException;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\ListData;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\CommonMark\Node\Inline\HtmlInline;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalink;
use League\CommonMark\Extension\TableOfContents\Node\TableOfContents;
use League\CommonMark\Extension\TableOfContents\Normalizer\AsIsNormalizerStrategy;
use League\CommonMark\Extension\TableOfContents\Normalizer\FlatNormalizerStrategy;
use League\CommonMark\Extension\TableOfContents\Normalizer\NormalizerStrategyInterface;
use League\CommonMark\Extension\TableOfContents\Normalizer\RelativeNormalizerStrategy;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\StringContainerHelper;

final class TableOfContentsGenerator implements TableOfContentsGeneratorInterface
{
    public const STYLE_BULLET  = ListBlock::TYPE_BULLET;
    public const STYLE_ORDERED = ListBlock::TYPE_ORDERED;

    public const NORMALIZE_DISABLED = 'as-is';
    public const NORMALIZE_RELATIVE = 'relative';
    public const NORMALIZE_FLAT     = 'flat';

    /**
     * @var string
     *
     * @psalm-readonly
     */
    private $style;

    /**
     * @var string
     *
     * @psalm-readonly
     */
    private $normalizationStrategy;

    /**
     * @var int
     *
     * @psalm-readonly
     */
    private $minHeadingLevel;

    /**
     * @var int
     *
     * @psalm-readonly
     */
    private $maxHeadingLevel;

    public function __construct(string $style, string $normalizationStrategy, int $minHeadingLevel, int $maxHeadingLevel)
    {
        $this->style                 = $style;
        $this->normalizationStrategy = $normalizationStrategy;
        $this->minHeadingLevel       = $minHeadingLevel;
        $this->maxHeadingLevel       = $maxHeadingLevel;
    }

    public function generate(Document $document): ?TableOfContents
    {
        $toc = $this->createToc();

        $normalizer = $this->getNormalizer($toc);

        $firstHeading = null;

        foreach ($this->getHeadingLinks($document) as $headingLink) {
            $heading = $headingLink->parent();
            // Make sure this is actually tied to a heading
            if (! $heading instanceof Heading) {
                continue;
            }

            // Skip any headings outside the configured min/max levels
            if ($heading->getLevel() < $this->minHeadingLevel || $heading->getLevel() > $this->maxHeadingLevel) {
                continue;
            }

            // Keep track of the first heading we see - we might need this later
            $firstHeading = $firstHeading ?? $heading;

            // Keep track of the start and end lines
            $toc->setStartLine($firstHeading->getStartLine());
            $toc->setEndLine($heading->getEndLine());

            // Create the new link
            $link      = new Link('#' . $headingLink->getSlug(), StringContainerHelper::getChildText($heading, [HtmlBlock::class, HtmlInline::class]));
            $paragraph = new Paragraph();
            $paragraph->setStartLine($heading->getStartLine());
            $paragraph->setEndLine($heading->getEndLine());
            $paragraph->appendChild($link);

            $listItem = new ListItem($toc->getListData());
            $listItem->setStartLine($heading->getStartLine());
            $listItem->setEndLine($heading->getEndLine());
            $listItem->appendChild($paragraph);

            // Add it to the correct place
            $normalizer->addItem($heading->getLevel(), $listItem);
        }

        // Don't add the TOC if no headings were present
        if (! $toc->hasChildren() || $firstHeading === null) {
            return null;
        }

        return $toc;
    }

    private function createToc(): TableOfContents
    {
        $listData = new ListData();

        if ($this->style === self::STYLE_BULLET) {
            $listData->type = ListBlock::TYPE_BULLET;
        } elseif ($this->style === self::STYLE_ORDERED) {
            $listData->type = ListBlock::TYPE_ORDERED;
        } else {
            throw InvalidOptionException::forParameter('table of contents list style', $this->style);
        }

        return new TableOfContents($listData);
    }

    /**
     * @return iterable<HeadingPermalink>
     */
    private function getHeadingLinks(Document $document): iterable
    {
        $walker = $document->walker();
        while ($event = $walker->next()) {
            if ($event->isEntering() && ($node = $event->getNode()) instanceof HeadingPermalink) {
                yield $node;
            }
        }
    }

    private function getNormalizer(TableOfContents $toc): NormalizerStrategyInterface
    {
        switch ($this->normalizationStrategy) {
            case self::NORMALIZE_DISABLED:
                return new AsIsNormalizerStrategy($toc);
            case self::NORMALIZE_RELATIVE:
                return new RelativeNormalizerStrategy($toc);
            case self::NORMALIZE_FLAT:
                return new FlatNormalizerStrategy($toc);
            default:
                throw InvalidOptionException::forParameter('table of contents normalization strategy', $this->normalizationStrategy);
        }
    }
}
