<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->date('date_in')->nullable();
            $table->date('deadline')->nullable();

            $table->foreignId('assign_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assign_to_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('type_label')->nullable(); // Internal or Client
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('pic_name')->nullable();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

            $table->string('status')->default('Pending'); // Pending / On Going / Completed
            $table->text('remarks')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
