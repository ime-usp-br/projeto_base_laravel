<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSenhaunicaUsersTable extends Migration
{
    /**
     * Executa as migrações.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
            if (!Schema::hasColumn('users', 'codpes')) {

                    if ('sqlite' === Schema::connection($this->getConnection())->getConnection()->getDriverName()) {
                    $table->integer('codpes')->nullable();
                } else {
                    $table->integer('codpes');
                }
            }
        });
    }

    /**
     * Reverte as migrações.
     *
     * @return void
     */
    public function down(): void
    {

    }
}