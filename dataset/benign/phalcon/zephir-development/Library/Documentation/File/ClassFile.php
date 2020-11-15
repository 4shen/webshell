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

use Zephir\ClassDefinition;
use Zephir\CompilerFile;
use Zephir\Documentation;
use Zephir\Documentation\FileInterface;

class ClassFile implements FileInterface
{
    /**
     * @var ClassDefinition
     */
    protected $class;

    /**
     * @var CompilerFile
     */
    protected $compilerFile;

    public function __construct($config, CompilerFile $class)
    {
        $this->compilerFile = $class;
        $this->class = $class->getClassDefinition();
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTemplateName(): string
    {
        return 'class.phtml';
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getData(): array
    {
        $nsPieces = explode('\\', $this->class->getNamespace());

        $nsPatches = [];
        $nsStr = '';

        foreach ($nsPieces as $n) {
            if (\strlen($nsStr) > 0) {
                $nsStr .= '\\';
            }
            $nsStr .= $n;
            $nsPatches[$n] = $nsStr;
        }

        return [
            'classDefinition' => $this->class,
            'compilerFile' => $this->compilerFile,
            'className' => $this->class->getName(),
            'classNamespace' => $this->class->getNamespace(),
            'fullName' => $this->class->getCompleteName(),
            'methods' => $this->class->getMethods(),
            'namespacePieces' => $nsPatches,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getOutputFile(): string
    {
        return Documentation::classUrl($this->class);
    }
}
