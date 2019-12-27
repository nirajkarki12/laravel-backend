<?php

namespace App\Finance\Http\Controllers;

use App\Common\Http\Controllers\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Finance\Models\Network;

/**
 *@group Admin Network Management
 *Admin APIs for managing networks
 */

class NetworkController extends BaseApiController
{
    /**
	 * Listing networks
	 *
	 * [Admin apis for listing networks]
	 *
	 */
    public function index()
    {
        $networks=Network::orderBy('name','asc')->get();
        return $this->successResponse($networks, 'Network listing');
    }

    /**
	 * Store a network
	 *
	 * [store network to database]
     *
     * @bodyParam name string required name of the atm network
	 *
	 */
    public function store(Request $request)
    {
        try {
            $validator=Validator::make($request->all(),[
                'name'=>'required|unique:networks'
            ]);

            if(!$validator->fails())
            {
                $network=Network::firstOrcreate([
                    'name' => strtoupper($request->name)
                ]);

            }else {
                throw new \Exception($validator->errors()->first(), 1);
            }

            return $this->successResponse($network, 'Network created');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 406);
        }
    }

    /*
        updating network
        @bodyParam name required name of a network
        @bodyParam id   required id of object

    */
    public function update(Request $request)
    {
        try {
            $validator=Validator::make($request->all(),[
                'name'=>'required|unique:networks',
                'id'=>'required'
            ]);

            if(!$validator->fails())
            {
                $network=Network::find($request->id);
                $network->name=strtoupper($request->name);
                $network->update();
                return $this->successResponse($network, 'Network Updated');

            }else {
                throw new \Exception($validator->errors()->first(), 1);
            }

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 406);
        }
    }

    /**
	 * delete a network
	 *
	 * [delete a network from database]
     *
     * @bodyParam network object required
	 *
	 */

    public function destroy(Network $network)
    {
        $network->delete();
        return $this->successResponse([], 'Network deleted');
    }

}
