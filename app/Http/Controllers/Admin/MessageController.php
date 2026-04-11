<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;

class MessageController extends Controller
{
    public function index()
    {
        $messages = Message::with('plumber')->latest()->paginate(25);

        return view('admin.messages.index', compact('messages'));
    }

    public function destroy(Message $message)
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('success', 'Message supprimé.');
    }
}
