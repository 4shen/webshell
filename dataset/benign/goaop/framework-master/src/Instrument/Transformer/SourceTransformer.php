<?php
declare(strict_types = 1);
/*
 * Go! AOP framework
 *
 * @copyright Copyright 2011, Lisachenko Alexander <lisachenko.it@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Go\Instrument\Transformer;

/**
 * General source transformer interface
 */
interface SourceTransformer
{
    /**
     * Transformer decided to stop whole transformation process, all changes should be reverted
     */
    public const RESULT_ABORTED = 'aborted';

    /**
     * Transformer voted to abstain transformation, need to process following transformers to get result
     */
    public const RESULT_ABSTAIN = 'abstain';

    /**
     * Source code was transformed, can process next transformers if needed
     */
    public const RESULT_TRANSFORMED = 'transformed';

    /**
     * This method may transform the supplied source and return a new replacement for it
     *
     * @return string See RESULT_XXX constants in the interface
     */
    public function transform(StreamMetaData $metadata): string;
}
