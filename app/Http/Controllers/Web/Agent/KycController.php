<?php

namespace App\Http\Controllers\Web\Agent;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\RespondsWithFlash;
use App\Models\KycDocument;
use App\Models\User;
use App\Services\KycService;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class KycController extends Controller
{
    use RespondsWithFlash;

    public function __construct(protected KycService $kyc) {}

    public function pending()
    {
        $users = User::where('kyc_status', 'pending')->latest()->paginate(20);

        return view('agent.kyc.pending', compact('users'));
    }

    //     public function document(User $user, KycDocument $document)
    // {
    //     $disk = Storage::disk('local');

    //     dd([
    //         'current_user' => Auth::id(),
    //         'document_user_id' => $document->user_id,
    //         'document_id' => $document->id,
    //         'document_path' => $document->document_path,
    //         'storage_root' => config('filesystems.disks.local.root'),
    //         'full_path' => $disk->path($document->document_path),
    //         'exists' => $disk->exists($document->document_path),
    //         'readable' => is_readable($disk->path($document->document_path)),
    //     ]);
    // }

    public function document(User $user, KycDocument $document)
    {
        // Make sure the document belongs to this user.
        abort_unless(
            $document->user_id === $user->id,
            404
        );

        $disk = Storage::disk('local');

        // Make sure the file exists.
        abort_unless(
            $disk->exists($document->document_path),
            404
        );

        $path = $disk->path($document->document_path);

        // Make sure PHP can read it.
        abort_unless(
            is_readable($path),
            403
        );

        return response()->file($path);
    }

    public function show(User $user)
    {
        abort_unless($user->hasRole('user'), 404);

        $documents = KycDocument::where('user_id', $user->id)->latest()->get();

        return view('agent.kyc.show', compact('user', 'documents'));
    }

    public function approve(Request $request, User $user)
    {
        try {
            $this->kyc->approve(user: $user, verifier: $request->user());

            return $this->success('KYC approved.', 'agent.kyc.pending');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (Throwable $e) {
            return $this->error('Approval failed: '.$e->getMessage());
        }
    }

    public function reject(Request $request, User $user)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $this->kyc->reject(user: $user, verifier: $request->user(), reason: $data['reason']);

            return $this->success('KYC rejected.', 'agent.kyc.pending');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (Throwable $e) {
            return $this->error('Rejection failed: '.$e->getMessage());
        }
    }
}
