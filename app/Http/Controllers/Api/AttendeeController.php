<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class AttendeeController extends Controller
{
    use CanLoadRelationships;
    use AuthorizesRequests;

    private array $relations = ['user'];

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show', 'update']);
        $this->middleware('throttle:60,1')->only(['store', 'destroy']);
        $this->authorizeResource(Attendee::class, 'attendee');
    }

//    public static function middleware(): array
//    {
//        return [
//            new Middleware('auth:sanctum', except: ['index', 'show', 'update']),
//        ];
//    }

    public function index(Event $event)
    {
        $attendees = $this->loadRelationships(
            $event->attendees()->latest()
        );

        return AttendeeResource::collection($attendees->paginate());
    }

    public function store(Request $request, Event $event)
    {
//        $this->authorize('delete-attendee', $event);

        $attendee = $this->loadRelationships(
            $event->attendees()->create([
                'user_id' => $request->user()->id,
            ])
        );

        return new AttendeeResource($attendee);
    }

    public function show(Event $event, Attendee $attendee)
    {
        return new AttendeeResource(
            $this->loadRelationships($attendee)
        );
    }

    public function destroy(Event $event, Attendee $attendee)
    {
        $attendee->delete();

        return response(status: 204);
    }
}
