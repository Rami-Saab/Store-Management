<?php // Name : Rodain Gouzlan Id:

// هذا الملف يمثل Migration مُجمّع (squashed) لتغييرات عام 2026 ويُستخدم لإعداد قاعدة البيانات دفعة واحدة.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // تنفيذ إنشاء/تحديث الجداول والمؤشرات والإجراءات المخزنة الخاصة بوحدة الفروع.
    public function up(): void
    {
        // تحديث جدول المستخدمين وإضافة الحقول المطلوبة لإدارة الفروع.
        $this->updateUsersTable();

        // إزالة جداول إعادة تعيين كلمة المرور غير المستخدمة في هذا المشروع.
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('password_resets');

        // إنشاء جدول المحافظات إذا لم يكن موجوداً.
        if (! Schema::hasTable('provinces')) {
            Schema::create('provinces', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 10)->unique();
                $table->timestamps();
            });
        }

        // إنشاء جدول المنتجات إذا لم يكن موجوداً.
        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('sku')->unique();
                $table->decimal('price', 10, 2)->default(0);
                $table->string('status', 30)->default('available');
                $table->timestamps();
                $table->index(['name', 'status']);
            });
        }

        // إنشاء جدول الفروع أو ضمان وجود الأعمدة/الفهارس المطلوبة.
        if (! Schema::hasTable('stores')) {
            Schema::create('stores', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('branch_code')->unique();
                $table->foreignId('province_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
                $table->string('city');
                $table->string('address', 500);
                $table->string('phone', 30);
                $table->text('description')->nullable();
                $table->string('email')->nullable();
                $table->string('status', 30)->default('active');
                $table->string('working_hours', 255)->nullable();
                $table->time('workday_starts_at')->nullable();
                $table->time('workday_ends_at')->nullable();
                $table->date('opening_date');
                $table->string('brochure_path')->nullable();
                $table->timestamps();
                $table->index(['name', 'status', 'province_id', 'city']);
                $table->index(['status', 'province_id'], 'stores_status_province_idx');
                $table->index(['branch_code'], 'stores_branch_code_idx');
            });
        } else {
            // في حال كان الجدول موجوداً نضمن اكتمال الأعمدة والفهارس.
            $this->ensureStoresColumns();
            $this->ensureStoresIndexes();
        }

        // إنشاء جدول ربط المنتجات بالفروع أو ضمان فهارسه.
        if (! Schema::hasTable('product_store')) {
            Schema::create('product_store', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignId('store_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['product_id', 'store_id']);
                $table->index(['store_id'], 'product_store_store_id_idx');
                $table->index(['product_id'], 'product_store_product_id_idx');
            });
        } else {
            $this->ensureProductStoreIndexes();
        }

        // إنشاء جدول تعيينات المستخدمين للفروع أو ضمان فهارسه.
        if (! Schema::hasTable('store_user')) {
            Schema::create('store_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
                $table->string('assignment_role', 30);
                $table->timestamps();
                $table->unique(['store_id', 'user_id']);
                $table->index(['user_id'], 'store_user_user_id_idx');
                $table->index(['store_id', 'assignment_role'], 'store_user_store_role_idx');
                $table->index(['assignment_role'], 'store_user_role_idx');
            });
        } else {
            $this->ensureStoreUserIndexes();
        }

        // إنشاء جداول طلبات التعيين أو ضمان أعمدتها وفهارسها.
        if (! Schema::hasTable('assignment_requests')) {
            Schema::create('assignment_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
                $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
                $table->string('requested_role', 50)->nullable();
                $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
                $table->json('employee_ids')->nullable();
                $table->json('baseline_employee_ids')->nullable();
                $table->json('transfer_employee_ids')->nullable();
                $table->string('status', 20)->default('pending');
                $table->string('decision_note', 255)->nullable();
                $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('decided_at')->nullable();
                $table->timestamps();
                $table->index(['requested_by', 'status'], 'assign_req_requested_by_status_idx');
                $table->index(['store_id', 'status'], 'assign_req_store_status_idx');
            });
        } else {
            $this->ensureAssignmentRequestColumns();
            $this->ensureAssignmentRequestIndexes();
        }

        // إنشاء جدول إشعارات الطلبات أو ضمان فهارسه.
        if (! Schema::hasTable('assignment_request_notifications')) {
            Schema::create('assignment_request_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assignment_request_id')->constrained('assignment_requests')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                // Use a short index name to stay within MySQL's identifier length limits.
                $table->unique(['assignment_request_id', 'user_id'], 'assign_req_notif_unique');
                $table->index(['user_id', 'read_at'], 'assign_notif_user_read_idx');
                $table->index(['user_id', 'created_at'], 'assign_notif_user_created_idx');
            });
        } else {
            $this->ensureAssignmentNotificationIndexes();
        }

        // إنشاء إجراء مخزن للبحث عن الفروع (MySQL).
        $this->createStoreSearchProcedure();
    }

    // التراجع: حذف الجداول وإزالة الإجراء المخزن.
    public function down(): void
    {
        // إزالة الإجراء المخزن أولاً لتجنب التعارض.
        $this->dropStoreSearchProcedure();

        // حذف جداول التعيينات والربط والبيانات الأساسية.
        Schema::dropIfExists('assignment_request_notifications');
        Schema::dropIfExists('assignment_requests');
        Schema::dropIfExists('store_user');
        Schema::dropIfExists('product_store');
        Schema::dropIfExists('stores');
        Schema::dropIfExists('products');
        Schema::dropIfExists('provinces');

        // إعادة جدول المستخدمين إلى حالته قبل التوسعة.
        $this->rollbackUsersTable();
    }

    // تحديث جدول المستخدمين بإضافة الحقول المطلوبة والفهارس اللازمة.
    private function updateUsersTable(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        // إضافة حقول القسم/الدور/الهاتف/الحالة إذا كانت مفقودة.
        if (! Schema::hasColumn('users', 'department')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('department', 100)->default('store_management')->after('password');
            });
        }

        if (! Schema::hasColumn('users', 'job_title')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('job_title', 100)->default('store_employee')->after('department');
            });
        }

        if (! Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone', 30)->nullable()->after('job_title');
            });
        }

        if (! Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('status', 30)->default('active')->after('phone');
            });
        }

        // إضافة فهارس لتحسين الاستعلامات حسب القسم/الدور/الحالة.
        if (
            Schema::hasColumn('users', 'department')
            && Schema::hasColumn('users', 'job_title')
            && ! $this->hasIndex('users', 'users_department_job_title_index')
        ) {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['department', 'job_title']);
            });
        }

        if (
            Schema::hasColumn('users', 'department')
            && Schema::hasColumn('users', 'job_title')
            && Schema::hasColumn('users', 'status')
            && ! $this->hasIndex('users', 'users_dept_job_status_idx')
        ) {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['department', 'job_title', 'status'], 'users_dept_job_status_idx');
            });
        }

        // Project requirement: authentication uses only `name` + `password`.
        // Keep the column for compatibility but allow NULL values.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NULL');
        }
    }

    // إعادة جدول المستخدمين إلى حالته الأصلية عند التراجع.
    private function rollbackUsersTable(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if ($this->hasIndex('users', 'users_dept_job_status_idx')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_dept_job_status_idx');
            });
        }

        if ($this->hasIndex('users', 'users_department_job_title_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_department_job_title_index');
            });
        }

        $columnsToDrop = [];
        foreach (['department', 'job_title', 'phone', 'status'] as $column) {
            if (Schema::hasColumn('users', $column)) {
                $columnsToDrop[] = $column;
            }
        }

        if ($columnsToDrop !== []) {
            Schema::table('users', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NOT NULL');
        }
    }

    // التأكد من وجود أعمدة إضافية مطلوبة في جدول stores.
    private function ensureStoresColumns(): void
    {
        if (! Schema::hasTable('stores')) {
            return;
        }

        if (! Schema::hasColumn('stores', 'description')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->text('description')->nullable()->after('phone');
            });
        }

        if (! Schema::hasColumn('stores', 'working_hours')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->string('working_hours', 255)->nullable()->after('status');
            });
        }

        if (! Schema::hasColumn('stores', 'workday_starts_at')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->time('workday_starts_at')->nullable()->after('working_hours');
            });
        }

        if (! Schema::hasColumn('stores', 'workday_ends_at')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->time('workday_ends_at')->nullable()->after('workday_starts_at');
            });
        }
    }

    // التأكد من وجود الفهارس المطلوبة في جدول stores.
    private function ensureStoresIndexes(): void
    {
        if (! Schema::hasTable('stores')) {
            return;
        }

        if (! $this->hasIndex('stores', 'stores_status_province_idx')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->index(['status', 'province_id'], 'stores_status_province_idx');
            });
        }

        if (! $this->hasIndex('stores', 'stores_branch_code_idx')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->index(['branch_code'], 'stores_branch_code_idx');
            });
        }
    }

    // التأكد من فهارس جدول product_store (المنتجات مع الفروع).
    private function ensureProductStoreIndexes(): void
    {
        if (! Schema::hasTable('product_store')) {
            return;
        }

        if (! $this->hasIndex('product_store', 'product_store_store_id_idx')) {
            Schema::table('product_store', function (Blueprint $table) {
                $table->index(['store_id'], 'product_store_store_id_idx');
            });
        }

        if (! $this->hasIndex('product_store', 'product_store_product_id_idx')) {
            Schema::table('product_store', function (Blueprint $table) {
                $table->index(['product_id'], 'product_store_product_id_idx');
            });
        }
    }

    // التأكد من فهارس جدول store_user (تعيينات الموظفين).
    private function ensureStoreUserIndexes(): void
    {
        if (! Schema::hasTable('store_user')) {
            return;
        }

        if (! $this->hasIndex('store_user', 'store_user_user_id_idx')) {
            Schema::table('store_user', function (Blueprint $table) {
                $table->index(['user_id'], 'store_user_user_id_idx');
            });
        }

        if (! $this->hasIndex('store_user', 'store_user_store_role_idx')) {
            Schema::table('store_user', function (Blueprint $table) {
                $table->index(['store_id', 'assignment_role'], 'store_user_store_role_idx');
            });
        }

        if (! $this->hasIndex('store_user', 'store_user_role_idx')) {
            Schema::table('store_user', function (Blueprint $table) {
                $table->index(['assignment_role'], 'store_user_role_idx');
            });
        }
    }

    // التأكد من أعمدة إضافية في جدول طلبات التعيين.
    private function ensureAssignmentRequestColumns(): void
    {
        if (! Schema::hasTable('assignment_requests')) {
            return;
        }

        if (! Schema::hasColumn('assignment_requests', 'baseline_employee_ids')) {
            Schema::table('assignment_requests', function (Blueprint $table) {
                $table->json('baseline_employee_ids')->nullable()->after('employee_ids');
            });
        }

        if (! Schema::hasColumn('assignment_requests', 'transfer_employee_ids')) {
            Schema::table('assignment_requests', function (Blueprint $table) {
                $table->json('transfer_employee_ids')->nullable()->after('baseline_employee_ids');
            });
        }
    }

    // التأكد من فهارس جدول طلبات التعيين.
    private function ensureAssignmentRequestIndexes(): void
    {
        if (! Schema::hasTable('assignment_requests')) {
            return;
        }

        if (! $this->hasIndex('assignment_requests', 'assign_req_requested_by_status_idx')) {
            Schema::table('assignment_requests', function (Blueprint $table) {
                $table->index(['requested_by', 'status'], 'assign_req_requested_by_status_idx');
            });
        }

        if (! $this->hasIndex('assignment_requests', 'assign_req_store_status_idx')) {
            Schema::table('assignment_requests', function (Blueprint $table) {
                $table->index(['store_id', 'status'], 'assign_req_store_status_idx');
            });
        }
    }

    // التأكد من فهارس جدول إشعارات طلبات التعيين.
    private function ensureAssignmentNotificationIndexes(): void
    {
        if (! Schema::hasTable('assignment_request_notifications')) {
            return;
        }

        if (! $this->hasIndex('assignment_request_notifications', 'assign_notif_user_read_idx')) {
            Schema::table('assignment_request_notifications', function (Blueprint $table) {
                $table->index(['user_id', 'read_at'], 'assign_notif_user_read_idx');
            });
        }

        if (! $this->hasIndex('assignment_request_notifications', 'assign_notif_user_created_idx')) {
            Schema::table('assignment_request_notifications', function (Blueprint $table) {
                $table->index(['user_id', 'created_at'], 'assign_notif_user_created_idx');
            });
        }
    }

    // إنشاء الإجراء المخزن للبحث عن الفروع (MySQL فقط).
    private function createStoreSearchProcedure(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_search_store_branches');
        DB::unprepared("
            CREATE PROCEDURE sp_search_store_branches (
                IN p_name VARCHAR(255),
                IN p_status VARCHAR(30),
                IN p_province_id BIGINT
            )
            BEGIN
                SELECT
                    stores.id,
                    stores.name,
                    stores.branch_code,
                    provinces.name AS province_name,
                    stores.city,
                    stores.status,
                    stores.opening_date,
                    stores.brochure_path,
                    (
                        SELECT users.name
                        FROM store_user
                        INNER JOIN users ON users.id = store_user.user_id
                        WHERE store_user.store_id = stores.id
                          AND store_user.assignment_role = 'manager'
                        LIMIT 1
                    ) AS manager_name,
                    (
                        SELECT COUNT(*)
                        FROM store_user
                        WHERE store_user.store_id = stores.id
                          AND store_user.assignment_role = 'employee'
                    ) AS employees_count,
                    (
                        SELECT COUNT(*)
                        FROM product_store
                        WHERE product_store.store_id = stores.id
                    ) AS products_count
                FROM stores
                INNER JOIN provinces ON provinces.id = stores.province_id
                WHERE (p_name IS NULL OR stores.name LIKE CONCAT('%', p_name, '%'))
                  AND (p_status IS NULL OR stores.status = p_status)
                  AND (p_province_id IS NULL OR stores.province_id = p_province_id)
                ORDER BY stores.created_at DESC;
            END
        ");
    }

    // حذف الإجراء المخزن عند التراجع.
    private function dropStoreSearchProcedure(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_search_store_branches');
    }

    // التحقق من وجود فهرس باسم محدد داخل جدول.
    private function hasIndex(string $table, string $indexName): bool
    {
        try {
            $rows = DB::select('SHOW INDEX FROM '.$this->quoteIdentifier($table));
            foreach ($rows as $row) {
                $key = $row->Key_name ?? null;
                if ($key === $indexName) {
                    return true;
                }
            }
            return false;
        } catch (\Throwable) {
            return false;
        }
    }

    // تأمين اسم الجدول ضد الأحرف الخاصة في الاستعلام.
    private function quoteIdentifier(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }
};

// Summary: Migration مجمّع يهيّئ جداول Block 3 (الفروع/المنتجات/التعيينات) ويضمن الفهارس ويضيف إجراء البحث.