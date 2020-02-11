<?php
namespace App\News\Repository;

use App\News\Models\NewsData;

class NewsDataRepository
{
    protected $model;
    
    function __construct(NewsData $model)
    {
        $this->model = $model;
    }

    public function create($data = [])
    {
        if($this->model::create($data)) return true;

        return false;
    }
}