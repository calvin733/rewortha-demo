<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION prevent_snapshot_mutation() RETURNS trigger
            LANGUAGE plpgsql SECURITY INVOKER AS $$
            BEGIN
                RAISE EXCEPTION 'Historical snapshots are immutable' USING ERRCODE = '23514';
            END;
            $$;
            CREATE TRIGGER analyses_immutable BEFORE UPDATE OR DELETE ON analyses
                FOR EACH ROW EXECUTE FUNCTION prevent_snapshot_mutation();
            CREATE TRIGGER scenarios_immutable BEFORE UPDATE OR DELETE ON scenarios
                FOR EACH ROW EXECUTE FUNCTION prevent_snapshot_mutation();
            SQL);
        $apiRoles = DB::table('pg_roles')->whereIn('rolname', ['anon', 'authenticated'])->pluck('rolname');
        foreach (['users', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'personal_access_tokens', 'devices', 'analyses', 'scenarios'] as $table) {
            DB::statement('ALTER TABLE "'.$table.'" ENABLE ROW LEVEL SECURITY');
            DB::statement('REVOKE ALL ON TABLE "'.$table.'" FROM PUBLIC');
            foreach ($apiRoles as $role) {
                DB::statement('REVOKE ALL ON TABLE "'.$table.'" FROM "'.$role.'"');
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared('DROP TRIGGER IF EXISTS scenarios_immutable ON scenarios; DROP TRIGGER IF EXISTS analyses_immutable ON analyses; DROP FUNCTION IF EXISTS prevent_snapshot_mutation();');
        }
    }
};
