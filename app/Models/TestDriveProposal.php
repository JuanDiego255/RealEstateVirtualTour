<?php

namespace App\Models;

use App\Lead;
use App\Appointment;
use App\Properties;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Propuesta de prueba de manejo generada por el bot. Espera la confirmación de
 * un asesor, que al aceptarla crea la cita real en la agenda.
 */
class TestDriveProposal extends Model
{
    protected $table = 'whatsapp_test_drive_proposals';

    protected $fillable = [
        'company_id', 'chat_id', 'lead_id', 'phone', 'vehicle_id',
        'client_name', 'client_email', 'proposed_at', 'duration_minutes',
        'notes', 'status', 'appointment_id',
    ];

    protected $casts = [
        'proposed_at'      => 'datetime',
        'duration_minutes' => 'integer',
    ];

    const STATUS_PENDING   = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_DISMISSED = 'dismissed';

    public static function available(): bool
    {
        static $cache = null;
        if ($cache === null) {
            $cache = Schema::hasTable('whatsapp_test_drive_proposals');
        }
        return $cache;
    }

    public function vehicle()
    {
        return $this->belongsTo(Properties::class, 'vehicle_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function scopePending($q)
    {
        return $q->where('status', self::STATUS_PENDING);
    }

    public function vehicleTitle(): string
    {
        if (!$this->vehicle) {
            return 'Vehículo';
        }
        $t = trim(($this->vehicle->brand ?? '') . ' ' . ($this->vehicle->model ?? '') . ' ' . ($this->vehicle->year ?? ''));
        return $t !== '' ? $t : ($this->vehicle->name ?? 'Vehículo');
    }
}
