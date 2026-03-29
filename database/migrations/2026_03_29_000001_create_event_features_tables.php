<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración unificada para todas las funcionalidades del evento de concesionario
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Tracking de visualizaciones de vehículos en el evento
        Schema::create('vehicle_event_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable();
            $table->string('source')->default('kiosk'); // kiosk, qr, direct
            $table->integer('view_duration_seconds')->default(0);
            $table->boolean('spin_interacted')->default(false);
            $table->boolean('compared')->default(false);
            $table->boolean('quoted')->default(false);
            $table->boolean('lead_captured')->default(false);
            $table->string('device_type')->nullable(); // tablet, mobile, desktop
            $table->string('event_name')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'created_at']);
            $table->index('source');
            $table->index('event_name');
        });

        // 2. Cotizaciones de financiamiento
        Schema::create('vehicle_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->decimal('vehicle_price', 15, 2);
            $table->decimal('down_payment', 15, 2); // Prima
            $table->decimal('down_payment_percent', 5, 2);
            $table->integer('term_months'); // Plazo en meses
            $table->decimal('interest_rate', 5, 2); // Tasa de interés anual
            $table->decimal('monthly_payment', 15, 2); // Cuota mensual
            $table->decimal('total_interest', 15, 2); // Total intereses
            $table->decimal('total_amount', 15, 2); // Monto total a pagar
            $table->string('currency', 3)->default('CRC');
            $table->boolean('email_sent')->default(false);
            $table->boolean('pdf_generated')->default(false);
            $table->string('pdf_path')->nullable();
            $table->string('event_name')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'created_at']);
            $table->index('customer_email');
        });

        // 3. Escaneos de códigos QR
        Schema::create('qr_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('qr_code')->unique();
            $table->integer('scan_count')->default(0);
            $table->timestamp('last_scanned_at')->nullable();
            $table->string('event_name')->nullable();
            $table->json('scan_history')->nullable(); // Array de timestamps
            $table->timestamps();

            $table->index('qr_code');
            $table->index(['vehicle_id', 'scan_count']);
        });

        // 4. Hotspots interactivos en el Spin 360
        Schema::create('spin_hotspots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spin_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->default('info'); // info, engine, sound, wheel, interior
            $table->integer('frame_number'); // En qué frame aparece
            $table->integer('frame_range_start')->nullable(); // Rango de frames visible
            $table->integer('frame_range_end')->nullable();
            $table->decimal('position_x', 5, 2); // Posición X en porcentaje (0-100)
            $table->decimal('position_y', 5, 2); // Posición Y en porcentaje (0-100)
            $table->string('image')->nullable(); // Imagen ampliada
            $table->string('video')->nullable(); // Video corto opcional
            $table->string('audio')->nullable(); // Audio (ej: sonido del motor)
            $table->json('specs')->nullable(); // Especificaciones técnicas JSON
            $table->boolean('status')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['spin_id', 'frame_number']);
        });

        // 5. Colores alternativos de vehículos (AR Lite)
        Schema::create('vehicle_colors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('color_name');
            $table->string('color_code', 7); // Hex color #RRGGBB
            $table->string('preview_image')->nullable(); // Imagen del vehículo en ese color
            $table->string('spin_frames_dir')->nullable(); // Directorio con frames de ese color
            $table->boolean('is_default')->default(false);
            $table->decimal('additional_price', 15, 2)->default(0); // Costo adicional
            $table->boolean('available')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['vehicle_id', 'is_default']);
        });

        // 6. Videos de Test Drive Virtual
        Schema::create('test_drive_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('video_path');
            $table->string('thumbnail')->nullable();
            $table->string('engine_audio')->nullable(); // Audio del motor
            $table->integer('duration_seconds')->default(0);
            $table->string('video_type')->default('exterior'); // exterior, interior, engine, features
            $table->boolean('autoplay')->default(false);
            $table->boolean('loop')->default(false);
            $table->boolean('status')->default(true);
            $table->integer('order')->default(0);
            $table->integer('views_count')->default(0);
            $table->timestamps();

            $table->index(['vehicle_id', 'video_type']);
        });

        // 7. Leads específicos del evento (extendido)
        Schema::create('event_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->text('notes')->nullable();
            $table->string('source')->default('event'); // event, qr, kiosk, compare
            $table->string('event_name')->nullable();
            $table->string('interest_level')->default('medium'); // low, medium, high, hot
            $table->boolean('contacted')->default(false);
            $table->timestamp('contacted_at')->nullable();
            $table->string('contacted_by')->nullable();
            $table->json('vehicles_viewed')->nullable(); // Array de vehicle_ids vistos
            $table->json('vehicles_compared')->nullable(); // Array de comparaciones
            $table->json('quotes_requested')->nullable(); // Array de quote_ids
            $table->string('follow_up_status')->default('pending'); // pending, scheduled, completed, no_answer
            $table->timestamp('follow_up_date')->nullable();
            $table->timestamps();

            $table->index(['event_name', 'created_at']);
            $table->index('interest_level');
            $table->index('follow_up_status');
            $table->index('phone');
        });

        // 8. Wishlist de clientes con token compartible
        Schema::create('client_wishlists', function (Blueprint $table) {
            $table->id();
            $table->string('share_token', 32)->unique();
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->json('vehicle_ids'); // Array de IDs de vehículos
            $table->string('event_name')->nullable();
            $table->integer('access_count')->default(0);
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('share_token');
            $table->index('client_email');
        });

        // 9. Configuración del modo kiosko
        Schema::create('kiosk_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('event_name')->nullable();
            $table->string('logo')->nullable();
            $table->string('background_color', 7)->default('#0b0f14');
            $table->string('accent_color', 7)->default('#c2ac1f');
            $table->integer('auto_rotate_seconds')->default(15); // Segundos entre vehículos
            $table->integer('idle_timeout_seconds')->default(60); // Timeout de inactividad
            $table->boolean('show_price')->default(true);
            $table->boolean('show_specs')->default(true);
            $table->boolean('enable_compare')->default(true);
            $table->boolean('enable_quote')->default(true);
            $table->boolean('enable_qr')->default(true);
            $table->boolean('enable_lead_capture')->default(true);
            $table->json('featured_vehicle_ids')->nullable(); // Vehículos destacados
            $table->json('excluded_vehicle_ids')->nullable(); // Vehículos excluidos
            $table->string('welcome_message')->nullable();
            $table->string('contact_info')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kiosk_settings');
        Schema::dropIfExists('client_wishlists');
        Schema::dropIfExists('event_leads');
        Schema::dropIfExists('test_drive_videos');
        Schema::dropIfExists('vehicle_colors');
        Schema::dropIfExists('spin_hotspots');
        Schema::dropIfExists('qr_scans');
        Schema::dropIfExists('vehicle_quotes');
        Schema::dropIfExists('vehicle_event_views');
    }
};
