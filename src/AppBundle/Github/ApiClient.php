<?php

namespace AppBundle\Github;

use AppBundle\Entity\Environment;
use AppBundle\Entity\Project;
use GuzzleHttp\Client;

class ApiClient
{
    /**
     * @var Client
     */
    private $client;

    /**
     * GithubApi constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param Project $project
     *
     * @return array
     * @throws \Exception
     */
    public function getBranchesFromRepository(Project $project): array
    {
        $result = $this->client->get(sprintf('/repos/%s/%s/branches', $project->getGithubOwner(), $project->getGithubProject()));
        if ($result->getStatusCode() !== 200) {
            throw new ApiException($result->getReasonPhrase());
        }
        $items    = json_decode($result->getBody()->getContents());
        $branches = [];
        foreach ($items as $branch) {
            $branches[$branch->name] = $branch->name;
        }

        return $branches;
    }

    /**
     * @param Project $project
     * @param string  $branch
     *
     * @return \stdClass
     * @throws \Exception
     */
    public function getBranchLastCommit(Project $project, string $branch): \stdClass
    {
        $result = $this->client->get(sprintf('/repos/%s/%s/branches/%s', $project->getGithubOwner(), $project->getGithubProject(), $branch));
        if ($result->getStatusCode() !== 200) {
            throw new ApiException($result->getReasonPhrase());
        }
        return json_decode($result->getBody()->getContents());
    }

    /**
     * @param Environment $environment
     * @param string      $branch
     *
     * @return array
     * @throws ApiException
     */
    public function getLastBranchCommits(Environment $environment, string $branch): array
    {
        $project = $environment->getProject();
        if (null !== $environment->getCurrentVersion()) {
            $lastCommit = $this->getCommit($project, $environment->getCurrentVersion()->getCommit());
            $url = sprintf('/repos/%s/%s/commits?sha=%s&since=%s', $project->getGithubOwner(), $project->getGithubProject(), $branch, $lastCommit->commit->author->date);
        } else {
            $url = sprintf('/repos/%s/%s/commits?sha=%s', $project->getGithubOwner(), $project->getGithubProject(), $branch);
        }

        $result = $this->client->get($url);
        if ($result->getStatusCode() !== 200) {
            throw new ApiException($result->getReasonPhrase());
        }
        return json_decode($result->getBody()->getContents());
    }

    private function getCommit(Project $project, string $sha)
    {
        $result = $this->client->get(sprintf('/repos/%s/%s/commits/%s', $project->getGithubOwner(), $project->getGithubProject(), $sha));
        if ($result->getStatusCode() !== 200) {
            throw new ApiException($result->getReasonPhrase());
        }

        return json_decode($result->getBody()->getContents());
    }
}
