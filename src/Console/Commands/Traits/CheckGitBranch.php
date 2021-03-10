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
        $gitBranchExist = (bool) intval(exec("git ls-remote --heads git@github.com:$repo.git $gitBranch | wc -l "));

        if (! $gitBranchExist) {
            $this->error("The git branch : $gitBranch does not exist !");
        }

        return $gitBranchExist;
    }
}
