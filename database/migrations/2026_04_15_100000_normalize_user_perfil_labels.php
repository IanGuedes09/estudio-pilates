<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('perfil', 'ADM_PROF')->update(['perfil' => 'Administrador']);
        DB::table('users')->where('perfil', 'PROFESSORA')->update(['perfil' => 'Professor']);
    }

    public function down(): void
    {
        DB::table('users')->where('perfil', 'Administrador')->update(['perfil' => 'ADM_PROF']);
        DB::table('users')->where('perfil', 'Professor')->update(['perfil' => 'PROFESSORA']);
    }
};
