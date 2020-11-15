<?php

declare(strict_types=1);

namespace GrumPHP\IO;

use GrumPHP\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConsoleIO implements IOInterface, \Serializable
{
    private $input;
    private $output;

    /**
     * @var string
     */
    private $stdin = '';

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    public function isInteractive(): bool
    {
        return $this->input->isInteractive();
    }

    public function isDecorated(): bool
    {
        return $this->output->isDecorated();
    }

    public function isVerbose(): bool
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }

    public function isVeryVerbose(): bool
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE;
    }

    public function isDebug(): bool
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG;
    }

    public function write(array $messages, bool $newline = true): void
    {
        $this->doWrite($messages, $newline, false);
    }

    public function writeError(array $messages, bool $newline = true): void
    {
        $this->doWrite($messages, $newline, true);
    }

    public function getInput(): InputInterface
    {
        return $this->input;
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    public function readCommandInput($handle): string
    {
        if (!is_resource($handle)) {
            throw new RuntimeException(
                sprintf('Expected a resource stream for reading the commandline input. Got %s.', gettype($handle))
            );
        }

        if (0 !== ftell($handle)) {
            return $this->stdin;
        }

        $input = '';
        while (!feof($handle)) {
            $input .= fread($handle, 1024);
        }

        // When the input only consist of white space characters, we assume that there is no input.
        $this->stdin = !preg_match_all('/^([\s]*)$/', $input) ? $input : '';

        return $this->stdin;
    }

    private function doWrite(array $messages, bool $newline, bool $stderr): void
    {
        if (true === $stderr && $this->output instanceof ConsoleOutputInterface) {
            $this->output->getErrorOutput()->write($messages, $newline);

            return;
        }

        $this->output->write($messages, $newline);
    }

    public function style(): StyleInterface
    {
        return new SymfonyStyle($this->input, $this->output);
    }

    public function section(): ConsoleSectionOutput
    {
        return $this->output->section();
    }

    public function colorize(array $messages, string $color): array
    {
        return array_map(
            static function (string $message) use ($color) : string {
                return '<fg='.$color.'>'.$message.'</fg='.$color.'>';
            },
            $messages
        );
    }

    /**
     * Serializing this IO will result in an unwritable resource stream.
     * Therefor we serialize the data end build up a new stream instead.
     */
    public function serialize()
    {
        return serialize([
            'input' => [
                'arguments' => $this->input->getArguments(),
            ],
            'output' => [
                'verbosity' => $this->output->getVerbosity(),
            ],
        ]);
    }

    /**
     * Use the serialized data to rebuild new input + output streams.
     * Note: When you run in parallel mode, the stream will be non-blocking.
     * All tasks can write at the same time, which is not optimal.
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized, ['allowed_classes' => false]);

        $this->input = new ArrayInput(
            (array) ($data['input']['arguments'] ?? [])
        );
        $this->output = new ConsoleOutput(
            (int) ($data['output']['verbosity'] ?? ConsoleOutput::VERBOSITY_NORMAL)
        );
    }
}
