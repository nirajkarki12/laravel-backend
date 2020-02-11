<?php
namespace App\News\Repository;

use App\News\Models\News;

class NewsRepository
{
    protected $model;

    function __construct(News $model)
    {
        $this->model = $model;
    }

    public function index(News $news)
    {
        return $this->successResponse($this->leaderRepo->paginateLeaderLists($request, $this->getAuthUser()->id, 50), 'Leaders listing');
    }

    public function paginateNewsList(int $limit = 10) {
        return $this->model::orderBy('created_at','desc')->paginate($limit);
    }

    public function create($data = [])
    {
        if($news=$this->model::create($data))
        {
            return $news;
        }

        throw new \Exception('Post can not be created',1);
    }


    public function update($data = [],int $id)
    {
        $news = $this->model::find($id);

        if(!$news) throw new \Exception('No post is found',1);

        if(!$news->update($data)) throw new \Exception('No post is found',1);

        return true;
    }

    public function delete(int $id)
    {
        $news = $this->model::find($id);

        if(!$news) throw new \Exception('No post is found',1);

        if($news->delete()) return true;

        return new \Excpetion('Can not delete post',1);
    }

    public function edit(int $id)
    {
        $news = $this->model::find($id);

        if(!$news) throw new \Excpetion('No post found');

        return $news;
    }

}