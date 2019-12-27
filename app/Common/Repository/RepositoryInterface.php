<?php 

namespace App\Common\Repository;

use Illuminate\Http\Request;

interface RepositoryInterface
{
    public function all();

    public function create(Request $request);

    public function update(Request $request, int $id);

    public function delete(int $id);

    public function show(int $id);
}