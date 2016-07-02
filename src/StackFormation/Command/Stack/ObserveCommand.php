<?php

namespace StackFormation\Command\Stack;

use StackFormation\Helper;
use StackFormation\Helper\Validator;
use StackFormation\Observer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ObserveCommand extends \StackFormation\Command\AbstractCommand
{

    protected function configure()
    {
        $this
            ->setName('stack:observe')
            ->setDescription('Observe stack progress')
            ->addArgument(
                'stack',
                InputArgument::REQUIRED,
                'Stack'
            )
            ->addOption(
                'deleteOnTerminate',
                null,
                InputOption::VALUE_NONE,
                'Delete current stack if StackFormation received SIGTERM (e.g. Jenkins job abort) or SIGINT (e.g. CTRL+C)'
            );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->interactAskForStack($input, $output, null, '/IN_PROGRESS/');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stackName = $input->getArgument('stack');
        Validator::validateStackname($stackName);

        $stack = $this->getStackFactory()->getStack($stackName);

        $observer = new Observer($stack, $this->getStackFactory(), $output);
        if ($input->getOption('deleteOnTerminate')) {
            $observer->deleteOnSignal();
        }
        return $observer->observeStackActivity();
    }
}
