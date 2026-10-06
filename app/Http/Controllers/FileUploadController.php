<!-- 

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class FileUploadController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        abort_unless($project->roleFor($request->user())?->canContribute(), 403);

        $request->validate([
            'file' => ['required', 'file', 'max:' . ProjectFile::MAX_KILOBYTES],
        ], [
            'file.required' => 'Choose a file to upload. If you did, it may be larger than the server allows.',
            'file.max' => 'The file must be ' . (ProjectFile::MAX_KILOBYTES / 1024) . ' MB or smaller.',
            'file.uploaded' => 'The file failed to upload. It may exceed the server upload limit.',
        ]);

        $upload = $request->file('file');
        $disk = Storage::disk(ProjectFile::DISK);

        // Random name without extension: the original name is only kept in the database.
        $path = $disk->putFileAs("project-files/{$project->id}", $upload, (string) Str::ulid());

        abort_if($path === false, 500, 'The file could not be stored.');

        try {
            $project->files()->create([
                'user_id' => $request->user()->id,
                'file_name' => Str::limit(basename(str_replace('\\', '/', $upload->getClientOriginalName())), 255, ''),
                'file_path' => $path,
                'file_type' => $upload->getClientMimeType(),
                'file_size' => $upload->getSize(),
            ]);
        } catch (Throwable $e) {
            $disk->delete($path); // don't leave an orphaned file if the insert fails

            throw $e;
        }

        return redirect()
            ->to(route('projects.show', $project) . '#files')
            ->with('status', 'File uploaded.');
    }

    public function download(Request $request, Project $project, ProjectFile $file): StreamedResponse
    {
        abort_unless($project->roleFor($request->user()), 403);
        abort_unless($file->project_id === $project->id, 404);

        $disk = Storage::disk(ProjectFile::DISK);

        abort_unless($disk->exists($file->file_path), 404);

        return $disk->download($file->file_path, $file->file_name, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Request $request, Project $project, ProjectFile $file): RedirectResponse
    {
        abort_unless($file->project_id === $project->id, 404);
        abort_unless($file->deletableBy($project->roleFor($request->user()), $request->user()), 403);

        $name = $file->file_name;

        $file->delete();

        return redirect()
            ->to(route('projects.show', $project) . '#files')
            ->with('status', "File \"{$name}\" was deleted.");
    }
} -->