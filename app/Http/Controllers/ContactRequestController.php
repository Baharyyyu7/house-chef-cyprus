<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\ContactRequestNotification;
use App\Models\ContactRequest;
use App\Models\ContactRequestStatusHistory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

final class ContactRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $this->validateHoneypot($request);
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^[0-9+() .-]+$/'],
            'subject' => ['required', 'string', 'in:'.implode(',', array_keys(config('contact-requests.subjects')))],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'privacy_consent' => ['accepted'],
            'source_page' => ['nullable', 'string', 'max:2048'],
            'captcha_token' => [config('contact-requests.captcha.enabled') ? 'required' : 'nullable', 'string', 'max:4096'],
        ]);
        $this->validateCaptcha($data['captcha_token'] ?? null, $request);

        $email = mb_strtolower(trim($data['email']));
        $contact = ContactRequest::create([
            'first_name' => trim(strip_tags($data['first_name'])),
            'last_name' => trim(strip_tags($data['last_name'])),
            'email' => $email,
            'phone' => $this->normalizePhone($data['phone'] ?? null),
            'subject' => $data['subject'],
            'message' => trim(strip_tags($data['message'])),
            'status' => 'new',
            'priority' => in_array($data['subject'], ['copyright_notice', 'privacy'], true) ? 'high' : 'normal',
            'user_id' => User::query()->whereRaw('lower(email) = ?', [$email])->value('id'),
            'source_page' => $this->safeUrl($data['source_page'] ?? null),
            'referrer_url' => $this->safeUrl($request->headers->get('referer')),
            'utm_source' => $request->query('utm_source'),
            'utm_medium' => $request->query('utm_medium'),
            'utm_campaign' => $request->query('utm_campaign'),
            'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
            'user_agent' => Str::limit((string) $request->userAgent(), 512, ''),
            'privacy_consent_at' => now(),
            'last_activity_at' => now(),
            'submitted_at' => now(),
        ]);
        ContactRequestStatusHistory::create(['contact_request_id' => $contact->id, 'to_status' => 'new', 'note' => 'Public form submission']);

        Mail::to((string) config('contact-requests.recipient'))->queue(new ContactRequestNotification($contact));

        return redirect(route('legal.center').'#iletisim-ve-geri-bildirim')
            ->with('contact_reference', $contact->public_id)
            ->with('status', __('contact.flash_received', ['reference' => $contact->public_id]));
    }

    private function validateHoneypot(Request $request): void
    {
        if (filled($request->input('website'))) {
            abort(422, 'İstek doğrulanamadı.');
        }
    }

    private function validateCaptcha(?string $token, Request $request): void
    {
        if (! config('contact-requests.captcha.enabled')) {
            return;
        }

        $secret = (string) config('contact-requests.captcha.secret');
        abort_unless($secret !== '' && $token !== null, 422, 'Güvenlik doğrulaması tamamlanamadı.');

        $verified = Http::asForm()->timeout(3)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $request->ip(),
        ])->json('success') === true;

        abort_unless($verified, 422, 'Güvenlik doğrulaması tamamlanamadı.');
    }

    private function normalizePhone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        return Str::limit(preg_replace('/[^0-9+]/', '', $phone) ?? '', 32, '');
    }

    private function safeUrl(?string $url): ?string
    {
        return $url !== null && filter_var($url, FILTER_VALIDATE_URL) ? Str::limit($url, 2048, '') : null;
    }
}
