<?php

namespace App\Command;

use App\Entity\Token;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'tokens:clear',
    description: 'Removes tokens by date/user id',
)]
class TokensClearCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManagerInterface)
    {
        $this->em = $entityManagerInterface;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('type', InputArgument::OPTIONAL, 'Expired or user id to delete tokens')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->writeln("Running token clear process");

        $option = $input->getArgument("type");
        $repo = $this->em->getRepository(Token::class);

        if($option == "expired") {
            $repo->removeExpiredTokens();
        }else if(!empty($option)){
            $repo->deleteUserTokens($option);
        }

        $io->success("");
        return Command::SUCCESS;
    }
}
