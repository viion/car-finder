<?php

namespace App\Command;

use App\Service\AutoTraderParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseCommand extends Command
{
    protected static $defaultName = 'app:parse';
    
    /** @var AutoTraderParser */
    private $parser;
    
    public function __construct(AutoTraderParser $parser, string $name = null)
    {
        parent::__construct($name);
        
        $this->parser = $parser;
    }
    
    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->parser->parse();
    }
}
