<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Creates an admin user'
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->addArgument('phone', InputArgument::REQUIRED)
            ->addArgument('name', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED);
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Create Admin User');

        $name = $input->getArgument('username');
        $phoneNumber = $input->getArgument('phone');
        $password = $input->getOption('password');

        if (empty($name)) {
            $io->error('Name cannot be empty!');

            return Command::FAILURE;
        }

        if (empty($phoneNumber)) {
            $io->error('Phone number cannot be empty!');

            return Command::FAILURE;
        }

        if (empty($password)) {
            $io->error('Password cannot be empty!');

            return Command::FAILURE;
        }

        $existingUser = $this->entityManager->getRepository(User::class)
            ->findOneBy(['phoneNumber' => $phoneNumber]);

        if ($existingUser) {
            $io->error(sprintf('User with nymber %s is already created', $phoneNumber));

            return Command::FAILURE;
        }

        $user = new User();
        $user->setName($name);
        $user->setPhoneNumber($phoneNumber);
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Admin user created successfully!');

        return Command::SUCCESS;
    }
}
