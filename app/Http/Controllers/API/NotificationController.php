<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use App\Models\Member;
use App\Models\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 20;
        $page = $request->page ?? 1;

        $query = Notification::where('user_id', auth()->id())
            ->orWhere('user_id', null) // Broadcast notifications
            ->orderBy('created_at', 'desc');

        // Filter by read status
        if ($request->has('read')) {
            $query->where('read', $request->boolean('read'));
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $notifications = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    public function unread()
    {
        $notifications = Notification::where(function($query) {
                $query->where('user_id', auth()->id())
                      ->orWhere('user_id', null);
            })
            ->where('read', false)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    public function count()
    {
        $count = Notification::where(function($query) {
                $query->where('user_id', auth()->id())
                      ->orWhere('user_id', null);
            })
            ->where('read', false)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count,
            ],
        ]);
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:notifications,id',
        ]);

        $notification = Notification::find($request->id);

        // Check if user owns this notification or it's a broadcast
        if ($notification->user_id && $notification->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $notification->update(['read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    public function destroy($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        // Check if user owns this notification or it's a broadcast
        if ($notification->user_id && $notification->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }

    public function clearAll()
    {
        Notification::where('user_id', auth()->id())->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications cleared',
        ]);
    }

    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_type' => 'required|in:member,event_attendees,group,all_members,users',
            'recipient_ids' => 'nullable|array',
            'recipient_ids.*' => 'integer',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:email,sms,push,both',
            'send_immediately' => 'boolean',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $churchId = auth()->user()->church_id;
        $recipients = $this->getRecipients($request->recipient_type, $request->recipient_ids, $churchId);

        // Create notifications in database
        foreach ($recipients as $recipient) {
            Notification::create([
                'user_id' => $recipient['user_id'] ?? null,
                'title' => $request->subject,
                'message' => $request->message,
                'type' => $request->type,
                'data' => [
                    'recipient_type' => $request->recipient_type,
                    'recipient_id' => $recipient['id'] ?? null,
                    'sent_by' => auth()->id(),
                ],
                'read' => false,
                'scheduled_at' => $request->scheduled_at,
            ]);
        }

        // Send immediately if requested
        if ($request->boolean('send_immediately')) {
            $this->sendNotifications($recipients, $request->subject, $request->message, $request->type);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifications queued for ' . count($recipients) . ' recipients',
            'data' => [
                'recipient_count' => count($recipients),
                'scheduled' => !$request->boolean('send_immediately'),
            ],
        ]);
    }

    public function broadcast(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:email,sms,push,both',
            'target' => 'required|in:all_members,all_users,active_members,event_attendees',
            'event_id' => 'nullable|exists:events,id',
            'send_immediately' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $churchId = auth()->user()->church_id;
        $recipients = $this->getBroadcastRecipients($request->target, $request->event_id, $churchId);

        // Create broadcast notification (user_id = null for broadcast)
        Notification::create([
            'user_id' => null,
            'title' => $request->subject,
            'message' => $request->message,
            'type' => 'broadcast',
            'data' => [
                'target' => $request->target,
                'event_id' => $request->event_id,
                'sent_by' => auth()->id(),
                'recipient_count' => count($recipients),
            ],
            'read' => false,
            'scheduled_at' => now(),
        ]);

        // Also create individual notifications for tracking
        foreach ($recipients as $recipient) {
            Notification::create([
                'user_id' => $recipient['user_id'] ?? null,
                'title' => $request->subject,
                'message' => $request->message,
                'type' => $request->type,
                'data' => [
                    'broadcast' => true,
                    'recipient_id' => $recipient['id'] ?? null,
                    'sent_by' => auth()->id(),
                ],
                'read' => false,
                'scheduled_at' => now(),
            ]);
        }

        // Send immediately if requested
        if ($request->boolean('send_immediately')) {
            $this->sendNotifications($recipients, $request->subject, $request->message, $request->type);
        }

        return response()->json([
            'success' => true,
            'message' => 'Broadcast sent to ' . count($recipients) . ' recipients',
            'data' => [
                'recipient_count' => count($recipients),
                'broadcast' => true,
            ],
        ]);
    }

    private function getRecipients($type, $ids, $churchId)
    {
        $recipients = [];

        switch ($type) {
            case 'member':
                $members = Member::where('church_id', $churchId)
                    ->whereIn('id', $ids ?? [])
                    ->get();

                foreach ($members as $member) {
                    $recipients[] = [
                        'id' => $member->id,
                        'name' => $member->first_name . ' ' . $member->last_name,
                        'email' => $member->email,
                        'phone' => $member->phone,
                        'type' => 'member',
                    ];
                }
                break;

            case 'event_attendees':
                if (empty($ids)) {
                    break;
                }

                // Get all attendees for the specified events
                $events = Event::where('church_id', $churchId)
                    ->whereIn('id', $ids)
                    ->with('attendances')
                    ->get();

                foreach ($events as $event) {
                    // For simplicity, we'll just get the event attendees count
                    // In a real implementation, you might want to get individual attendees
                    $recipients[] = [
                        'id' => $event->id,
                        'name' => 'Attendees of ' . $event->title,
                        'type' => 'event_group',
                        'attendee_count' => $event->attendances->sum('total'),
                    ];
                }
                break;

            case 'group':
                // Get members by group (you would need a groups table)
                // For now, returning empty
                break;

            case 'all_members':
                $members = Member::where('church_id', $churchId)->get();

                foreach ($members as $member) {
                    $recipients[] = [
                        'id' => $member->id,
                        'name' => $member->first_name . ' ' . $member->last_name,
                        'email' => $member->email,
                        'phone' => $member->phone,
                        'type' => 'member',
                    ];
                }
                break;

            case 'users':
                $users = User::where('church_id', $churchId)
                    ->whereIn('id', $ids ?? [])
                    ->get();

                foreach ($users as $user) {
                    $recipients[] = [
                        'id' => $user->id,
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'type' => 'user',
                    ];
                }
                break;
        }

        return $recipients;
    }

    private function getBroadcastRecipients($target, $eventId, $churchId)
    {
        $recipients = [];

        switch ($target) {
            case 'all_members':
                $members = Member::where('church_id', $churchId)->get();

                foreach ($members as $member) {
                    $recipients[] = [
                        'id' => $member->id,
                        'name' => $member->first_name . ' ' . $member->last_name,
                        'email' => $member->email,
                        'phone' => $member->phone,
                        'type' => 'member',
                    ];
                }
                break;

            case 'all_users':
                $users = User::where('church_id', $churchId)->get();

                foreach ($users as $user) {
                    $recipients[] = [
                        'id' => $user->id,
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'type' => 'user',
                    ];
                }
                break;

            case 'active_members':
                $members = Member::where('church_id', $churchId)
                    ->where('membership_status', 'active')
                    ->get();

                foreach ($members as $member) {
                    $recipients[] = [
                        'id' => $member->id,
                        'name' => $member->first_name . ' ' . $member->last_name,
                        'email' => $member->email,
                        'phone' => $member->phone,
                        'type' => 'member',
                    ];
                }
                break;

            case 'event_attendees':
                if (!$eventId) {
                    break;
                }

                $event = Event::where('church_id', $churchId)
                    ->where('id', $eventId)
                    ->first();

                if ($event) {
                    // Get all attendance records for this event
                    // In a real implementation, you might want to get individual member contacts
                    $recipients[] = [
                        'id' => $event->id,
                        'name' => 'Attendees of ' . $event->title,
                        'type' => 'event_group',
                        'attendee_count' => $event->attendances->sum('total') ?? 0,
                    ];
                }
                break;
        }

        return $recipients;
    }

    private function sendNotifications($recipients, $subject, $message, $type)
    {
        foreach ($recipients as $recipient) {
            if ($type === 'email' || $type === 'both') {
                $this->sendEmail($recipient, $subject, $message);
            }

            if ($type === 'sms' || $type === 'both') {
                $this->sendSMS($recipient, $message);
            }

            if ($type === 'push') {
                $this->sendPush($recipient, $subject, $message);
            }
        }
    }

    private function sendEmail($recipient, $subject, $message)
    {
        if (empty($recipient['email'])) {
            return;
        }

        try {
            Mail::send([], [], function ($mail) use ($recipient, $subject, $message) {
                $mail->to($recipient['email'])
                    ->subject($subject)
                    ->html($message);
            });
        } catch (\Exception $e) {
            \Log::error('Failed to send email: ' . $e->getMessage());
        }
    }

    private function sendSMS($recipient, $message)
    {
        if (empty($recipient['phone'])) {
            return;
        }

        // Implement SMS sending logic here
        // This would depend on your SMS provider (Twilio, Nexmo, etc.)

        \Log::info('SMS would be sent to ' . $recipient['phone'] . ': ' . substr($message, 0, 50) . '...');
    }

    private function sendPush($recipient, $title, $message)
    {
        // Implement push notification logic here
        // This would depend on your push notification service (Firebase, OneSignal, etc.)

        \Log::info('Push notification would be sent: ' . $title);
    }
}
