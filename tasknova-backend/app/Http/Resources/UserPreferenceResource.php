<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPreferenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'theme' => $this->theme,
            'language' => $this->language,
            'notifications_enabled' => $this->notifications_enabled,
            'timezone' => $this->timezone,
            'week_start' => $this->week_start,
            'default_view' => $this->default_view,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
