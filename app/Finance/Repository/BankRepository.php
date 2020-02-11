<?php 

namespace App\Finance\Repository;

use App\Common\Repository\RepositoryInterface;
use App\Finance\Models\Bank;
use Illuminate\Http\Request;
use File;

class BankRepository implements RepositoryInterface
{
    // model property on class instances
    protected $bank,$storageFolder;

    // Constructor to bind model to repo
    public function __construct(Bank $bank) {
        $this->bank = $bank;
        $this->storageFolder = '/app/public/bank/logo';
    }

    public function all() {
        return $this->bank->all();
    }

    public function create(Request $request) {
        $data = $request->all();

        if($request->has('logo'))
        {
            $data['logo'] = Helper::imageUpload($request->logo,$this->storageFolder);
            $data['logo_full_path'] = $data['logo'];
        }

        $bank = $this->bank->create($data);

        return $bank;
    }

    public function update(Request $request, int $id) {
        $bank = $this->bank->find($id);

        if($request->has('logo'))
        {
            if($bank->logo)
            {
                if(\file_exists('../storage/app/public/bank/logo/' .$bank->logo))
                {
                    File::delete('../storage/app/public/bank/logo/' .$bank->logo);
                }
            }
            $bank->logo = imageUpload($request->logo);
            $bank->logo_full_path = $bank->logo;
        }
        return $bank->update($request->all());
    }

    public function delete(int $id) {
        $bank = $this->bank->find($id);

        if($bank->logo)
        {
            if(\file_exists('../storage/app/public/bank/logo/'.$bank->logo))
            {
                File::delete('../storage/app/public/bank/logo/'.$bank->logo);
            }
        }
        return $this->bank->destroy($id);
    }

    public function show(int $id) {
        return $this->bank->findOrFail($id);
    }

    public function getModel() {
        return $this->bank;
    }

    public function setModel(Bank $bank) {
        $this->bank = $bank;
        return $this;
    }

    public function with($relations) {
        return $this->bank->with($relations);
    }

    public function getBank(int $bankId) {
        return $this->bank::where('id', $bankId)
                ->select('id', 'name', 'abbre', 'logo_full_path as logo')
                ->first();
    }

    public function paginateBankLists(int $limit = 10) {
        return $this->bank::select('id', 'name', 'abbre', 'logo_full_path as logo')
            ->orderBy('name','asc')
            ->paginate($limit)
            ;
    }

    public function getBankLists() {
        return $this->bank::select('id', 'name', 'abbre', 'logo_full_path as logo')
            ->orderBy('name','asc')
            ->get()
            ;

    }
}