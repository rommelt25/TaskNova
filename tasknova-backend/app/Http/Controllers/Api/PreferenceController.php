<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Preference\UpdateUserPreferenceRequest;
use App\Http\Resources\UserPreferenceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreferenceController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $preferences = $request->user()->preference()->firstOrCreate()->refresh();

        return (new UserPreferenceResource($preferences))
            ->response()
            ->setStatusCode(200);
    }

    public function update(UpdateUserPreferenceRequest $request): JsonResponse
    {
        $preferences = $request->user()->preference()->firstOrCreate();
        $preferences->update($request->validated());

        return (new UserPreferenceResource($preferences->refresh()))->response()->setStatusCode(200);
    }
}
