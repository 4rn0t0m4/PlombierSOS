<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatbotConversation;

class ChatbotController extends Controller
{
    public function index()
    {
        $conversations = ChatbotConversation::orderByDesc('updated_at')
            ->paginate(20);

        return view('admin.chatbot.index', compact('conversations'));
    }

    public function show(ChatbotConversation $conversation)
    {
        return view('admin.chatbot.show', compact('conversation'));
    }

    public function destroy(ChatbotConversation $conversation)
    {
        $conversation->delete();

        return redirect()->route('admin.chatbot.index')
            ->with('success', 'Conversation supprimée.');
    }
}
