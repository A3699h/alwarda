<?php

namespace App\Command;

use App\Repository\MessageFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class MessageFilesRemoveOldCommand extends Command
{
    protected static $defaultName = 'message-files:remove-old';
    private $messageFileRepository;
    private $kernel;
    private $fileSystem;
    private $manager;

    public function __construct(KernelInterface $kernel,
                                EntityManagerInterface $manager,
                                FileSystem $fileSystem,
                                MessageFileRepository $messageFileRepository
        , string $name = null)
    {
        parent::__construct($name);
        $this->messageFileRepository = $messageFileRepository;
        $this->kernel = $kernel;
        $this->fileSystem = $fileSystem;
        $this->manager = $manager;
    }

    protected
    function configure()
    {
        $this
            ->setDescription('Add a short description for your command');
    }

    protected
    function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $qrCodesFolder = $this->kernel->getProjectDir() . '/public/qrCodes/';
        $oldFiles = $this->messageFileRepository->findOld();
        foreach ($oldFiles as $messageFile) {
            $this->manager->remove($messageFile);
            $this->manager->flush();
            $qrCode = $messageFile->getQrCode();
            if ($qrCode && file_exists($qrCodesFolder . $qrCode)) {
                $fileName = $qrCodesFolder . $qrCode;
                $this->fileSystem->remove($fileName);
            }
        }
        return 0;
    }
}
