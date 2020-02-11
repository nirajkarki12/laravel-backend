<?php

namespace App\News\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Common\Http\Controllers\BaseApiController;
use Illuminate\Support\Facades\Validator;
use App\News\Repository\NewsDataRepository;
use App\Helpers\Helper;

class NewsDataController extends BaseApiController
{

    protected $storageFolder;
    protected $repo;

    function __construct(NewsDataRepository $repo)
    {
        $this->repo = $repo;
        $this->storageFolder = '/app/public/userdata';
        $this->publicfolder  = '/storage/userdata/';
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('news::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('news::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        
        try {
            $validator = Validator::make($request->all(),[
                'user_id'       =>'required',
                'name'          =>'required',
                'email'         =>'required',
                'address'       =>'required',
                'mobile'        =>'required',
                'files'         =>'array',
                'files.*'         =>'mimes:png,jpg,jpeg',
                'videos'        =>'array',
                'videos.*'         =>'mimes:mp4,avi,3gp,flv,webm',
            ]);
    
            if($validator->fails()) throw new \Exception($validator->errors()->first());
    
            $files = array();
            $input = $request->all();
    
            
            if($request->has('files'))
            {
                foreach($request->file('files') as $file)
                {
                    $filename = Helper::imageUplaod($file,$this->storageFolder);
                    $files['files'][]=\URL::to($this->publicfolder.$filename);
                }
                $input['files'] = $files['files'];
            }
    
            if($request->has('videos'))
            {
                foreach($request->file('videos') as $video)
                {
                    $videoname = Helper::imageUplaod($video,$this->storageFolder);
                    $files['videos'][]=\URL::to($this->publicfolder.$videoname);
                }
                $input['videos'] = $files['videos'];
            }
    
            if($this->repo->create($input))
            {
                return $this->successResponse([], 'Files posted');
            }

            throw new \Exception('Unable to post the files',1);

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(),403);
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('news::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('news::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
