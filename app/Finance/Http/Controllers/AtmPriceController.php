<?php

namespace App\Finance\Http\Controllers;

use App\Common\Http\Controllers\BaseApiController;
use App\Finance\Http\Requests\AtmPriceRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Finance\Repository\AtmPriceRepository;
/**
*@group Admin Atm Price Management
*/
class AtmPriceController extends BaseApiController
{
    protected $atmPriceRepo;

    public function __construct(AtmPriceRepository $atmPriceRepo)
    {
       // set the atm price repo
       $this->atmPriceRepo = $atmPriceRepo;
    }

    /**
    *List atm price/charge lists
    *
    *json array
    *
    */
    public function index()
    {
        return $this->successResponse($this->atmPriceRepo->paginateAtmPriceLists(10), 'Atm prices listing');
    }

    /**
    * atm charge detail
    *
    * @bodyParam atmPriceId required AtmPrice object
    */

    public function detail(int $atmPriceId)
    {
        try {

            if(!$atmPrice = $this->atmPriceRepo->show($atmPriceId)) throw new \Exception("ATM Charge Record not found", 1);
            
            return $this->successResponse($atmPrice, 'Atm price detail');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 406);
        }
    }

    /**
    *create a new atm charge relation
    *
    *@bodyParam bank_from integer required id of the first bank in relation
    *@bodyParam bank_to array required array of the ids of the banks
    *@bodyParam network string required network name eg. sct,visa,mastercard fetch the list from the api
    *@bodyParam charge integer optional charge for the withdraw, default value=N/A
    */

    public function store(Request $request)
    {
        try {

            if(!\is_array($request->bank_to))
            {
                throw new \Exception("Process required an array list of bank_to", 1);
            }

            if(collect($request->bank_to)->pluck('id')->contains($request->bank_from))
            {
                throw new \Exception("You can not add same banks",1);
            }

            if($this->atmPriceRepo->getAtmChargeByFromAndToBank($request)) throw new \Exception("This record has already been inserted", 1);

            if(!$this->atmPriceRepo->create($request)) throw new \Exception("Error Processing Request", 1);
                
            return $this->successResponse([], 'Atm Charge Record Added Successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 406);
        }
    }

    public  function update(Request $request)
    {
        try {

            if(!\is_array($request->bank_to))
            {
                throw new \Exception("Process required an array list of bank_to", 1);
            }
            // needs to be verified
            // if($this->atmPriceRepo->getAtmChargeByFromAndToBank($request, $request->id)) throw new \Exception("This record has already been inserted", 1);

            if(!$this->atmPriceRepo->update($request, $request->id)) throw new \Exception("Error Processing Request", 1);
            
            return $this->successResponse([], 'Atm Charge Record Updated Successfully');

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 406);
        }
    }

    /**
    *delete a atm charge relation
    *@bodyParam atmprice id required atmprice an instance of AtmPrice class
    */
    public function destroy(int $atmpriceId)
    {
        try 
        {
            if(!$this->atmPriceRepo->delete($atmpriceId)) throw new \Exception("Error Processing Request", 1);
            
            return $this->successResponse([], 'Atm Charge Record Removed Successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 406);
        }
    }
}
