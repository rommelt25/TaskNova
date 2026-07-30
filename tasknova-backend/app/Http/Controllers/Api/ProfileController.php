<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->profile()->firstOrCreate();

        return (new ProfileResource($profile->load('user')))->response()->setStatusCode(200);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->profile()->firstOrCreate();
        $attributes = $request->safe()->except('avatar');

        if ($request->hasFile('avatar')) {
            if ($profile->avatar) {
                Storage::disk('public')->delete($profile->avatar);
            }

            $attributes['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $profile->fill($attributes)->save();

        return (new ProfileResource($profile->load('user')))->response()->setStatusCode(200);
    }
}
