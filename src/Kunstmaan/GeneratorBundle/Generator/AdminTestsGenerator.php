<?php

namespace Kunstmaan\GeneratorBundle\Generator;

use Kunstmaan\GeneratorBundle\Helper\GeneratorUtils;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Generates tests to test the admin backend generated by the default-site generator
 */
class AdminTestsGenerator extends Generator
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $fullSkeletonDir;

    /**
     * @param ContainerInterface $container   The container
     * @param Filesystem         $filesystem  The filesytem
     * @param string             $skeletonDir The skeleton directory
     */
    public function __construct(ContainerInterface $container, Filesystem $filesystem, $skeletonDir)
    {
        $this->container = $container;
        $this->filesystem = $filesystem;
        $this->fullSkeletonDir = GeneratorUtils::getFullSkeletonPath($skeletonDir);
    }

    /**
     * @param BundleInterface $bundle
     * @param OutputInterface $output
     */
    public function generate(BundleInterface $bundle, OutputInterface $output)
    {
        // This is needed so the renderFile method will search for the files
        // in the correct location
        $this->setSkeletonDirs(array($this->fullSkeletonDir));

        $parameters = array(
            'namespace' => $bundle->getNamespace(),
            'bundle' => $bundle,
            'isV4' => Kernel::VERSION_ID >= 40000,
        );

        $this->generateBehatTests($bundle, $output, $parameters);
    }

    /**
     * @param BundleInterface $bundle
     * @param OutputInterface $output
     * @param array           $parameters
     */
    public function generateBehatTests(BundleInterface $bundle, OutputInterface $output, array $parameters)
    {
        $dirPath = Kernel::VERSION_ID >= 40000 ? sprintf('%s/features', $this->container->getParameter('kernel.project_dir')) : sprintf('%s/Features', $bundle->getPath());
        $skeletonDir = sprintf('%s/Features', $this->fullSkeletonDir);

        // Copy all default feature files
        $featureFiles = (new Finder())
            ->files()
            ->in($skeletonDir)
            ->filter(function (\SplFileInfo $fileinfo) {
                return false !== strpos($fileinfo->getRelativePathName(), '.feature');
            })
            ->getIterator();

        foreach ($featureFiles as $file) {
            $this->filesystem->copy($file, $dirPath . '/' . $file->getFilename());
        }

        // Copy dummy media files used in scenarios
        $this->filesystem->mirror($skeletonDir . '/Media', $dirPath . '/bootstrap/Media');

        // Render the Context files to replace the namespace etc.
        if ($handle = opendir($skeletonDir . '/Context')) {
            $targetPath = Kernel::VERSION_ID >= 40000 ? $dirPath . '/bootstrap/' : $dirPath . '/Context/';
            while (false !== ($entry = readdir($handle))) {
                // Check to make sure we skip hidden folders
                // And we render the files ending in .php
                if (substr($entry, 0, 1) != '.' && substr($entry, -strlen('.php')) === '.php') {
                    $this->renderFile('/Features/Context/' . $entry, $targetPath . $entry, $parameters);
                }
            }

            closedir($handle);
        }

        // Replace admin password
        $contextPath = Kernel::VERSION_ID >= 40000 ? $dirPath . '/bootstrap/FeatureContext.php' : $dirPath . '/Context/FeatureContext.php';
        $featureContext = $contextPath . '/FeatureContext.php';
        if ($this->filesystem->exists($featureContext)) {
            $contents = file_get_contents($featureContext);
            $contents = str_replace(
                '-adminpwd-',
                $this->container->getParameter('kunstmaan_admin.admin_password'),
                $contents
            );
            file_put_contents($featureContext, $contents);
        }

        $output->writeln('Generating Behat Tests : <info>OK</info>');
    }
}
