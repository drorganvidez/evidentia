<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Http\Controllers\Concerns\PaginatesLists;
use App\Models\Attendee;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\User;
use App\Support\CertificatePdf;
use App\View\PlainText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Certificates of attendance (the student downloads one for every event they attended), diplomas issued by the
 * presidency or the lecturer (speakers, contest winners) and the public page that verifies the code of any of them.
 */
class CertificateController extends Controller
{
    use PaginatesLists;

    /** The certificate of an attended event, created the first time and the same one (same code) afterwards */
    public function attendance(int $id)
    {
        $user = Auth::user();
        $event = Event::findOrFail($id);
        abort_unless(
            Attendee::where('user_id', $user->id)->where('event_id', $event->id)->where('status', Attendee::ATTENDED)->exists(),
            404,
        );

        $certificate = Certificate::firstOrCreate(
            ['type' => Certificate::ATTENDANCE, 'user_id' => $user->id, 'event_id' => $event->id],
            [
                'recipient_name' => trim($user->name.' '.$user->surname),
                'title' => PlainText::fromHtml((string) $event->name) ?: 'Evento',
                'hours' => $event->hours,
            ],
        );

        return CertificatePdf::download($certificate);
    }

    /** A diploma of the person who downloads it, or any certificate for the management */
    public function download(string $code)
    {
        $certificate = Certificate::where('code', $code)->firstOrFail();
        $user = Auth::user();
        abort_unless($certificate->user_id === $user->id || $this->manages($user), 404);

        return CertificatePdf::download($certificate);
    }

    /** Public: anybody holding the code sees whether it is valid and whom it was issued to */
    public function verify(?string $code = null)
    {
        $code = strtoupper(trim((string) ($code ?? request('code'))));
        $certificate = $code === '' ? null : Certificate::with('event')->where('code', $code)->first();

        return view('certificate.verify', ['code' => $code, 'certificate' => $certificate]);
    }

    public function index(Request $request)
    {
        $query = Certificate::query()->with(['user', 'issuer']);
        $type = in_array($request->query('tipo'), ['diplomas', 'asistencia'], true) ? $request->query('tipo') : null;
        if ($type !== null) {
            $query->where('type', $type === 'diplomas' ? Certificate::DIPLOMA : Certificate::ATTENDANCE);
        }
        $this->searchList($query, $request, ['code', 'recipient_name', 'title']);

        return view('certificate.index', [
            'type' => $type,
            'certificates' => $this->paginateList($query->orderByDesc('issued_at')->orderByDesc('id'), $request),
        ]);
    }

    public function create()
    {
        return view('certificate.create', ['users' => User::orderBy('surname')->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'recipient_name' => 'required_without:user_id|nullable|string|max:255',
            'title' => 'required|string|max:255',
            'body' => 'nullable|string|max:2000',
            'hours' => 'nullable|numeric|min:0|max:999',
            'issued_at' => 'required|date',
        ], [], [
            'user_id' => 'persona', 'recipient_name' => 'nombre', 'title' => 'motivo', 'body' => 'texto',
            'hours' => 'horas', 'issued_at' => 'fecha',
        ]);

        $recipient = filled($data['user_id'] ?? null) ? User::find($data['user_id']) : null;
        $certificate = Certificate::create([
            ...$data,
            'type' => Certificate::DIPLOMA,
            'recipient_name' => filled($data['recipient_name'] ?? null)
                ? $data['recipient_name']
                : trim($recipient->name.' '.$recipient->surname),
            'issued_by' => Auth::id(),
        ]);

        Activity::record(ActivityAction::CertificateIssued, $certificate, details: [
            'code' => $certificate->code, 'recipient' => $certificate->recipient_name, 'title' => $certificate->title,
        ], targets: $recipient ? [$recipient] : []);

        return redirect()->route('certificate.index')
            ->with('success', 'Diploma emitido para '.$certificate->recipient_name.' con el código '.$certificate->code.'.');
    }

    public function destroy(int $id)
    {
        $certificate = Certificate::findOrFail($id);
        Activity::record(ActivityAction::CertificateRevoked, $certificate, details: [
            'code' => $certificate->code, 'recipient' => $certificate->recipient_name, 'title' => $certificate->title,
        ], targets: $certificate->user ? [$certificate->user] : []);
        $certificate->delete();

        return redirect()->route('certificate.index')->with('success', 'Certificado '.$certificate->code.' revocado.');
    }

    private function manages(User $user): bool
    {
        return $user->hasRole('PRESIDENT') || $user->hasRole('LECTURE');
    }
}
