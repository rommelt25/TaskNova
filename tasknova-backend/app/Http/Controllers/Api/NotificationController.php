<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);

        return NotificationResource::collection(
            $request->user()->appNotifications()
                ->orderByRaw('read_at IS NULL DESC')
                ->latest()
                ->paginate($perPage)
                ->withQueryString()
        );
    }

    public function markAsRead(Request $request, int $notification): NotificationResource
    {
        $model = $this->notificationFor($request, $notification);
        $model->update(['read_at' => $model->read_at ?? now()]);

        return new NotificationResource($model);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $updated = $request->user()->appNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['data' => ['updated' => $updated]]);
    }

    public function destroy(Request $request, int $notification): JsonResponse
    {
        $this->notificationFor($request, $notification)->delete();

        return response()->json(null, 204);
    }

    private function notificationFor(Request $request, int $notification): Notification
    {
        return $request->user()->appNotifications()->findOrFail($notification);
    }
}
