<?php

/*
 * This file is part of the Zephir.
 *
 * (c) Phalcon Team <team@zephir-lang.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Zephir\Documentation\File;

use Zephir\CompilerFile;
use Zephir\Documentation;
use Zephir\Documentation\FileInterface;
use Zephir\Documentation\NamespaceHelper;

class NamespaceFile implements FileInterface
{
    /**
     * @var NamespaceHelper
     */
    protected $namespaceHelper;

    /**
     * @var CompilerFile
     */
    protected $compilerFile;

    public function __construct($config, NamespaceHelper $nh)
    {
        $this->namespaceHelper = $nh;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTemplateName(): string
    {
        return 'namespace.phtml';
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getData(): array
    {
        return [
            'namespaceHelper' => $this->namespaceHelper,
            'subNamespaces' => $this->namespaceHelper->getNamespaces(),
            'subClasses' => $this->namespaceHelper->getClasses(),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getOutputFile(): string
    {
        return Documentation::namespaceUrl($this->namespaceHelper->getFullNamespace());
    }
}
