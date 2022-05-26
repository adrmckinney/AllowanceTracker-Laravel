<?php

namespace App\Http\Controllers;

use App\Models\Chore;
use App\Types\ChoreType;
use Illuminate\Http\Request;

class ChoreController extends Controller
{
    public function getChore(Request $request, $id)
    {
        if ($request->user()->cannot('getOne', Chore::class)) {
            abort(403, 'You do not have access to get this chore');
        }

        return $this->getChoreById($id);
    }

    public function getChoreList(Request $request)
    {
        if ($request->user()->cannot('getMany', Chore::class)) {
            abort(403, 'You do not have access to get chores');
        }

        return Chore::all();
    }

    public function createChore(Request $request)
    {

        if ($this->choreExists($request->name)) {
            abort(406, 'A chore with this name already exists.');
        }
        if ($request->user()->cannot('create', Chore::class)) {
            abort(403, 'You do not have access to create a chore');
        }

        $createChore = new ChoreType([
            'name' => $request['name'],
            'description' => $request['description'],
            'cost' => $request['cost'],
        ]);

        return Chore::create($createChore->toCreateArray());
    }

    public function updateChore(Request $request)
    {
        $fields = [
            'name',
            'description',
            'cost',
        ];

        if ($this->choreExists($request->name)) {
            abort(406, 'A chore with this name already exists.');
        }

        $chore = $this->getChoreById($request->id);

        if ($request->user()->cannot('update', $chore)) {
            abort(403, 'You do not have access to update this chore');
        };


        foreach ($fields as $field) {
            if ($request->$field) {
                $chore->$field = $request->$field;

                $chore->save();
            }
        }
        return $chore;
    }

    public function deleteChore(Request $request, $id)
    {
        if ($request->user()->cannot('delete', Chore::class)) {
            abort(403, 'You do not have access to delete this chore');
        };

        $chore = $this->getChoreById($id);
        $chore->delete();

        return $chore;
    }

    public function getChoreById($id)
    {
        return Chore::find($id);
    }

    public function choreExists($name)
    {
        return Chore::where('name', '=', $name)->first();
    }
}
