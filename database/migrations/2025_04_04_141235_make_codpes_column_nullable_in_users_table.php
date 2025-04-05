<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa as migrações.
     * Torna a coluna 'codpes' na tabela 'users' anulável.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('codpes')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverte as migrações.
     * Torna a coluna 'codpes' na tabela 'users' não anulável novamente (pode falhar se houver valores nulos).
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('codpes')->change();
        });
    }
};