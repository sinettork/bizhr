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
            DO $bizhr$
            DECLARE
                foreign_key record;
            BEGIN
                FOR foreign_key IN
                    SELECT
                        namespace.nspname AS schema_name,
                        relation.relname AS table_name,
                        constraint_record.conname AS constraint_name,
                        columns.column_list
                    FROM pg_catalog.pg_constraint AS constraint_record
                    JOIN pg_catalog.pg_class AS relation
                        ON relation.oid = constraint_record.conrelid
                    JOIN pg_catalog.pg_namespace AS namespace
                        ON namespace.oid = relation.relnamespace
                    CROSS JOIN LATERAL (
                        SELECT string_agg(
                            format('%I', attribute.attname),
                            ', ' ORDER BY key_column.ordinality
                        ) AS column_list
                        FROM unnest(constraint_record.conkey)
                            WITH ORDINALITY AS key_column(attnum, ordinality)
                        JOIN pg_catalog.pg_attribute AS attribute
                            ON attribute.attrelid = constraint_record.conrelid
                            AND attribute.attnum = key_column.attnum
                    ) AS columns
                    WHERE constraint_record.contype = 'f'
                        AND namespace.nspname = 'public'
                        AND NOT EXISTS (
                            SELECT 1
                            FROM pg_catalog.pg_index AS index_record
                            WHERE index_record.indrelid = constraint_record.conrelid
                                AND index_record.indisvalid
                                AND (
                                    string_to_array(
                                        trim(index_record.indkey::text),
                                        ' '
                                    )::smallint[]
                                )[1:cardinality(constraint_record.conkey)]
                                    = constraint_record.conkey
                        )
                LOOP
                    EXECUTE format(
                        'CREATE INDEX %I ON %I.%I (%s)',
                        'bizhr_fk_' || substr(
                            md5(
                                foreign_key.schema_name
                                || '.'
                                || foreign_key.table_name
                                || '.'
                                || foreign_key.constraint_name
                            ),
                            1,
                            16
                        ),
                        foreign_key.schema_name,
                        foreign_key.table_name,
                        foreign_key.column_list
                    );
                END LOOP;
            END
            $bizhr$;
            SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
            DO $bizhr$
            DECLARE
                index_record record;
            BEGIN
                FOR index_record IN
                    SELECT schemaname, indexname
                    FROM pg_catalog.pg_indexes
                    WHERE schemaname = 'public'
                        AND indexname LIKE 'bizhr_fk_%'
                LOOP
                    EXECUTE format(
                        'DROP INDEX IF EXISTS %I.%I',
                        index_record.schemaname,
                        index_record.indexname
                    );
                END LOOP;
            END
            $bizhr$;
            SQL);
    }
};
