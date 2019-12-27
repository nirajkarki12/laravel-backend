<?php 

namespace App\Finance\Repository;

use App\Common\Repository\RepositoryInterface;
use App\Finance\Models\AtmPrice;
use Illuminate\Http\Request;
use File;

class AtmPriceRepository implements RepositoryInterface
{
    // model property on class instances
    protected $atmPrice;

    // Constructor to bind model to repo
    public function __construct(AtmPrice $atmPrice) {
        $this->atmPrice = $atmPrice;
    }

    public function all() {
        return $this->atmPrice->all();
    }

    public function create(Request $request) {
        return $this->atmPrice->create($request->all());
    }

    public function update(Request $request, int $id) {
        $atmPrice = $this->atmPrice->find($id);
        
        return $atmPrice->update($request->all());
    }

    public function delete(int $id) {
        return $this->atmPrice->destroy($id);
    }

    public function show(int $id) {
        return $this->atmPrice->findOrFail($id);
    }

    public function getModel() {
        return $this->atmPrice;
    }

    public function setModel(AtmPrice $atmPrice) {
        $this->atmPrice = $atmPrice;
        return $this;
    }

    public function with($relations) {
        return $this->atmPrice->with($relations);
    }

    public function getAtmChargeByFromAndToBank(Request $request, int $currentId = null) {
        $result =  $this->atmPrice::where('bank_from', $request->bank_from)
                    ->whereJsonContains('bank_to', $request->bank_to)
                    ->whereJsonContains('network', $request->network)
                    ;
        if($currentId) {
            $result->where('id', '!=', $currentId);
        }

        return $result->first();
    }

    public function getAtmPriceLists() {
        return $this->atmPrice::join('banks', 'banks.id', 'atm_prices.bank_from')
                ->select('*', 'atm_prices.id as id')
                ->get()
                ;
    }

    public function paginateAtmPriceLists(int $limit = 10) {
        return $this->atmPrice::join('banks', 'banks.id', 'atm_prices.bank_from')
                ->select('*', 'atm_prices.id as id')
                ->paginate($limit)
                ;
    }

    public function getAtmPriceListsByFromAndToBank($bankFrom, $bankTo) {
        return $this->atmPrice::join('banks AS b', 'b.id', 'atm_prices.bank_from')
                ->where('bank_from', $bankFrom)
                ->whereRaw('JSON_CONTAINS(bank_to->"$[*].id", ?)', $bankTo)
                ->select(
                    'network',
                    'charge',
                    'note',
                    'b.name as bankName',
                    'b.logo_full_path as logo',
                    'b.abbre',
                    'bank_to'
                    )
                ->get()
                ;
    }
}