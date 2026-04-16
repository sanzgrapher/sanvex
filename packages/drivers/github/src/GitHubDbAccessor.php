<?php

namespace Sanvex\Drivers\GitHub;

use Sanvex\Core\DbAccessor;
use Sanvex\Drivers\GitHub\Resources\Db\IssuesDbResource;
use Sanvex\Drivers\GitHub\Resources\Db\RepositoriesDbResource;

class GitHubDbAccessor extends DbAccessor
{
    public function repositories(): RepositoriesDbResource
    {
        return new RepositoriesDbResource($this->driver);
    }

    public function issues(): IssuesDbResource
    {
        return new IssuesDbResource($this->driver);
    }
}
