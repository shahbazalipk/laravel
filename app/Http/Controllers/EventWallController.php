<?php

namespace App\Http\Controllers;

use App\Models\EventWallPost;
use App\Models\EventWallComment;
use App\Models\EventWallLike;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class EventWallController extends Controller
{
    private function getRegistration()
    {
        $attendeeId = Session::get('attendee_id');
        
        if (!$attendeeId) {
            return redirect()->route('attendee.login');
        }

        $registration = Registration::with(['registrationCategory', 'event'])->find($attendeeId);

        if (!$registration) {
            Session::forget('attendee_id');
            return redirect()->route('attendee.login');
        }

        return $registration;
    }

    public function index(Request $request)
    {
        $registration = $this->getRegistration();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $query = EventWallPost::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->active()
            ->with(['registration.registrationCategory', 'comments.registration', 'likes']);

        // Filter by hashtag
        if ($request->filled('hashtag')) {
            $query->whereJsonContains('hashtags', $request->hashtag);
        }

        // Filter by post type
        if ($request->filled('type')) {
            $query->where('post_type', $request->type);
        }

        $posts = $query->recent()->paginate(10);

        // Get trending hashtags
        $trendingHashtags = EventWallPost::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->active()
            ->whereNotNull('hashtags')
            ->get()
            ->pluck('hashtags')
            ->flatten()
            ->countBy()
            ->sortDesc()
            ->take(10);

        return view('attendee.event-wall', compact('registration', 'posts', 'trendingHashtags'));
    }

    public function store(Request $request)
    {
        $registration = $this->getRegistration();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $validated = $request->validate([
            'content' => 'nullable|string|max:5000',
            'post_type' => 'required|in:text,image,link',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'link_url' => 'nullable|url|max:500',
        ]);

        // Extract hashtags from content
        $hashtags = [];
        if ($request->content) {
            preg_match_all('/#(\w+)/', $request->content, $matches);
            $hashtags = $matches[1] ?? [];
        }

        // Extract mentions from content
        $mentions = [];
        if ($request->content) {
            preg_match_all('/@(\w+)/', $request->content, $matches);
            $mentions = $matches[1] ?? [];
        }

        $postData = [
            'event_id' => $eventId,
            'org_id' => $orgId,
            'registration_id' => $registration->id,
            'content' => $request->content,
            'post_type' => $request->post_type,
            'hashtags' => $hashtags,
            'mentions' => $mentions,
        ];

        // Handle image uploads
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('event-wall', $filename, 'public');
                $imagePaths[] = $path;
            }
            $postData['images'] = $imagePaths;
            $postData['post_type'] = 'image'; // Ensure post type is image when images are uploaded
        }

        // Handle link preview (basic implementation)
        if ($request->post_type === 'link' && $request->link_url) {
            $postData['link_url'] = $request->link_url;
            $postData['link_title'] = $this->extractTitleFromUrl($request->link_url);
        }

        $post = EventWallPost::create($postData);

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully',
            'post' => $post->load('registration.registrationCategory'),
            'debug' => [
                'has_images' => $request->hasFile('images'),
                'images_count' => $request->hasFile('images') ? count($request->file('images')) : 0,
                'stored_images' => $postData['images'] ?? []
            ]
        ]);
    }

    public function destroy(EventWallPost $post)
    {
        $registration = $this->getRegistration();

        // Only allow deletion of own posts
        if ($post->registration_id !== $registration->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete images
        if ($post->images) {
            foreach ($post->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $post->delete();

        return response()->json(['success' => true, 'message' => 'Post deleted successfully']);
    }

    public function toggleLike(Request $request, EventWallPost $post)
    {
        $registration = $this->getRegistration();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $like = EventWallLike::where('registration_id', $registration->id)
            ->where('likeable_type', EventWallPost::class)
            ->where('likeable_id', $post->id)
            ->first();

        if ($like) {
            $like->delete();
            $post->decrement('likes_count');
            $liked = false;
        } else {
            EventWallLike::create([
                'event_id' => $eventId,
                'org_id' => $orgId,
                'registration_id' => $registration->id,
                'likeable_type' => EventWallPost::class,
                'likeable_id' => $post->id,
                'reaction_type' => $request->input('reaction_type', 'like'),
            ]);
            $post->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $post->fresh()->likes_count
        ]);
    }

    public function storeComment(Request $request, EventWallPost $post)
    {
        $registration = $this->getRegistration();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:event_wall_comments,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $commentData = [
            'event_id' => $eventId,
            'org_id' => $orgId,
            'event_wall_post_id' => $post->id,
            'registration_id' => $registration->id,
            'content' => $request->content,
            'parent_id' => $request->parent_id,
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $filename = time() . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $path = $request->file('image')->storeAs('event-wall/comments', $filename, 'public');
            $commentData['image'] = $path;
        }

        $comment = EventWallComment::create($commentData);
        $post->increment('comments_count');

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully',
            'comment' => $comment->load('registration.registrationCategory')
        ]);
    }

    public function destroyComment(EventWallComment $comment)
    {
        $registration = $this->getRegistration();

        // Only allow deletion of own comments
        if ($comment->registration_id !== $registration->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete image if exists
        if ($comment->image) {
            Storage::disk('public')->delete($comment->image);
        }

        $comment->post->decrement('comments_count');
        $comment->delete();

        return response()->json(['success' => true, 'message' => 'Comment deleted successfully']);
    }

    public function toggleCommentLike(EventWallComment $comment)
    {
        $registration = $this->getRegistration();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $like = EventWallLike::where('registration_id', $registration->id)
            ->where('likeable_type', EventWallComment::class)
            ->where('likeable_id', $comment->id)
            ->first();

        if ($like) {
            $like->delete();
            $comment->decrement('likes_count');
            $liked = false;
        } else {
            EventWallLike::create([
                'event_id' => $eventId,
                'org_id' => $orgId,
                'registration_id' => $registration->id,
                'likeable_type' => EventWallComment::class,
                'likeable_id' => $comment->id,
                'reaction_type' => 'like',
            ]);
            $comment->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $comment->fresh()->likes_count
        ]);
    }

    private function extractTitleFromUrl($url)
    {
        // Basic title extraction - in production, use a proper URL metadata fetcher
        $parsedUrl = parse_url($url);
        return $parsedUrl['host'] ?? 'Link';
    }
}
