<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $avatarUrl = $this->avatar ? url(Storage::disk('public')->url($this->avatar)) : null;

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'birth_date' => $this->birth_date?->toDateString(),
            'gender' => $this->gender,
            'sex' => $this->gender,
            'institution' => $this->institution,
            'education_level' => $this->education_level,
            'career' => $this->career,
            'grade' => $this->grade,
            'academic_cycle' => $this->academic_cycle,
            'cycle' => $this->academic_cycle,
            'department' => $this->department,
            'province' => $this->province,
            'district' => $this->district,
            'avatar' => $this->avatar,
            'avatar_url' => $avatarUrl,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
