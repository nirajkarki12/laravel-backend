<?php

namespace App\Finance\Http\Controllers;

use App\Common\Http\Controllers\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Finance\Repository\BankRepository;

use Illuminate\Validation\Rule;
    /**
     *@group Admin Bank Management
     */
class BankController extends BaseApiController
{

    protected $bankRepo;

    public function __construct(BankRepository $bankRepo)
    {
        // set the bank repo
        $this->bankRepo = $bankRepo;
    }

    /**
    * Pagination List all banks
    *
    * Pagination List all banks in a json array
    *
    */
    public function paginateBank()
    {
        return $this->successResponse($this->bankRepo->paginateBankLists(10), 'Banks listing');
    }

    /**
    * List all banks
    *
    * List all banks in a json array
    *
    */
    public function index()
    {
        return $this->successResponse($this->bankRepo->getBankLists(), 'Banks listing');
    }

    /**
    * bank detail
    *
    * @bodyParam bank object required bank object
    */

    public function detail(int $bankId)
    {
        try {
            if(!$bank = $this->bankRepo->getBank($bankId)) throw new \Exception("Bank not found", 1);
            
            return $this->successResponse($bank, 'Banks detail');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 406);
        }
    }

    /**
    * Create a new bank
    *
    * @bodyParam name string required name of a bank
    * @bodyPram abbre string required abbreviation of a bank
    * @bodyParam logo string optional logo file image
    */

    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(),[
                'name'=>'required',
                'abbre'=>'required|unique:banks',
                'logo'=> 'required'
            ]);

            if(!$validator->fails())
            {
                if(!$bank = $this->bankRepo->create($request)) throw new \Exception("Could not process", 1);
                
                return $this->successResponse($bank, 'Bank added');

            }else {
                throw new \Exception($validator->errors()->first(), 1);
            }

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 406);
        }
    }

     /**
    * Delete a bank
    *
    * @bodyParam bank object required bank object
    */

    public function destroy(int $bankId)
    {
        try {
            if(!$this->bankRepo->delete($bankId)) throw new \Exception("Error Processing Request", 1);
            
            return $this->successResponse([], 'Bank deleted');

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 406);
        }
    }

     /**
    * Update a bank data
    *
    * @bodyParam bank object required bank object
    */
    public function update(Request $request)
    {
        try {
            $validator=Validator::make($request->all(),[
                'name'=>'required:max:255',
                'abbre'=>[
                    'required',
                    Rule::unique('banks')->ignore($request->id),
                ], // needs to be check unique on edit
                'id'=>'required'
            ]);

            if(!$validator->fails())
            {
                if(!$bank = $this->bankRepo->update($request, $request->id)) throw new \Exception("Could not process", 1);

                return $this->successResponse([], 'Bank updated');

            }else{
                throw new \Exception($validator->errors()->first(), 1);
            }

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 406);
        }
    }
}
