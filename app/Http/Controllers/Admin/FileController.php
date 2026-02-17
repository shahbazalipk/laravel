<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function __construct(
        private FileService $service
    ) {}

    public function index(Request $request)
    {
        $category = $request->get('category');
        $files = $this->service->getAllFiles($category);
        
        return view('admin.files.index', compact('files', 'category'));
    }

    public function create()
    {
        return view('admin.files.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|max:51200', // 50MB max per file
            'name' => 'nullable|string|max:255',
            'category' => 'nullable|in:document,image,video,audio,other',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $uploadedCount = 0;
        $files = $request->file('files');

        foreach ($files as $index => $file) {
            $fileData = [
                'name' => $validated['name'] ? $validated['name'] . '-' . ($index + 1) : null,
                'category' => $validated['category'] ?? null,
                'description' => $validated['description'] ?? null,
                'is_public' => $validated['is_public'] ?? false,
            ];

            $this->service->uploadFile($file, $fileData);
            $uploadedCount++;
        }

        $message = $uploadedCount === 1 
            ? 'File uploaded successfully' 
            : "{$uploadedCount} files uploaded successfully";

        return redirect()->route('admin.files.index')
            ->with('success', $message);
    }

    public function edit(File $file)
    {
        return view('admin.files.edit', compact('file'));
    }

    public function update(Request $request, File $file)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:document,image,video,audio,other',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $this->service->updateFile($file, $validated);

        return redirect()->route('admin.files.index')
            ->with('success', 'File updated successfully');
    }

    public function destroy(File $file)
    {
        $this->service->deleteFile($file);

        return redirect()->route('admin.files.index')
            ->with('success', 'File deleted successfully');
    }

    public function download(File $file)
    {
        if (!Storage::disk('public')->exists($file->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($file->file_path, $file->original_name);
    }
}
