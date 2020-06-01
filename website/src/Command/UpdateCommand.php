<?php

namespace App\Command;

use App\Service\AutoTraderUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
    protected static $defaultName = 'app:update';
    
    /** @var AutoTraderUpdater */
    private $autoTraderUpdater;
    
    public function __construct(AutoTraderUpdater $autoTraderUpdater, string $name = null)
    {
        parent::__construct($name);
        
        $this->autoTraderUpdater = $autoTraderUpdater;
    }
    
    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->autoTraderUpdater->update();
    }
}
