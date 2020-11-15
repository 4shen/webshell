<?php
namespace Psalm\Type\Atomic;

use function implode;
use Psalm\Codebase;
use Psalm\CodeLocation;
use Psalm\Internal\Type\TemplateResult;
use Psalm\StatementsSource;
use Psalm\Type;
use Psalm\Type\Union;
use Psalm\Storage\MethodStorage;
use function array_map;
use function strtolower;

class TTemplateParam extends \Psalm\Type\Atomic
{
    use HasIntersectionTrait;

    /**
     * @var string
     */
    public $param_name;

    /**
     * @var Union
     */
    public $as;

    /**
     * @var string
     */
    public $defining_class;

    /**
     * @param string $defining_class
     */
    public function __construct(string $param_name, Union $extends, string $defining_class)
    {
        $this->param_name = $param_name;
        $this->as = $extends;
        $this->defining_class = $defining_class;
    }

    public function __toString()
    {
        return $this->param_name;
    }

    /**
     * @return string
     */
    public function getKey(bool $include_extra = true)
    {
        if ($include_extra && $this->extra_types) {
            return $this->param_name . ':' . $this->defining_class . '&' . implode('&', $this->extra_types);
        }

        return $this->param_name . ':' . $this->defining_class;
    }

    /**
     * @return string
     */
    public function getAssertionString()
    {
        return $this->as->getId();
    }

    public function getId(bool $nested = false)
    {
        if ($this->extra_types) {
            return '(' . $this->param_name . ':' . $this->defining_class . ' as ' . $this->as->getId()
                . ')&' . implode('&', array_map(function ($type) {
                    return $type->getId(true);
                }, $this->extra_types));
        }

        return ($nested ? '(' : '') . $this->param_name
            . ':' . $this->defining_class
            . ' as ' . $this->as->getId() . ($nested ? ')' : '');
    }

    /**
     * @param  string|null   $namespace
     * @param  array<string> $aliased_classes
     * @param  string|null   $this_class
     * @param  int           $php_major_version
     * @param  int           $php_minor_version
     *
     * @return null
     */
    public function toPhpString(
        $namespace,
        array $aliased_classes,
        $this_class,
        $php_major_version,
        $php_minor_version
    ) {
        return null;
    }

    /**
     * @param  string|null   $namespace
     * @param  array<string, string> $aliased_classes
     * @param  string|null   $this_class
     * @param  bool          $use_phpdoc_format
     *
     * @return string
     */
    public function toNamespacedString(
        ?string $namespace,
        array $aliased_classes,
        ?string $this_class,
        bool $use_phpdoc_format
    ) {
        if ($use_phpdoc_format) {
            return $this->as->toNamespacedString(
                $namespace,
                $aliased_classes,
                $this_class,
                $use_phpdoc_format
            );
        }

        $intersection_types = $this->getNamespacedIntersectionTypes(
            $namespace,
            $aliased_classes,
            $this_class,
            $use_phpdoc_format
        );

        return $this->param_name . $intersection_types;
    }

    public function getChildNodes() : array
    {
        return [$this->as];
    }

    /**
     * @return bool
     */
    public function canBeFullyExpressedInPhp()
    {
        return false;
    }

    public function replaceTemplateTypesWithArgTypes(
        TemplateResult $template_result,
        ?Codebase $codebase
    ) : void {
        $this->replaceIntersectionTemplateTypesWithArgTypes($template_result, $codebase);
    }
}
