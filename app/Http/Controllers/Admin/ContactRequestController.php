<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ContactRequestReplyMail;
use App\Models\AuditLog;
use App\Models\ContactRequest;
use App\Models\ContactRequestNote;
use App\Models\ContactRequestReply;
use App\Models\ContactRequestStatusHistory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class ContactRequestController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', ContactRequest::class);
        $requests = ContactRequest::query()
            ->with('assignedAdmin:id,name,public_id')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->string('priority')->toString()))
            ->when($request->filled('subject'), fn ($query) => $query->where('subject', $request->string('subject')->toString()))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $value = '%'.$request->string('q')->toString().'%';
                $query->where(fn ($search) => $search->where('first_name', 'like', $value)->orWhere('last_name', 'like', $value)->orWhere('email', 'like', $value)->orWhere('phone', 'like', $value)->orWhere('public_id', 'like', $value)->orWhere('message', 'like', $value));
            })
            ->latest('submitted_at')->paginate(30)->withQueryString();

        return view('admin.contact-requests.index', compact('requests'));
    }

    public function show(ContactRequest $contactRequest): View
    {
        Gate::authorize('view', $contactRequest);
        $contactRequest->load(['assignedAdmin:id,name,public_id', 'user:id,name,email,public_id', 'replies.admin:id,name,public_id', 'notes.admin:id,name,public_id', 'statusHistory.admin:id,name,public_id']);
        $previous = ContactRequest::query()->where('email', $contactRequest->email)->whereKeyNot($contactRequest->id)->latest('submitted_at')->limit(5)->get(['public_id', 'subject', 'status', 'submitted_at']);

        return view('admin.contact-requests.show', ['contact' => $contactRequest, 'admins' => User::query()->where('role', 'admin')->orderBy('name')->get(['id', 'name', 'public_id']), 'previous' => $previous]);
    }

    public function update(Request $request, ContactRequest $contactRequest): RedirectResponse
    {
        Gate::authorize('update', $contactRequest);
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(config('contact-requests.statuses')))],
            'priority' => ['required', 'in:'.implode(',', array_keys(config('contact-requests.priorities')))],
            'assigned_admin_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'admin')],
        ]);
        $oldStatus = $contactRequest->status;
        $contactRequest->update([...$data, 'resolved_at' => $data['status'] === 'resolved' ? now() : null, 'last_activity_at' => now()]);
        if ($oldStatus !== $data['status']) {
            ContactRequestStatusHistory::create(['contact_request_id' => $contactRequest->id, 'admin_user_id' => $request->user()->id, 'from_status' => $oldStatus, 'to_status' => $data['status']]);
        }
        $this->audit($request, 'contact_request.updated', $contactRequest);

        return back()->with('status', 'Talep bilgileri güncellendi.');
    }

    public function note(Request $request, ContactRequest $contactRequest): RedirectResponse
    {
        Gate::authorize('note', $contactRequest);
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);
        ContactRequestNote::create(['contact_request_id' => $contactRequest->id, 'admin_user_id' => $request->user()->id, 'body' => trim(strip_tags($data['body']))]);
        $contactRequest->update(['last_activity_at' => now()]);
        $this->audit($request, 'contact_request.note_added', $contactRequest);

        return back()->with('status', 'İç not kaydedildi.');
    }

    public function reply(Request $request, ContactRequest $contactRequest): RedirectResponse
    {
        Gate::authorize('reply', $contactRequest);
        $data = $request->validate(['body' => ['required', 'string', 'min:5', 'max:5000']]);
        $body = trim(strip_tags($data['body']));
        Mail::to($contactRequest->email)->send(new ContactRequestReplyMail($contactRequest, $body));
        ContactRequestReply::create(['contact_request_id' => $contactRequest->id, 'admin_user_id' => $request->user()->id, 'body' => $body, 'sent_at' => now()]);
        $oldStatus = $contactRequest->status;
        $contactRequest->update(['status' => 'answered', 'first_responded_at' => $contactRequest->first_responded_at ?? now(), 'last_activity_at' => now()]);
        ContactRequestStatusHistory::create(['contact_request_id' => $contactRequest->id, 'admin_user_id' => $request->user()->id, 'from_status' => $oldStatus, 'to_status' => 'answered', 'note' => 'E-posta yanıtı gönderildi']);
        $this->audit($request, 'contact_request.replied', $contactRequest);

        return back()->with('status', 'Yanıt e-posta ile gönderildi ve geçmişe kaydedildi.');
    }

    public function anonymize(Request $request, ContactRequest $contactRequest): RedirectResponse
    {
        Gate::authorize('anonymize', $contactRequest);
        $contactRequest->update(['first_name' => 'Anonim', 'last_name' => 'Kullanıcı', 'email' => 'anonymized-'.$contactRequest->public_id.'@invalid.local', 'phone' => null, 'message' => '[Kişisel veri talebi üzerine anonimleştirildi]', 'ip_hash' => null, 'user_agent' => null, 'referrer_url' => null, 'utm_source' => null, 'utm_medium' => null, 'utm_campaign' => null, 'anonymized_at' => now(), 'last_activity_at' => now()]);
        $this->audit($request, 'contact_request.anonymized', $contactRequest);

        return back()->with('status', 'Talep kişisel verileri güvenli biçimde anonimleştirildi.');
    }

    private function audit(Request $request, string $action, ContactRequest $contactRequest): void
    {
        AuditLog::create(['user_id' => $request->user()->id, 'action' => $action, 'auditable_type' => ContactRequest::class, 'auditable_id' => $contactRequest->id, 'metadata' => ['contact_request_public_id' => $contactRequest->public_id]]);
    }
}
