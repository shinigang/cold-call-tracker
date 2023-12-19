<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Company;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
            'commentable_id' => 'required|integer',
            'commentable_type' => 'required|string',
        ]);
        $modelName = Str::ucfirst($validated['commentable_type']);
        $comment = Comment::create([
            'user_id' => auth()->id(),
            'body' => $validated['comment'],
            'commentable_id' => $validated['commentable_id'],
            'commentable_type' => 'App\\Models\\' . $modelName,
        ]);

        $comment = Comment::with('user')->whereId($comment->id)->first();

        if ($request->wantsJson() && $modelName == 'Company') {
            $companyRelationships = [
                'contactPersons',
                'contactNumbers',
                'assignedCaller',
                'assignedConsultant',
                // 'calendarEvents.user',
                'calls.user',
                'comments.user',
                'actionLogs.user',
                'assignments.user',
            ];
            return [
                'company' => Company::with($companyRelationships)->whereId($validated['commentable_id'])->first(),
                'comment' => $comment
            ];
        } else {
            return $comment;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        //
    }
}
