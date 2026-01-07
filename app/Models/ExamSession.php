<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model
{
    protected $fillable = [
        'user_id',
        'simulasi_id',
        'status',
        'started_at',
        'submitted_at',
        'last_activity',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'last_activity' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function simulasi()
    {
        return $this->belongsTo(Simulasi::class);
    }

    // Helper methods
    public function isActive()
    {
        return in_array($this->status, ['logged_in', 'in_progress', 'reviewing']);
    }

    public function updateActivity()
    {
        $this->update(['last_activity' => now()]);
    }
}
