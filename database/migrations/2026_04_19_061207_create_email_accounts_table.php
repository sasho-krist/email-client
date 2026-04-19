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
        Schema::create('email_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('profile_name')->nullable();
            $table->string('account_color', 7)->nullable();
            $table->string('email');
            $table->text('mailbox_password');
            $table->string('display_name')->nullable();
            $table->string('reply_to')->nullable();
            $table->string('organization')->nullable();
            $table->string('imap_host');
            $table->unsignedSmallInteger('imap_port')->default(993);
            $table->string('imap_security', 32)->default('ssl');
            $table->string('imap_auth', 32)->default('password');
            $table->string('smtp_host');
            $table->unsignedSmallInteger('smtp_port')->default(465);
            $table->string('smtp_security', 32)->default('ssl');
            $table->string('smtp_auth', 32)->default('password');
            $table->boolean('check_on_startup')->default(true);
            $table->unsignedSmallInteger('check_interval_minutes')->default(10);
            $table->boolean('use_idle')->default(true);
            $table->string('delete_behavior', 32)->default('move_trash');
            $table->string('folder_inbox')->default('INBOX');
            $table->string('folder_sent')->nullable();
            $table->string('folder_spam')->nullable();
            $table->string('folder_trash')->nullable();
            $table->longText('signature_html')->nullable();
            $table->boolean('signature_use_html')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_accounts');
    }
};
