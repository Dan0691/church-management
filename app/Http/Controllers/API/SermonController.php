<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sermon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SermonController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 20;
        $churchId = auth()->user()->church_id;

        $query = Sermon::where('church_id', $churchId)
            ->orderBy('sermon_date', 'desc');

        // Apply filters
        if ($request->filled('series')) {
            $query->where('series', $request->series);
        }

        if ($request->filled('speaker')) {
            $query->where('speaker', $request->speaker);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('sermon_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('sermon_date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('speaker', 'like', "%{$search}%")
                  ->orWhere('scripture_reference', 'like', "%{$search}%");
            });
        }

        $sermons = $query->paginate($perPage);

        // Get unique series and speakers for filters
        $series = Sermon::where('church_id', $churchId)
            ->whereNotNull('series')
            ->distinct()
            ->pluck('series');

        $speakers = Sermon::where('church_id', $churchId)
            ->distinct()
            ->pluck('speaker');

        // Statistics
        $stats = [
            'total' => Sermon::where('church_id', $churchId)->count(),
            'total_views' => Sermon::where('church_id', $churchId)->sum('views'),
            'total_downloads' => Sermon::where('church_id', $churchId)->sum('downloads'),
            'by_series' => Sermon::where('church_id', $churchId)
                ->whereNotNull('series')
                ->groupBy('series')
                ->selectRaw('series, count(*) as count')
                ->get()
                ->pluck('count', 'series'),
        ];

        return response()->json([
            'success' => true,
            'data' => $sermons,
            'filters' => [
                'series' => $series,
                'speakers' => $speakers,
            ],
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'speaker' => 'required|string|max:255',
            'sermon_date' => 'required|date',
            'scripture_reference' => 'nullable|string',
            'series' => 'nullable|string',
            'duration' => 'nullable|integer|min:1',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg|max:102400', // 100MB max
            'video_file' => 'nullable|file|mimes:mp4,avi,mov|max:512000', // 500MB max
            'slides_file' => 'nullable|file|mimes:pdf,ppt,pptx|max:51200', // 50MB max
            'notes_file' => 'nullable|file|mimes:pdf,doc,docx,txt|max:51200', // 50MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $sermonData = [
            'church_id' => auth()->user()->church_id,
            'title' => $request->title,
            'description' => $request->description,
            'speaker' => $request->speaker,
            'sermon_date' => $request->sermon_date,
            'scripture_reference' => $request->scripture_reference,
            'series' => $request->series,
            'duration' => $request->duration,
            'created_by' => auth()->id(),
        ];

        // Handle file uploads
        if ($request->hasFile('audio_file')) {
            $path = $request->file('audio_file')->store('sermons/audio', 'public');
            $sermonData['audio_url'] = Storage::url($path);
            $sermonData['audio_file_name'] = $request->file('audio_file')->getClientOriginalName();
            $sermonData['audio_file_size'] = $request->file('audio_file')->getSize();
        }

        if ($request->hasFile('video_file')) {
            $path = $request->file('video_file')->store('sermons/video', 'public');
            $sermonData['video_url'] = Storage::url($path);
            $sermonData['video_file_name'] = $request->file('video_file')->getClientOriginalName();
            $sermonData['video_file_size'] = $request->file('video_file')->getSize();
        }

        if ($request->hasFile('slides_file')) {
            $path = $request->file('slides_file')->store('sermons/slides', 'public');
            $sermonData['slides_url'] = Storage::url($path);
            $sermonData['slides_file_name'] = $request->file('slides_file')->getClientOriginalName();
            $sermonData['slides_file_size'] = $request->file('slides_file')->getSize();
        }

        if ($request->hasFile('notes_file')) {
            $path = $request->file('notes_file')->store('sermons/notes', 'public');
            $sermonData['notes_url'] = Storage::url($path);
            $sermonData['notes_file_name'] = $request->file('notes_file')->getClientOriginalName();
            $sermonData['notes_file_size'] = $request->file('notes_file')->getSize();
        }

        $sermon = Sermon::create($sermonData);

        return response()->json([
            'success' => true,
            'message' => 'Sermon added successfully',
            'data' => $sermon,
        ], 201);
    }

    public function show($id)
    {
        $sermon = Sermon::findOrFail($id);

        if ($sermon->church_id !== auth()->user()->church_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $sermon,
        ]);
    }

    public function update(Request $request, $id)
    {
        $sermon = Sermon::findOrFail($id);

        if ($sermon->church_id !== auth()->user()->church_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'speaker' => 'required|string|max:255',
            'sermon_date' => 'required|date',
            'scripture_reference' => 'nullable|string',
            'series' => 'nullable|string',
            'duration' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $sermon->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Sermon updated successfully',
            'data' => $sermon,
        ]);
    }

    public function destroy($id)
    {
        $sermon = Sermon::findOrFail($id);

        if ($sermon->church_id !== auth()->user()->church_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        // Delete associated files
        if ($sermon->audio_url) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $sermon->audio_url));
        }

        if ($sermon->video_url) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $sermon->video_url));
        }

        if ($sermon->slides_url) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $sermon->slides_url));
        }

        if ($sermon->notes_url) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $sermon->notes_url));
        }

        $sermon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sermon deleted successfully',
        ]);
    }

    public function incrementView($id)
    {
        $sermon = Sermon::findOrFail($id);
        $sermon->increment('views');

        return response()->json([
            'success' => true,
            'message' => 'View count incremented',
            'data' => [
                'views' => $sermon->views,
            ],
        ]);
    }

    public function incrementDownload($id)
    {
        $sermon = Sermon::findOrFail($id);
        $sermon->increment('downloads');

        return response()->json([
            'success' => true,
            'message' => 'Download count incremented',
            'data' => [
                'downloads' => $sermon->downloads,
            ],
        ]);
    }

    public function getSeries()
    {
        $churchId = auth()->user()->church_id;

        $series = Sermon::where('church_id', $churchId)
            ->whereNotNull('series')
            ->distinct()
            ->pluck('series');

        return response()->json([
            'success' => true,
            'data' => $series,
        ]);
    }

    public function getPopular()
    {
        $churchId = auth()->user()->church_id;

        $popularSermons = Sermon::where('church_id', $churchId)
            ->orderBy('views', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $popularSermons,
        ]);
    }

    public function getRecent()
    {
        $churchId = auth()->user()->church_id;

        $recentSermons = Sermon::where('church_id', $churchId)
            ->orderBy('sermon_date', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $recentSermons,
        ]);
    }

    public function generateReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'in:pdf,excel',
        ]);

        $churchId = auth()->user()->church_id;

        $sermons = Sermon::where('church_id', $churchId)
            ->whereBetween('sermon_date', [$request->start_date, $request->end_date])
            ->get();

        $report = [
            'period' => [
                'start' => $request->start_date,
                'end' => $request->end_date,
            ],
            'total_sermons' => $sermons->count(),
            'total_views' => $sermons->sum('views'),
            'total_downloads' => $sermons->sum('downloads'),
            'by_series' => $sermons->groupBy('series')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'views' => $group->sum('views'),
                    'downloads' => $group->sum('downloads'),
                ];
            }),
            'by_speaker' => $sermons->groupBy('speaker')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'views' => $group->sum('views'),
                    'downloads' => $group->sum('downloads'),
                ];
            }),
            'sermons' => $sermons,
        ];

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    public function uploadFile(Request $request, $id, $type)
    {
        $sermon = Sermon::findOrFail($id);

        if ($sermon->church_id !== auth()->user()->church_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');
        $fileType = $file->getClientOriginalExtension();
        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();

        // Determine upload path based on file type
        $allowedTypes = [
            'audio' => ['mp3', 'wav', 'ogg', 'm4a'],
            'video' => ['mp4', 'avi', 'mov', 'wmv'],
            'slides' => ['pdf', 'ppt', 'pptx', 'key'],
            'notes' => ['pdf', 'doc', 'docx', 'txt'],
        ];

        if (!in_array($type, array_keys($allowedTypes))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file type',
            ], 400);
        }

        if (!in_array(strtolower($fileType), $allowedTypes[$type])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file format for ' . $type,
            ], 400);
        }

        // Delete old file if exists
        $urlField = $type . '_url';
        $nameField = $type . '_file_name';
        $sizeField = $type . '_file_size';

        if ($sermon->$urlField) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $sermon->$urlField));
        }

        // Upload new file
        $path = $file->store("sermons/{$type}", 'public');

        // Update sermon record
        $sermon->update([
            $urlField => Storage::url($path),
            $nameField => $fileName,
            $sizeField => $fileSize,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => [
                'url' => Storage::url($path),
                'file_name' => $fileName,
                'file_size' => $fileSize,
            ],
        ]);
    }

    public function deleteFile($id, $type)
    {
        $sermon = Sermon::findOrFail($id);

        if ($sermon->church_id !== auth()->user()->church_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $urlField = $type . '_url';
        $nameField = $type . '_file_name';
        $sizeField = $type . '_file_size';

        if (!$sermon->$urlField) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        // Delete file from storage
        Storage::disk('public')->delete(str_replace('/storage/', '', $sermon->$urlField));

        // Update sermon record
        $sermon->update([
            $urlField => null,
            $nameField => null,
            $sizeField => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully',
        ]);
    }
}
