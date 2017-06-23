<?php

namespace AppBundle\Service;

use AppBundle\Entity\Environment;
use AppBundle\Entity\Project;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CapistranoFinder
{
    protected const DEFAULT_ENV_CONFIG = [
        'keepReleases'     => 99,
        'defaultBranch'    => 'master',
        'branchSelectable' => false,
    ];
    /**
     * @var string
     */
    protected $rootPath;

    protected $configFileContent = [];

    /**
     * @var EntityManager
     */
    protected $entityManager;

    function __construct(EntityManager $entityManager, string $rootPath)
    {
        $this->entityManager = $entityManager;
        $this->rootPath      = $rootPath;
    }

    /**
     * @return array
     */
    public function listAvailableProject(): array
    {
        $existingFolders = $this->entityManager->getRepository(Project::class)->getUsedFolders();

        $finder = new Finder();
        $dirs   = [];
        /** @var SplFileInfo $dir */
        foreach ($finder->directories()->depth('== 0')->in($this->rootPath) as $dir) {
            if (!in_array($dir->getRelativePathname(), $existingFolders, true)) {
                $dirs[] = $dir->getRelativePathname();
            }
        }

        return $dirs;
    }

    /**
     * @param Project $project
     */
    private function setGithub(Project $project)
    {
        $content = $this->getConfigFileContent($project->getFolder());
        if (preg_match('/:repo_url, "git@github\.com:(.+)\/(.+)\.git"/', $content, $matches)) {
            $project->setGithubOwner($matches[1]);
            $project->setGithubProject($matches[2]);
        }
    }

    /**
     * @param string $folderName
     *
     * @return Project
     */
    public function createProjectFromConfig(string $folderName): Project
    {
        $contentFile = $this->getConfigFileContent($folderName);
        $project     = new Project();
        $project->setFolder($folderName);
        $project->setName(preg_match('/:application, "(.+)"/', $contentFile, $matches) ? $matches[1] : null);
        $this->setGithub($project);
        foreach ($this->getEnvironmentsFromProject($project) as $environment) {
            $project->addEnvironment($environment);
        }

        return $project;
    }

    public function refreshProjectFromConfig(Project $project): Project
    {
        $this->setGithub($project);

        $updatedEnvironments = [];
        foreach ($this->getEnvironmentsFromProject($project) as $environment) {
            if ($projectEnv = $project->getEnvironmentByName($environment->getName())) {
                $environment = $projectEnv->mergeUneditableProperties($environment);
            }
            $updatedEnvironments[] = $environment;
        }

        $project->setEnvironments($updatedEnvironments);
        $project->mergeUneditableProperties($project);

        return $project;
    }

    /**
     * @param string $projectName
     *
     * @return array|null
     */
    private function getProjectEnvFile(string $projectName): ?array
    {
        $finder       = new Finder();
        $environments = [];

        try {
            /** @var SplFileInfo $file */
            foreach ($finder->files()->in("$this->rootPath/$projectName/config/deploy") as $file) {
                preg_match('/(.*)\.rb/', $file->getRelativePathname(), $matches);
                if (count($matches) === 2) {
                    $environments[$matches[1]] = $file;
                }
            }
        } catch (\InvalidArgumentException $e) {
            return null;
        }

        return $environments;
    }

    /**
     * @param Project $project
     *
     * @return \Generator
     */
    private function getEnvironmentsFromProject(Project $project)
    {
        $defaultEnvConfig = $this->getProjectDefaults($project);
        $envFiles         = $this->getProjectEnvFile($project->getFolder());
        foreach ($envFiles as $name => $file) {
            $envConfig = $this->getEnvironmentConfig($file, $defaultEnvConfig);
            yield (new Environment())
                ->setName($name)
                ->setKeepReleases($envConfig['keepReleases'])
                ->setDefaultBranch($envConfig['defaultBranch'])
                ->setBranchSelectable($envConfig['branchSelectable']);
        }
    }

    /**
     * @param SplFileInfo $file
     *
     * @param array       $defaultEnvConfig
     *
     * @return array
     */
    private function getEnvironmentConfig(SplFileInfo $file, array $defaultEnvConfig): array
    {
        $contentFile = file_get_contents($file->getRealPath());
        $envConfig   = [];
        preg_match('/:keep_releases, (\d+)/', $contentFile, $matches);
        $envConfig['keepReleases'] = $matches[1] ?? $defaultEnvConfig['keepReleases'] ?? self::DEFAULT_ENV_CONFIG['keepReleases'];

        preg_match('/:branch, ([\'|"])(\w+)\1/', $contentFile, $matches);
        $envConfig['defaultBranch'] = $matches[2] ?? $defaultEnvConfig['defaultBranch'] ?? self::DEFAULT_ENV_CONFIG['defaultBranch'];

        $envConfig['branchSelectable'] = preg_match('/:branch, ENV\[\'branch\'\]/', $contentFile) || $defaultEnvConfig['branchSelectable'] || self::DEFAULT_ENV_CONFIG['branchSelectable'];

        return $envConfig;
    }

    /**
     * @param string $folderName
     *
     * @return mixed
     */
    private function getConfigFileContent(string $folderName)
    {
        if (!isset($this->configFileContent[$folderName])) {
            $deployFile = sprintf("%s/%s/config/deploy.rb", $this->rootPath, $folderName);
            if (!file_exists($deployFile)) {
                throw new NotFoundHttpException();
            }

            $this->configFileContent[$folderName] = file_get_contents($deployFile);
        }

        return $this->configFileContent[$folderName];
    }

    /**
     * @param Project $project
     *
     * @return array
     */
    private function getProjectDefaults(Project $project)
    {
        $contentFile      = $this->getConfigFileContent($project->getFolder());
        $defaultEnvConfig = [];
        if (preg_match('/:keep_releases, (\d+)/', $contentFile, $matches)) {
            $defaultEnvConfig['keepReleases'] = $matches[1];
        }

        if (preg_match('/:branch, ([\'|"])(\w+)\1/', $contentFile, $matches)) {
            $defaultEnvConfig['defaultBranch'] = $matches[2];
        }

        if (preg_match('/:branch, ENV\[\'branch\'\]/', $contentFile, $matches)) {
            $defaultEnvConfig['branchSelectable'] = true;
        }

        return $defaultEnvConfig;
    }
}
