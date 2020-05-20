<?php

namespace App\Command;

use App\Service\AutoTraderParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseCommand extends Command
{
    protected static $defaultName = 'app:parse';
    
    /** @var AutoTraderParser */
    private $autoTraderParser;
    
    public function __construct(AutoTraderParser $autoTraderParser, string $name = null)
    {
        parent::__construct($name);
        
        $this->autoTraderParser = $autoTraderParser;
    }
    
    protected function configure()
    {
        $this->addArgument('page', InputArgument::OPTIONAL, 'The search page to parse.', 1);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->autoTraderParser->parse(
            $input->getArgument('page')
        );
    }
}
