<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

use App\Models\Message;
use App\Events\NewMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index()
    {
        $contacts = $this->searchContacts();
        return Inertia::render('Chat/Index', [
            'contacts' => $contacts
        ]);
    }

    public function searchContacts()
    {
        $contacts = request()->user()->currentTeam->users->where('id', '!=', auth()->id());
        $keyword = Str::lower(request()->input('keyword'));
        if (isset(request()->keyword)) {
            $contacts = $contacts->filter(function ($contact) use ($keyword) {
                $name = Str::lower($contact->name);
                $email = Str::lower($contact->email);
                return Str::contains($name, $keyword) || Str::contains($email, $keyword);
            });
        }
        $contacts = $contacts->all();
        if (request()->user()->currentTeam->owner->id != auth()->id()) {
            $ownerName = Str::lower(request()->user()->currentTeam->owner->name);
            $ownerEmail = Str::lower(request()->user()->currentTeam->owner->email);

            if (
                (
                    isset(request()->keyword) && (Str::contains($ownerName, $keyword) || Str::contains($ownerEmail, $keyword))
                )
                || !isset(request()->keyword)
            ) {
                array_push($contacts, request()->user()->currentTeam->owner);
            }
        }

        $unreadIds = Message::select(DB::raw('`from` as sender_id, count(`from`) as messages_count, max(created_at) as last_message_at'))
            ->where('to', auth()->id())
            ->whereNull('read_at')
            ->groupBy('from')
            ->get();

        $collection = collect($contacts);

        $contacts = $collection->map(function ($contact) use ($unreadIds) {
            $contactUnread = $unreadIds->where('sender_id', $contact->id)->first();
            $contact->unread = $contactUnread ? $contactUnread->messages_count : 0;
            $contact->last_message_at = $contactUnread ? $contactUnread->last_message_at : '';
            return $contact;
        })->toArray();

        return array_values($contacts);
    }

    public function getMessagesFor($id)
    {
        Message::where('from', $id)->where('to', auth()->id())->update(['read_at' => now()]);

        $messages = Message::where(function ($query) use ($id) {
            $query->where('from', auth()->id())->where('to', $id);
        })->orWhere(function ($query) use ($id) {
            $query->where('from', $id)->where('to', auth()->id());
        })->get();

        return response()->json($messages);
    }

    public function send(Request $request)
    {
        $message = Message::create([
            'from' => auth()->id(),
            'to' => $request->contact_id,
            'text' => $request->text
        ]);

        // broadcast(new NewMessage($message))->toOthers();
        NewMessage::dispatch($message);

        return response()->json($message);
    }

    public function read($id)
    {
        Message::where('id', $id)->update(['read_at' => now()]);
    }
}
