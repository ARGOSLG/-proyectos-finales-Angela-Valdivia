<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE alerts DROP CONSTRAINT alerts_type_check');

        DB::statement("ALTER TABLE alerts ADD CONSTRAINT alerts_type_check
            CHECK (type IN (
                'keyword_detected',
                'phone_usage',
                'drowsiness',
                'speeding',
                'harsh_braking',
                'sos_button',
                'alcohol_detected',
                'biometric_alert',
                'geofence_exit',
                'manual',
                'fifth_wheel_unlock',
                'trailer_theft_in_progress'
            ))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE alerts DROP CONSTRAINT alerts_type_check');

        DB::statement("ALTER TABLE alerts ADD CONSTRAINT alerts_type_check
            CHECK (type IN (
                'keyword_detected',
                'phone_usage',
                'drowsiness',
                'speeding',
                'harsh_braking',
                'sos_button',
                'alcohol_detected',
                'biometric_alert',
                'geofence_exit',
                'manual'
            ))");
    }
};
