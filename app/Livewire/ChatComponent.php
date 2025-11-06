<?php

namespace App\Livewire;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\MessageReaction;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Storage;

class ChatComponent extends Component
{
    use WithFileUploads, WithPagination;

    // State
    public $selectedConversationId = null;
    public $search = '';
    public $showNewChatModal = false;
    public $messageSearch = '';

    // Message input
    public $body = '';
    public $attachments = [];
    public $replyingTo = null;
    public $editingMessageId = null;

    protected $rules = [
        'body' => 'required_without:attachments|string|max:5000',
        'attachments.*' => 'file|max:10240',
    ];

    public function mount($conversation = null)
    {
        // If conversation ID provided in URL, select it
        if ($conversation) {
            $conv = Conversation::find($conversation);
            if ($conv && $conv->hasUser(auth()->id())) {
                $this->selectedConversationId = $conv->id;
                $this->markAsRead();
                $this->markUnreadMessagesAsRead();
                return;
            }
        }

        // Otherwise, auto-select first conversation
        $firstConversation = $this->getConversationsProperty()->first();
        if ($firstConversation) {
            $this->selectedConversationId = $firstConversation->id;
            $this->markAsRead();
            $this->markUnreadMessagesAsRead();
        }
    }

    public function getConversationsProperty()
    {
        $userId = auth()->id();
        
        return auth()->user()
            ->conversations()
            ->with(['userOne', 'userTwo', 'latestMessage.user'])
            ->when($this->search, function ($query) use ($userId) {
                $searchTerm = '%' . $this->search . '%';
                $query->where(function($q) use ($userId, $searchTerm) {
                    // Search in the OTHER user's name (not the current user)
                    $q->where(function($subQ) use ($userId, $searchTerm) {
                        $subQ->where('user_one_id', '!=', $userId)
                             ->whereHas('userOne', function($userQ) use ($searchTerm) {
                                 $userQ->where('name', 'like', $searchTerm)
                                       ->orWhere('email', 'like', $searchTerm);
                             });
                    })->orWhere(function($subQ) use ($userId, $searchTerm) {
                        $subQ->where('user_two_id', '!=', $userId)
                             ->whereHas('userTwo', function($userQ) use ($searchTerm) {
                                 $userQ->where('name', 'like', $searchTerm)
                                       ->orWhere('email', 'like', $searchTerm);
                             });
                    });
                });
            })
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function getConversationUsersProperty()
    {
        // Map conversation ID to other user ID
        return $this->conversations->mapWithKeys(function ($conversation) {
            return [$conversation->id => $conversation->getOtherUser(auth()->id())->id];
        })->toArray();
    }

    public function getMessagesProperty()
    {
        if (!$this->selectedConversationId) {
            return collect([]);
        }

        $query = Message::where('conversation_id', $this->selectedConversationId)
            ->with(['user', 'parent.user', 'attachments', 'reactions.user'])
            ->where('is_deleted', false);

        if ($this->messageSearch) {
            $query->where('body', 'like', '%' . $this->messageSearch . '%');
        }

        return $query->orderBy('created_at', 'asc')->limit(50)->get();
    }

    public function selectConversation($conversationId)
    {
        $this->selectedConversationId = $conversationId;
        $this->messageSearch = '';
        $this->replyingTo = null;
        $this->resetPage();
        $this->markAsRead();
        $this->dispatch('conversationSelected', conversationId: $conversationId);
      $this->markUnreadMessagesAsRead();


    }

    public function startNewChat($userId)
    {
        $conversation = Conversation::findOrCreateBetween(auth()->id(), $userId);
        $this->selectedConversationId = $conversation->id;
        $this->showNewChatModal = false;
        $this->markAsRead();
        $this->dispatch('conversationSelected', conversationId: $conversation->id);
    }

    public function sendMessage()
    {
        // If editing, update instead
        if ($this->editingMessageId) {
            $this->updateMessage();
            return;
        }

        $this->validate();

        if (!$this->selectedConversationId) {
            return;
        }

        $conversation = Conversation::find($this->selectedConversationId);

        if (!$conversation || !$conversation->hasUser(auth()->id())) {
            return;
        }

        // Check if either user has blocked the conversation
        $isBlockedByMe = \DB::table('conversation_user')
            ->where('conversation_id', $this->selectedConversationId)
            ->where('user_id', auth()->id())
            ->value('is_blocked');

        $otherUser = $conversation->getOtherUser(auth()->id());
        $isBlockedByOther = \DB::table('conversation_user')
            ->where('conversation_id', $this->selectedConversationId)
            ->where('user_id', $otherUser->id)
            ->value('is_blocked');

        if ($isBlockedByMe || $isBlockedByOther) {
            session()->flash('error', 'Cannot send message. This conversation is blocked.');
            return;
        }

        // Create message
        $message = Message::create([
            'conversation_id' => $this->selectedConversationId,
            'user_id' => auth()->id(),
            'parent_id' => $this->replyingTo,
            'body' => $this->body,
        ]);

        // Handle attachments
        if (!empty($this->attachments)) {
            foreach ($this->attachments as $attachment) {
                $path = $attachment->store('chat-attachments', 'public');

                MessageAttachment::create([
                    'message_id' => $message->id,
                    'file_name' => $attachment->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $this->getFileType($attachment->getMimeType()),
                    'mime_type' => $attachment->getMimeType(),
                    'file_size' => $attachment->getSize(),
                ]);
            }
        }

        // Update conversation
        $conversation->update(['last_message_at' => now()]);

        // No broadcast needed - using whisper instead

        // Send notification
        $otherUser = $conversation->getOtherUser(auth()->id());
        $otherUser->notify(new \App\Notifications\NewMessageNotification($message));

        // Reset form
        $this->reset(['body', 'attachments', 'replyingTo']);

        // Dispatch to Alpine to trigger whisper
        $this->dispatch('message-sent', messageId: $message->id);
    }

    public function toggleReaction($messageId, $emoji)
    {
        MessageReaction::toggle($messageId, auth()->id(), $emoji);
        // No broadcast needed - using whisper instead
    }

    public function setReplyingTo($messageId)
    {
        $this->replyingTo = $messageId;
    }

    public function cancelReply()
    {
        $this->replyingTo = null;
    }

    public function editMessage($messageId)
    {
        $message = Message::find($messageId);
        
        if ($message && $message->user_id === auth()->id()) {
            $this->editingMessageId = $messageId;
            $this->body = $message->body;
        }
    }

    public function cancelEdit()
    {
        $this->editingMessageId = null;
        $this->body = '';
    }

    public function updateMessage()
    {
        if (!$this->editingMessageId) {
            return;
        }

        $message = Message::find($this->editingMessageId);
        
        if ($message && $message->user_id === auth()->id()) {
            $message->update([
                'body' => $this->body,
                'edited_at' => now()
            ]);

            broadcast(new \App\Events\MessageUpdated($message))->toOthers();
            
            $this->cancelEdit();
        }
    }

    public function deleteMessage($messageId)
    {
        $message = Message::find($messageId);

        if ($message && $message->user_id === auth()->id()) {
            $message->softDeleteMessage();
            broadcast(new \App\Events\MessageDeleted($message))->toOthers();
        }
    }

    public function removeAttachment($index)
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    public function deleteConversation()
    {
        if (!$this->selectedConversationId) {
            return;
        }

        $conversation = Conversation::find($this->selectedConversationId);
        
        if ($conversation && $conversation->hasUser(auth()->id())) {
            // Delete all messages in the conversation
            Message::where('conversation_id', $this->selectedConversationId)->delete();
            
            // Delete the conversation
            $conversation->delete();
            
            // Reset selection
            $this->selectedConversationId = null;
            $this->body = '';
            $this->replyingTo = null;
            $this->editingMessageId = null;
            
            session()->flash('message', 'Conversation deleted successfully.');
        }
    }

    public function blockUser()
    {
        if (!$this->selectedConversationId) {
            return;
        }

        $conversation = Conversation::find($this->selectedConversationId);
        
        if ($conversation && $conversation->hasUser(auth()->id())) {
            // Update is_blocked field in conversation_user pivot table
            \DB::table('conversation_user')
                ->where('conversation_id', $this->selectedConversationId)
                ->where('user_id', auth()->id())
                ->update(['is_blocked' => true]);
            
            session()->flash('message', 'User blocked successfully.');
        }
    }

    public function unblockUser()
    {
        if (!$this->selectedConversationId) {
            return;
        }

        $conversation = Conversation::find($this->selectedConversationId);
        
        if ($conversation && $conversation->hasUser(auth()->id())) {
            // Update is_blocked field in conversation_user pivot table
            \DB::table('conversation_user')
                ->where('conversation_id', $this->selectedConversationId)
                ->where('user_id', auth()->id())
                ->update(['is_blocked' => false]);
            
            session()->flash('message', 'User unblocked successfully.');
        }
    }

    public function refreshMessages()
    {
        // Mark unread messages as read when refreshing
        $this->markUnreadMessagesAsRead();
    }

    public function refreshConversations()
    {
        // Just trigger a re-render
    }

    public function isUserBlocked()
    {
        if (!$this->selectedConversationId) {
            return false;
        }

        // Check if current user has blocked this conversation
        $result = \DB::table('conversation_user')
            ->where('conversation_id', $this->selectedConversationId)
            ->where('user_id', auth()->id())
            ->value('is_blocked');

        return (bool) $result;
    }

    private function markUnreadMessagesAsRead()
    {
        if (!$this->selectedConversationId) {
            return;
        }

        // Mark all unread messages in this conversation as read
        // Use DB::table to prevent updated_at from changing
        $updated = \DB::table('messages')
            ->where('conversation_id', $this->selectedConversationId)
            ->where('user_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // If messages were marked as read, whisper to sender and refresh conversations
        if ($updated > 0) {
            $this->dispatch('messages-marked-read');
            // Force refresh conversations list to update unread count
            $this->dispatch('$refresh');
        }
    }

    public function getOtherUserProperty()
    {
        if (!$this->selectedConversationId) {
            return null;
        }

        $conversation = Conversation::find($this->selectedConversationId);
        if (!$conversation) {
            return null;
        }

        return $conversation->getOtherUser(auth()->id());
    }

    private function markAsRead()
    {
        if (!$this->selectedConversationId) {
            return;
        }

        $conversation = Conversation::find($this->selectedConversationId);
        if ($conversation) {
            $conversation->markAsRead(auth()->id());
        }
    }

    private function getFileType($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        } elseif (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        } else {
            return 'document';
        }
    }

    public function getAvailableUsersProperty()
    {
        return User::where('id', '!=', auth()->id())
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->limit(20)
            ->get();
    }

    public function getSelectedConversationProperty()
    {
        if (!$this->selectedConversationId) {
            return null;
        }

        return Conversation::with(['userOne', 'userTwo'])->find($this->selectedConversationId);
    }

    public function render()
    {
        return view('livewire.chat-component')->layout('layouts.app');
    }
}
