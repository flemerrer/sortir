<?php

    declare(strict_types=1);

    namespace App\Command;

    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Process\Process;

    #[AsCommand(name: 'app:reset-db', description: 'Drop, recreate DB schema and load fixtures (dev only).')]
    class RunCommand extends Command
    {
        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            if ($_ENV['APP_ENV'] !== 'dev') {
                $output->writeln('<error>Cannot run outside dev environment.</error>');
                return Command::FAILURE;
            }

            $commands = [
                ['symfony', 'console', 'doctrine:database:drop', '--force', '--if-exists'],
                ['symfony', 'console', 'doctrine:database:create'],
                // choose ONE of these lines depending on your setup:
                ['symfony', 'console', 'doctrine:schema:update', '--force'],
                // or: ['symfony', 'console', 'doctrine:migrations:migrate', '--no-interaction'],
                ['symfony', 'console', 'doctrine:fixtures:load', '--no-interaction'],
            ];

            foreach ($commands as $cmd) {
                $process = new Process($cmd);
                $process->setTimeout(null);
                $process->run(function ($type, $buffer) use ($output) {
                    $output->write($buffer);
                });

                if (!$process->isSuccessful()) {
                    return Command::FAILURE;
                }
            }

            return Command::SUCCESS;
        }
    }
