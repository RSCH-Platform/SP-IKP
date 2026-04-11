<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('laporan_insidens', function (Blueprint $table) {
            // Digital signature fields (HMAC-SHA256)
            $table->string('confirmation_signature', 64)->nullable()->comment('HMAC-SHA256 signature for tamper-proofing');
            $table->timestamp('confirmed_at')->nullable()->comment('When report was digitally signed');
            $table->string('confirmation_data_hash', 64)->nullable()->comment('Hash of form data at time of confirmation');
            $table->foreignId('signed_by')->nullable()->constrained('users')->comment('User who signed the report');
            $table->json('signature_metadata')->nullable()->comment('Additional metadata: IP, user-agent, timezone, etc');

            // Add index for quick signature lookups
            $table->index('confirmation_signature');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_insidens', function (Blueprint $table) {
            $table->dropIndex(['confirmation_signature']);
            $table->dropForeign(['signed_by']);
            $table->dropColumn([
                'confirmation_signature',
                'confirmed_at',
                'confirmation_data_hash',
                'signed_by',
                'signature_metadata'
            ]);
        });
    }
};
