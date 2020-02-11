<?php

namespace App\News\Http\Controllers;

use App\Common\Http\Controllers\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\News\Repository\NewsRepository;

class NewsController extends BaseApiController
{
    protected $repo;

    public function __construct(NewsRepository $repo)
    {
        // set the leader repo
        $this->repo = $repo;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return $this->successResponse($this->repo->paginateNewsList(50), 'News Listing');
    }



    public function store(Request $request)
    {

        try {

            $validator = Validator::make($request->all(),[
                'name'=>'required',
            ]);

            if(!$validator->fails())
            {
                $news = $this->repo->create($request->all());

                if(!$news) throw new \Exception("Could not process", 1);

                return $this->successResponse([], 'Post added');

            }else {
                throw new \Exception($validator->errors()->first(), 1);
            }


        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 406);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $leaderId
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id)
    {
        try {
            if(!$news = $this->repo->edit($id)) throw new \Exception("Post not found", 1);
            return $this->successResponse($news, 'Leader detail');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 406);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
           $validator = Validator::make($request->all(),[
                'name'=>'required',
            ]);

            if(!$validator->fails())
            {
                if(!$this->repo->update($request->all(), $request->id)) throw new \Exception("Could not process", 1);

                return $this->successResponse([], 'Post updated');

            }else{
                throw new \Exception($validator->errors()->first(), 1);
            }

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 406);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        try{
            $this->repo->delete($id);
            return $this->successResponse([], 'Post delete success');
        }catch(\Exception $e)
        {
            return $this->errorResponse($e->getMessage(), 406);
        }
    }

}
