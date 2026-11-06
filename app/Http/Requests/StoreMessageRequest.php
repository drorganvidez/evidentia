<?php

namespace App\Http\Requests;

use App\Models\Evidence;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Services\Activity\ActivityAccess;
use App\Services\Messaging\Audience;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A new thread: the addressee is checked against the list the author may write to, recomputed with the object the
 * thread is about, which the author must be able to see.
 */
class StoreMessageRequest extends FormRequest
{
    private ?Model $about = null;

    private bool $resolved = false;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'recipient' => ['required', 'string', Rule::in(Audience::optionsFor($this->user(), $this->about())->pluck('value')->all())],
            'subject' => ['required', 'string', 'min:5', 'max:255'],
            'body' => ['required', 'string', 'min:10', 'max:20000'],
            'about_type' => ['nullable', 'in:evidence,meeting_minutes,meeting_request'],
            'about_id' => ['required_with:about_type', 'nullable', 'integer'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['recipient.in' => 'Elige un destinatario de la lista.'];
    }

    /** The object the thread is about (the last version of an evidence); 404 when it does not exist or is out of reach. */
    public function about(): ?Model
    {
        if ($this->resolved) {
            return $this->about;
        }
        $this->resolved = true;

        $type = $this->input('about_type');
        $id = $this->input('about_id');
        if (! in_array($type, ['evidence', 'meeting_minutes', 'meeting_request'], true) || ! is_numeric($id)) {
            return null;
        }

        return $this->about = self::resolveAbout($type, (int) $id, $this->user());
    }

    /** Shared with the form of a new message. */
    public static function resolveAbout(string $type, int $id, $user): Model
    {
        $about = match ($type) {
            'evidence' => Evidence::find($id)?->headEvidence(),
            'meeting_minutes' => MeetingMinutes::with('meeting')->find($id),
            default => MeetingRequest::find($id),
        };
        abort_if($about === null, 404);
        abort_if($about instanceof Evidence && ! app(ActivityAccess::class)->canViewEvidence($user, $about), 404);

        return $about;
    }
}
