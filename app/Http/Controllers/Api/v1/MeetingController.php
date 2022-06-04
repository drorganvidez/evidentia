<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Meeting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MeetingController extends Controller
{
    public function getAll() {
        return response()->json(Meeting::paginate());
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string'],
            'datetime' => ['required', 'string'],
            'place' => ['required', 'string'],
            'type' => ['required', 'numeric'],
            'modality' => ['required', 'numeric'],
            'hours' => ['required', 'numeric']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->messages()
            ]);
        }

        $meeting = Meeting::create([
            'title' => $request['title'],
            'datetime' => $request['datetime'],
            'place' => $request['place'],
            'type' => $request['type'],
            'modality' => $request['modality'],
            'hours' => $request['hours']
        ]);

        return response()->json($meeting);
    }

    public function getById(Request $request) {
        return response()->json(Meeting::find($request['id']));
    }

    public function updateById(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string'],
            'datetime' => ['required', 'string'],
            'place' => ['required', 'string'],
            'type' => ['required', 'numeric'],
            'modality' => ['required', 'numeric'],
            'hours' => ['required', 'numeric']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->messages()
            ]);
        }

        $meeting = Meeting::find($request['id']);
        if (!$meeting) {
            return response()->json([
                'error' => ['id' => 'Invalid id']
            ]);
        }

        $meeting->update([
            'title' => $request['title'],
            'datetime' => $request['datetime'],
            'place' => $request['place'],
            'type' => $request['type'],
            'modality' => $request['modality'],
            'hours' => $request['hours']
        ]);

        return response()->json($meeting);
    }

    public function deleteById(Request $request) {
        $meeting = Meeting::find($request['id']);
        if (!$meeting) {
            return response()->json([
                'error' => ['id' => 'Invalid id']
            ]);
        }

        $meeting->delete();
        return response()->json($meeting);
    }
}