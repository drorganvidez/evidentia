<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Evidence;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EvidenceController extends Controller
{
    public function getAll() {
        return response()->json(Evidence::paginate());
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'hours' => ['required', 'numeric'],
            'user_id' => ['required', 'numeric'],
            'comittee_id' => ['required', 'numeric'],
            'points_to' => ['required', 'numeric'],
            'status' => ['required', 'numeric'],
            'stamp' => ['required', 'string'],
            'rand' => ['required', 'boolean']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->messages()
            ]);
        }

        $evidence = Evidence::create([
            'id' => $request['id'],
            'title' => $request['title'],
            'description' => $request['description'],
            'hours' => $request['hours'],
            'user_id' => $request['user_id'],
            'comittee_id' => $request['comittee_id'],
            'points_to' => $request['points_to'],
            'status' => $request['status'],
            'stamp' => $request['stamp'],
            'rand' => $request['rand']
        ]);

        return response()->json($evidence);
    }

    public function getById(Request $request) {
        return response()->json(Evidence::find($request['id']));
    }

    public function updateById(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'hours' => ['required', 'numeric'],
            'user_id' => ['required', 'numeric'],
            'comittee_id' => ['required', 'numeric'],
            'points_to' => ['required', 'numeric'],
            'status' => ['required', 'numeric'],
            'stamp' => ['required', 'string'],
            'rand' => ['required', 'boolean']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->messages()
            ]);
        }

        $evidence = Evidence::find($request['id']);
        if (!$evidence) {
            return response()->json([
                'error' => ['id' => 'Invalid id']
            ]);
        }

        $evidence->update([
            'title' => $request['title'],
            'description' => $request['description'],
            'hours' => $request['hours'],
            'user_id' => $request['user_id'],
            'comittee_id' => $request['comittee_id'],
            'points_to' => $request['points_to'],
            'status' => $request['status'],
            'stamp' => $request['stamp'],
            'rand' => $request['rand']
        ]);

        return response()->json($evidence);
    }

    public function deleteById(Request $request) {
        $evidence = Evidence::find($request['id']);
        if (!$evidence) {
            return response()->json([
                'error' => ['id' => 'Invalid id']
            ]);
        }

        $evidence->delete();
        return response()->json($evidence);
    }
}
