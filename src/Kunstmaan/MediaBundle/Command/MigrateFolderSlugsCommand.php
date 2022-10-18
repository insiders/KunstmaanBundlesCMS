<?php

namespace Kunstmaan\MediaBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Translation;
use Kunstmaan\MediaBundle\Entity\Folder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateFolderSlugsCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    /**
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Force slug (re)gen by appending a space to the name which will force the slug generation.
        // Then reset the name
        $repo = $this->entityManager->getRepository(Folder::class);
        $translationRepo = $this->entityManager->getRepository(Translation::class);
        $entities = $repo->findAll();
        foreach ($entities as $entity) {
            $entity->setName($entity->getName() . ' ');
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            $translations = $translationRepo->findTranslations($entity);
            foreach ($translations as $locale => $fields) {
                if (isset($fields['name'])) {
                    $entity->setTranslatableLocale($locale);
                    $entity->setName($fields['name']);
                    $this->entityManager->persist($entity);
                    $this->entityManager->flush();
                }
            }
        }
        $this->entityManager->flush();

        foreach ($entities as $entity) {
            $entity->setName(trim($entity->getName()));
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();

        $output->writeln('<info>All slugs have been generated.</info>');

        return 0;
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('kuma:media:generate-folder-slugs')
            ->setDescription('Fill existing media folder slugs')
            ->setHelp(
                'The <info>kuma:media:generate-folder-slugs</info> command can be used to generate slugs for media folders.'
            );
    }
}
