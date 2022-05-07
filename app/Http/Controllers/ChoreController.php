<?php

namespace App\Http\Controllers;

use App\Data\Enums\ChoreApprovalStatuses;
use App\Models\Chore;
use Illuminate\Http\Request;

class ChoreController extends Controller
{
    public function createChore(Request $request)
    {

        if ($this->choreExists($request->name)) {
            abort(406, 'A chore with this name already exists.');
        }

        $chore = Chore::create([
            'name' => $request['name'],
            'description' => $request['description'],
            'cost' => $request['cost'],
        ]);

        if ($request->user()->cannot('create', $chore)) {
            abort(403, 'You do not have access to create a chore');
        }

        return $chore;
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

        // if (!$this->isRequestingApproval($request, 'approval_requested')) {
        if ($request->user()->cannot('update', $chore)) {
            abort(403, 'You do not have access to update this chore');
        };
        // }


        foreach ($fields as $field) {
            if ($request->$field) {
                // if ($this->isRequestingApproval($request, $field)) {
                //     $this->handleApprovalRequest($chore);
                // }

                $chore->$field = $request->$field;

                $chore->save();
            }
        }
        return $chore;
    }

    public function isRequestingApproval($request, $field)
    {
        return ($field === 'approval_requested' && $request->$field === true);
    }

    public function handleApprovalRequest($chore)
    {
        $chore['approval_request_date'] = date('Y-m-d H:i:s', time());
        $chore['approval_status'] = ChoreApprovalStatuses::$PENDING;

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
