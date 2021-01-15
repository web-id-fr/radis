<?php

namespace WebId\Radis\Console\Commands\Traits;

trait CheckGitBranch
{
    /**
     * @param string $gitBranch
     * @return bool
     */
    protected function checkGitBranch(string $gitBranch): bool
    {
        $repo = config('radis.git_repository');
        $gitBranchExist = exec("git ls-remote --heads git@github.com:$repo.git $gitBranch | wc -l ");

        if (!$gitBranchExist == 1) {
            $this->error("The git branch : $gitBranch does not exist !");
        }

        return $gitBranchExist == 1;
    }
}
