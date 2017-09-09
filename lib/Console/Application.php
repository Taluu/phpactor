<?php

namespace Phpactor\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use Phpactor\Phpactor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Phpactor\Container\Container;
use Phpactor\Container\ApplicationContainer;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Phpactor\Console\Command\GreetCommand;

class Application extends SymfonyApplication
{
    /**
     * @var Container
     */
    private $container;

    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct('Phpactor', '0.1');
        $this->foobar = 'string';
    }

    public function initialize()
    {
        $this->container = new ApplicationContainer();
        $this->container->init();

        foreach ($this->container->getServiceIdsForTag('ui.console.command') as $commandId => $attrs) {
            $this->add($this->container->get($commandId));
        }

        if (getenv('PHPACTORTEST')) {
            $this->add(new GreetCommand());
        }
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->setCatchExceptions(false);

        if ($output->isVerbose()) {
            $this->container->get('monolog.logger')->pushHandler(new StreamHandler(STDOUT));
        }

        $formatter = $output->getFormatter();
        $formatter->setStyle('highlight', new OutputFormatterStyle('red', null, [ 'bold' ]));
        $formatter->setStyle('diff-add', new OutputFormatterStyle('green', null, [  ]));
        $formatter->setStyle('diff-remove', new OutputFormatterStyle('red', null, [  ]));

        try {
            return parent::doRun($input, $output);
        } catch (\Exception $e) {
            if ($input->hasOption('format') && $input->getOption('format')) {
                return $this->handleException($output, $input->getOption('format'), $e);
            }

            if ($output instanceof ConsoleOutputInterface) {
                $this->renderException($e, $output->getErrorOutput());
            }

            return 255;
        }
    }


    private function handleException(OutputInterface $output, string $dumper, \Exception $e)
    {
        $errors = [
            'error' => $this->serializeException($e),
            'previous' => [
            ],
        ];

        while ($e = $e->getPrevious()) {
            $errors['previous'][] = $this->serializeException($e);
        }

        $this->container->get('console.dumper_registry')->get($dumper)->dump($output, $errors);

        return 64;
    }

    private function serializeException(\Exception $e)
    {
        return [
            'class' => get_class($e),
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
        ];
    }
}
