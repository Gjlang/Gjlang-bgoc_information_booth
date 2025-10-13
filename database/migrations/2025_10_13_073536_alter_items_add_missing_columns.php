<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends \Illuminate\Database\Migrations\Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Only add if not exists (safe re-run)
            if (!Schema::hasColumn('items', 'date_in')) {
                $table->date('date_in')->nullable()->after('id');
            }
            if (!Schema::hasColumn('items', 'deadline')) {
                $table->date('deadline')->nullable()->after('date_in');
            }

            if (!Schema::hasColumn('items', 'assign_by_id')) {
                $table->foreignId('assign_by_id')->nullable()->constrained('users')->nullOnDelete()->after('deadline');
            }
            if (!Schema::hasColumn('items', 'assign_to_id')) {
                $table->foreignId('assign_to_id')->nullable()->constrained('users')->nullOnDelete()->after('assign_by_id');
            }

            if (!Schema::hasColumn('items', 'type_label')) {
                $table->string('type_label')->nullable()->after('assign_to_id'); // Internal/Client
            }
            if (!Schema::hasColumn('items', 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete()->after('type_label');
            }
            if (!Schema::hasColumn('items', 'pic_name')) {
                $table->string('pic_name')->nullable()->after('company_id');
            }
            if (!Schema::hasColumn('items', 'product_id')) {
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete()->after('pic_name');
            }

            if (!Schema::hasColumn('items', 'status')) {
                $table->string('status')->default('Pending')->after('product_id'); // Pending/On Going/Completed
            }
            if (!Schema::hasColumn('items', 'remarks')) {
                $table->text('remarks')->nullable()->after('status');
            }

            if (!Schema::hasColumn('items', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('remarks');
            }
            if (!Schema::hasColumn('items', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Drop FKs then columns (order matters)
            $fk = Schema::getConnection()->getDoctrineSchemaManager();

            // If your client doesn’t have doctrine/dbal installed, you can just drop blindly:
            $table->dropConstrainedForeignIdIfExists('assign_by_id');
            $table->dropConstrainedForeignIdIfExists('assign_to_id');
            $table->dropConstrainedForeignIdIfExists('company_id');
            $table->dropConstrainedForeignIdIfExists('product_id');
            $table->dropConstrainedForeignIdIfExists('created_by');
            $table->dropConstrainedForeignIdIfExists('updated_by');

            $table->dropColumn([
                'date_in',
                'deadline',
                'type_label',
                'pic_name',
                'status',
                'remarks',
            ]);
        });
    }
};
