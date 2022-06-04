<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Comittee;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommiteeController extends Controller
{
    public function getAll() {
        return response()->json(Comittee::paginate());
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'icon' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->messages()
            ]);
        }

        $committee = Comittee::create([
            'name' => $request['name'],
            'icon' => $request['icon']
        ]);

        return response()->json($committee);
    }

    public function getById(Request $request) {
        return response()->json(Comittee::find($request['id']));
    }

    public function updateById(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'icon' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->messages()
            ]);
        }

        $committee = Comittee::find($request['id']);
        if (!$committee) {
            return response()->json([
                'error' => ['id' => 'Invalid id']
            ]);
        }

        $committee->update([
            'name' => $request['name'],
            'icon' => $request['icon']
        ]);

        return response()->json($committee);
    }

    public function deleteById(Request $request) {
        $committee = Comittee::find($request['id']);
        if (!$committee) {
            return response()->json([
                'error' => ['id' => 'Invalid id']
            ]);
        }

        $committee->delete();
        return response()->json($committee);
    }
}