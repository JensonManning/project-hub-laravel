<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use App\Mail\MessageNotification;

class MessageController extends Controller
{
    /**
     * Display a listing of the messages for a project.
     */
    public function index(Project $project)
    {
        // Check if user is authorized to view the project
        $user = Auth::user();
        $hasAccess = $project->resources()
            ->where('project_resource.user_id', $user->id)
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'You do not have access to this project.');
        }
        
        $messages = $project->messages()
            ->threadStarters()
            ->with(['sender', 'recipients'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return Inertia::render('Message/Index', [
            'project' => $project,
            'messages' => $messages,
        ]);
    }

    /**
     * Show the form for creating a new message.
     */
    public function create(Project $project)
    {
        // Check if user is authorized to view the project
        $user = Auth::user();
        $hasAccess = $project->resources()
            ->where('project_resource.user_id', $user->id)
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'You do not have access to this project.');
        }
        
        // Get all registered users
        $allUsers = User::all();
        $projectUsers = $project->resources()->with('user')->get()->pluck('user')->unique('id');
        
        return Inertia::render('Message/Create', [
            'project' => $project,
            'projectUsers' => $projectUsers,
            'allUsers' => $allUsers,
        ]);
    }

    /**
     * Store a newly created message in storage.
     */
    public function store(Request $request, Project $project)
    {
        // Check if user is authorized to view the project
        $user = Auth::user();
        $hasAccess = $project->resources()
            ->where('project_resource.user_id', $user->id)
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'You do not have access to this project.');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'recipients' => 'required|array',
            'recipients.*' => 'exists:users,id',
        ]);
        
        $message = new Message([
            'project_id' => $project->id,
            'sender_id' => Auth::id(),
            'title' => $validated['title'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'is_thread_starter' => true,
        ]);
        
        $message->save();
        
        // Attach recipients
        foreach ($validated['recipients'] as $recipientId) {
            $message->recipients()->attach($recipientId, [
                'read' => false,
                'read_at' => null,
            ]);
            
            // Send email notification to recipient
            $recipient = User::find($recipientId);
            Mail::to($recipient->email)->send(new MessageNotification($message, $recipient));
        }
        
        return redirect()->route('projects.messages.show', [$project->id, $message->id])
            ->with('success', 'Message sent successfully.');
    }

    /**
     * Display the specified message and its replies.
     */
    public function show(Project $project, Message $message)
    {
        // Check if user is authorized to view the project
        $user = Auth::user();
        $hasAccess = $project->resources()
            ->where('project_resource.user_id', $user->id)
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'You do not have access to this project.');
        }
        
        // If this is a reply, redirect to the thread starter
        if (!$message->is_thread_starter && $message->parent_id) {
            return redirect()->route('projects.messages.show', [$project->id, $message->parent_id]);
        }
        
        // Mark message as read for the current user if they're a recipient
        if ($message->recipients->contains(Auth::id())) {
            $message->markAsReadBy(Auth::user());
        }
        
        // Load the message with its replies, sender, and recipients
        $message->load([
            'sender', 
            'recipients', 
            'replies' => function ($query) {
                $query->orderBy('created_at', 'asc');
            },
            'replies.sender',
        ]);
        
        // Get all users
        $allUsers = User::all();
        $projectUsers = $project->resources()->with('user')->get()->pluck('user')->unique('id');
        
        return Inertia::render('Message/Show', [
            'project' => $project,
            'message' => $message,
            'projectUsers' => $projectUsers,
            'allUsers' => $allUsers,
        ]);
    }

    /**
     * Store a reply to a message.
     */
    public function reply(Request $request, Project $project, Message $message)
    {
        // Check if user is authorized to view the project
        $user = Auth::user();
        $hasAccess = $project->resources()
            ->where('project_resource.user_id', $user->id)
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'You do not have access to this project.');
        }
        
        $validated = $request->validate([
            'body' => 'required|string',
            'recipients' => 'sometimes|array',
            'recipients.*' => 'exists:users,id',
        ]);
        
        // Get the thread starter message if this is a reply to a reply
        $threadStarter = $message->is_thread_starter ? $message : $message->parent;
        
        $reply = new Message([
            'project_id' => $project->id,
            'sender_id' => Auth::id(),
            'title' => 'Re: ' . $threadStarter->title,
            'subject' => 'Re: ' . $threadStarter->subject,
            'body' => $validated['body'],
            'is_thread_starter' => false,
            'parent_id' => $threadStarter->id,
        ]);
        
        $reply->save();
        
        // If recipients are specified, use them, otherwise use the original recipients
        $recipients = isset($validated['recipients']) 
            ? $validated['recipients'] 
            : $threadStarter->recipients->pluck('id')->toArray();
        
        // Add the original sender if they're not the current user
        if ($threadStarter->sender_id != Auth::id() && !in_array($threadStarter->sender_id, $recipients)) {
            $recipients[] = $threadStarter->sender_id;
        }
        
        // Attach recipients
        foreach ($recipients as $recipientId) {
            // Don't add the current user as a recipient
            if ($recipientId != Auth::id()) {
                $reply->recipients()->attach($recipientId, [
                    'read' => false,
                    'read_at' => null,
                ]);
                
                // Send email notification to recipient
                $recipient = User::find($recipientId);
                Mail::to($recipient->email)->send(new MessageNotification($reply, $recipient));
            }
        }
        
        return redirect()->route('projects.messages.show', [$project->id, $threadStarter->id])
            ->with('success', 'Reply sent successfully.');
    }
    
    /**
     * Store a quick reply to a message (sends to all original recipients).
     */
    public function quickReply(Request $request, Project $project, Message $message)
    {
        // Check if user is authorized to view the project
        $user = Auth::user();
        $hasAccess = $project->resources()
            ->where('project_resource.user_id', $user->id)
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'You do not have access to this project.');
        }
        
        $validated = $request->validate([
            'body' => 'required|string',
        ]);
        
        // Get the thread starter message if this is a reply to a reply
        $threadStarter = $message->is_thread_starter ? $message : $message->parent;
        
        $reply = new Message([
            'project_id' => $project->id,
            'sender_id' => Auth::id(),
            'title' => 'Re: ' . $threadStarter->title,
            'subject' => 'Re: ' . $threadStarter->subject,
            'body' => $validated['body'],
            'is_thread_starter' => false,
            'parent_id' => $threadStarter->id,
        ]);
        
        $reply->save();
        
        // Get all recipients from the thread
        $recipients = $threadStarter->recipients->pluck('id')->toArray();
        
        // Add the original sender if they're not the current user
        if ($threadStarter->sender_id != Auth::id() && !in_array($threadStarter->sender_id, $recipients)) {
            $recipients[] = $threadStarter->sender_id;
        }
        
        // Attach recipients
        foreach ($recipients as $recipientId) {
            // Don't add the current user as a recipient
            if ($recipientId != Auth::id()) {
                $reply->recipients()->attach($recipientId, [
                    'read' => false,
                    'read_at' => null,
                ]);
                
                // Send email notification to recipient
                $recipient = User::find($recipientId);
                Mail::to($recipient->email)->send(new MessageNotification($reply, $recipient));
            }
        }
        
        return redirect()->route('projects.messages.show', [$project->id, $threadStarter->id])
            ->with('success', 'Reply sent to all participants.');
    }
}
