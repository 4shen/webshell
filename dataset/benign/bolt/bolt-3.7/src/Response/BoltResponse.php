<?php

namespace Bolt\Response;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Stopwatch\Stopwatch;
use Twig\Error\Error;
use Twig\Template;

/**
 * BoltResponse uses a renderer and context variables
 * to create the response content.
 *
 * @deprecated Deprecated since 3.3, use TemplateResponse instead.
 *
 * @author Ross Riley <riley.ross@gmail.com>
 */
class BoltResponse extends Response
{
    /** @var Template */
    protected $template;
    protected $context = [];
    protected $compiled = false;
    /** @var Stopwatch|null */
    protected $stopwatch;

    /**
     * Constructor.
     *
     * @param Template $template An object that is able to render a template with context
     * @param array    $context  An array of context variables
     * @param array    $globals  An array of global context variables
     * @param int      $status   The response status code
     * @param array    $headers  An array of response headers
     */
    public function __construct(Template $template, array $context = [], array $globals = [], $status = 200, $headers = [])
    {
        parent::__construct(null, $status, $headers);
        $this->template = $template;
        $this->context = $context;

        $this->addGlobals($globals);
    }

    /**
     * Factory method for chainability.
     *
     * @param Template $template An object that is able to render a template with context
     * @param array    $context  An array of context variables
     * @param array    $globals  An array of global context variables
     * @param int      $status   The response status code
     * @param array    $headers  An array of response headers
     *
     * @return \Bolt\Response\BoltResponse
     */
    public static function create($template = null, $context = [], $globals = [], $status = 200, $headers = [])
    {
        return new static($template, $context, $globals, $status, $headers);
    }

    public function setStopwatch(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    /**
     * Sets the Renderer used to create this Response.
     *
     * @param Template $template A template object
     */
    public function setTemplate(Template $template)
    {
        if ($this->compiled) {
            throw new \LogicException('Template cannot be changed after the response is compiled');
        }
        $this->template = $template;
    }

    /**
     * Sets the context variables for this Response.
     *
     * @param array $context
     */
    public function setContext(array $context)
    {
        if ($this->compiled) {
            throw new \LogicException('Context cannot be changed after the response is compiled');
        }
        $this->context = $context;
    }

    /**
     * Returns the template.
     *
     * @return Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Returns the context.
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Gets globals from the template.
     *
     * @return array
     */
    public function getGlobalContext()
    {
        return $this->template->getEnvironment()->getGlobals();
    }

    /**
     * Adds globals to the template.
     *
     * @param array $globals
     */
    public function addGlobals(array $globals)
    {
        foreach ($globals as $name => $value) {
            $this->addGlobalContext($name, $value);
        }
    }

    /**
     * Adds a global to the template.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function addGlobalContext($name, $value)
    {
        $this->template->getEnvironment()->addGlobal($name, $value);
    }

    /**
     * Gets the name of the main loaded template.
     *
     * @return string
     */
    public function getTemplateName()
    {
        return $this->template->getTemplateName();
    }

    /**
     * Returns the Response as a string.
     *
     * @return string The Response as HTML
     */
    public function __toString()
    {
        try {
            return $this->getContent();
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Gets HTML content for the response.
     *
     * @return string
     */
    public function getContent()
    {
        if (!$this->compiled) {
            $this->compile();
        }

        return parent::getContent();
    }

    /**
     * Returns whether the response has been compiled.
     *
     * @return bool
     */
    public function isCompiled()
    {
        return $this->compiled;
    }

    /**
     * Compiles the template using the context.
     */
    public function compile()
    {
        if ($this->stopwatch) {
            $this->stopwatch->start('bolt.render', 'template');
        }
        $output = $this->template->render($this->context);
        $this->setContent($output);
        $this->compiled = true;
        if ($this->stopwatch) {
            $this->stopwatch->stop('bolt.render');
        }
    }

    /**
     * The __toString method isn't allowed to throw exceptions so we turn them into an error instead.
     *
     * @param \Exception $e
     *
     * @return string
     */
    private function handleException(\Exception $e)
    {
        trigger_error($e->getMessage() . "\n" . $e->getTraceAsString(), E_USER_WARNING);
        if ($e instanceof Error) {
            return '<strong>' . $e->getRawMessage() . '</strong>';
        }

        return '';
    }
}
