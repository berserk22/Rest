<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Rest\Console;

use Core\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Hash extends Command {

    private string $secret = 'apiSecret';

    /**
     * @return void
     */
    protected function configure(): void {
        $this->setName('hash:apihash')
            ->setDescription("Hashes a given string using Bcrypt.")
            ->addArgument('password', InputArgument::REQUIRED, 'What do you wish to hash)')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $input = $input->getArgument('password');
        $hash = hash("sha512", $input);
        $result = hash("sha512", $this->secret.strrev($this->secret.$hash));

        $this->default(sprintf(
            'Your password hashed: %s',
            $result
        ));
        return 1;
    }
}
