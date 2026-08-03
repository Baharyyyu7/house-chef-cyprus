<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ContactRequestNote extends Model
{
    protected $fillable = ['contact_request_id', 'admin_user_id', 'body'];

    /** @return BelongsTo<ContactRequest, $this> */
    public function contactRequest(): BelongsTo
    {
        return $this->belongsTo(ContactRequest::class);
    }

    /** @return BelongsTo<User, $this> */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
