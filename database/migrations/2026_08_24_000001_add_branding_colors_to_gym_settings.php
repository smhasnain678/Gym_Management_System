<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add branding color columns to gym_settings.
     *
     * primary_color   – hex colour used for the first word of the gym name
     *                   and primary accent elements in the sidebar.
     * secondary_color – hex colour used for remaining words of the gym name
     *                   and secondary accent elements.
     *
     * Defaults are the original WarmUp green palette so existing deployments
     * remain visually unchanged until the owner selects custom colours.
     */
    public function up(): void
    {
        Schema::table('gym_settings', function (Blueprint $table) {
            $table->string('primary_color', 7)->nullable()->default('#22C55E')->after('gym_logo');
            $table->string('secondary_color', 7)->nullable()->default('#16A34A')->after('primary_color');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('gym_settings', function (Blueprint $table) {
            $table->dropColumn(['primary_color', 'secondary_color']);
        });
    }
};
