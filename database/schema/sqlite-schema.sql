CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "role" varchar not null default 'member',
  "public_id" varchar,
  "account_status" varchar not null default 'active',
  "last_login_at" datetime,
  "last_activity_at" datetime,
  "last_login_ip_masked" varchar,
  "last_login_ip_hash" varchar,
  "last_country_code" varchar,
  "last_city_name" varchar,
  "first_name" varchar,
  "last_name" varchar,
  "phone" varchar,
  "country" varchar,
  "city_or_region" varchar,
  "preferred_language" varchar not null default 'tr',
  "registration_source" varchar,
  "terms_accepted_at" datetime,
  "marketing_consent_at" datetime
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE INDEX "cache_expiration_index" on "cache"("expiration");
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE INDEX "cache_locks_expiration_index" on "cache_locks"("expiration");
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" varchar not null,
  "queue" varchar not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE INDEX "failed_jobs_connection_queue_failed_at_index" on "failed_jobs"(
  "connection",
  "queue",
  "failed_at"
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "cuisines"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "country" varchar,
  "region" varchar,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "public_id" varchar
);
CREATE UNIQUE INDEX "cuisines_name_unique" on "cuisines"("name");
CREATE UNIQUE INDEX "cuisines_slug_unique" on "cuisines"("slug");
CREATE TABLE IF NOT EXISTS "categories"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "public_id" varchar
);
CREATE UNIQUE INDEX "categories_name_unique" on "categories"("name");
CREATE UNIQUE INDEX "categories_slug_unique" on "categories"("slug");
CREATE TABLE IF NOT EXISTS "recipe_steps"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "recipe_id" integer not null,
  "position" integer not null,
  "title" varchar not null,
  "instruction" text not null,
  "duration_seconds" integer,
  "temperature_value" integer,
  "temperature_unit" varchar,
  "timer_required" tinyint(1) not null default '0',
  "is_optional" tinyint(1) not null default '0',
  "equipment" varchar,
  "chef_tip" text,
  "safety_note" text,
  "animation_key" varchar,
  "completion_hint" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("recipe_id") references "recipes"("id") on delete cascade
);
CREATE UNIQUE INDEX "recipe_steps_recipe_id_position_unique" on "recipe_steps"(
  "recipe_id",
  "position"
);
CREATE UNIQUE INDEX "recipe_steps_public_id_unique" on "recipe_steps"(
  "public_id"
);
CREATE TABLE IF NOT EXISTS "favorites"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "recipe_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("recipe_id") references "recipes"("id") on delete cascade
);
CREATE UNIQUE INDEX "favorites_user_id_recipe_id_unique" on "favorites"(
  "user_id",
  "recipe_id"
);
CREATE TABLE IF NOT EXISTS "data_sources"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "base_url" varchar,
  "type" varchar not null default 'manual',
  "license" varchar,
  "reliability_score" integer not null default '100',
  "last_success_at" datetime,
  "is_active" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "users_role_index" on "users"("role");
CREATE TABLE IF NOT EXISTS "units"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "code" varchar not null,
  "name" varchar not null,
  "symbol" varchar,
  "unit_type" varchar not null,
  "base_unit_id" integer,
  "conversion_factor" numeric,
  "precision" integer not null default '2',
  "is_metric" tinyint(1) not null default '0',
  "is_active" tinyint(1) not null default '1',
  "sort_order" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  "singular_name" varchar,
  "plural_name" varchar,
  "short_label" varchar,
  "supports_fractions" tinyint(1) not null default '0',
  "is_user_visible" tinyint(1) not null default '1',
  "is_canonical" tinyint(1) not null default '0',
  foreign key("base_unit_id") references "units"("id") on delete set null
);
CREATE UNIQUE INDEX "units_public_id_unique" on "units"("public_id");
CREATE UNIQUE INDEX "units_code_unique" on "units"("code");
CREATE TABLE IF NOT EXISTS "nutrients"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "code" varchar not null,
  "name" varchar not null,
  "unit" varchar not null,
  "category" varchar,
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  "decimal_precision" integer not null default '2',
  "daily_reference_value" numeric,
  "is_macro" tinyint(1) not null default '0',
  "is_micronutrient" tinyint(1) not null default '0',
  "sort_order" integer not null default '0'
);
CREATE UNIQUE INDEX "nutrients_public_id_unique" on "nutrients"("public_id");
CREATE UNIQUE INDEX "nutrients_code_unique" on "nutrients"("code");
CREATE TABLE IF NOT EXISTS "nutrition_facts"(
  "id" integer primary key autoincrement not null,
  "nutritionable_type" varchar not null,
  "nutritionable_id" integer not null,
  "nutrient_id" integer not null,
  "value" numeric not null,
  "basis_type" varchar not null default 'per_100g',
  "basis_amount" numeric not null default '100',
  "source_type" varchar not null default 'calculated',
  "confidence_score" integer,
  "verified_at" datetime,
  "raw_payload" text,
  "created_at" datetime,
  "updated_at" datetime,
  "source_name" varchar,
  "calculation_method" varchar,
  "origin" varchar not null default 'editorial',
  "notes" text,
  "is_active" tinyint(1) not null default '1',
  foreign key("nutrient_id") references "nutrients"("id") on delete cascade
);
CREATE INDEX "nutrition_facts_nutritionable_type_nutritionable_id_index" on "nutrition_facts"(
  "nutritionable_type",
  "nutritionable_id"
);
CREATE TABLE IF NOT EXISTS "allergens"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "code" varchar not null,
  "name" varchar not null,
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  "description" text,
  "sort_order" integer not null default '0'
);
CREATE UNIQUE INDEX "allergens_public_id_unique" on "allergens"("public_id");
CREATE UNIQUE INDEX "allergens_code_unique" on "allergens"("code");
CREATE TABLE IF NOT EXISTS "dietary_tags"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "code" varchar not null,
  "name" varchar not null,
  "description" varchar,
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  "rule_type" varchar not null default 'manual',
  "threshold_config" text,
  "sort_order" integer not null default '0'
);
CREATE UNIQUE INDEX "dietary_tags_public_id_unique" on "dietary_tags"(
  "public_id"
);
CREATE UNIQUE INDEX "dietary_tags_code_unique" on "dietary_tags"("code");
CREATE TABLE IF NOT EXISTS "import_runs"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "data_source_id" integer,
  "status" varchar not null default 'queued',
  "connector" varchar,
  "processed_count" integer not null default '0',
  "created_count" integer not null default '0',
  "updated_count" integer not null default '0',
  "failed_count" integer not null default '0',
  "summary" text,
  "started_at" datetime,
  "finished_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("data_source_id") references "data_sources"("id") on delete set null
);
CREATE UNIQUE INDEX "import_runs_public_id_unique" on "import_runs"(
  "public_id"
);
CREATE UNIQUE INDEX "users_public_id_unique" on "users"("public_id");
CREATE UNIQUE INDEX "cuisines_public_id_unique" on "cuisines"("public_id");
CREATE UNIQUE INDEX "categories_public_id_unique" on "categories"("public_id");
CREATE TABLE IF NOT EXISTS "recipe_dietary_tag"(
  "recipe_id" integer not null,
  "dietary_tag_id" integer not null,
  foreign key("recipe_id") references "recipes"("id") on delete cascade,
  foreign key("dietary_tag_id") references "dietary_tags"("id") on delete cascade,
  primary key("recipe_id", "dietary_tag_id")
);
CREATE TABLE IF NOT EXISTS "ingredient_allergens"(
  "ingredient_id" integer not null,
  "allergen_id" integer not null,
  "risk_type" varchar not null default 'contains',
  foreign key("ingredient_id") references "ingredients"("id") on delete cascade,
  foreign key("allergen_id") references "allergens"("id") on delete cascade,
  primary key("ingredient_id", "allergen_id")
);
CREATE TABLE IF NOT EXISTS "cooking_sessions"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "user_id" integer not null,
  "recipe_id" integer not null,
  "current_step_id" integer,
  "servings_snapshot" integer not null,
  "status" varchar not null default 'in_progress',
  "started_at" datetime not null,
  "completed_at" datetime,
  "last_activity_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("recipe_id") references "recipes"("id") on delete cascade,
  foreign key("current_step_id") references "recipe_steps"("id") on delete set null
);
CREATE INDEX "cooking_sessions_user_id_recipe_id_status_index" on "cooking_sessions"(
  "user_id",
  "recipe_id",
  "status"
);
CREATE UNIQUE INDEX "cooking_sessions_public_id_unique" on "cooking_sessions"(
  "public_id"
);
CREATE TABLE IF NOT EXISTS "cooking_session_steps"(
  "id" integer primary key autoincrement not null,
  "cooking_session_id" integer not null,
  "recipe_step_id" integer not null,
  "completed_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("cooking_session_id") references "cooking_sessions"("id") on delete cascade,
  foreign key("recipe_step_id") references "recipe_steps"("id") on delete cascade
);
CREATE UNIQUE INDEX "cooking_session_steps_cooking_session_id_recipe_step_id_unique" on "cooking_session_steps"(
  "cooking_session_id",
  "recipe_step_id"
);
CREATE TABLE IF NOT EXISTS "cooking_timers"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "cooking_session_id" integer not null,
  "recipe_step_id" integer,
  "label" varchar not null,
  "duration_seconds" integer not null,
  "remaining_seconds" integer,
  "started_at" datetime,
  "paused_at" datetime,
  "completed_at" datetime,
  "status" varchar not null default 'ready',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("cooking_session_id") references "cooking_sessions"("id") on delete cascade,
  foreign key("recipe_step_id") references "recipe_steps"("id") on delete set null
);
CREATE INDEX "cooking_timers_cooking_session_id_status_index" on "cooking_timers"(
  "cooking_session_id",
  "status"
);
CREATE UNIQUE INDEX "cooking_timers_public_id_unique" on "cooking_timers"(
  "public_id"
);
CREATE TABLE IF NOT EXISTS "ingredients"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "aliases" text,
  "allergens" text,
  "default_unit" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "public_id" varchar,
  "canonical_name" varchar,
  "category" varchar,
  "default_unit_id" integer,
  "is_active" tinyint(1) not null default '1',
  "is_allergen" tinyint(1) not null default '0',
  "is_perishable" tinyint(1) not null default '0',
  "typical_shelf_life_days" integer,
  "storage_type" varchar,
  "notes" text,
  "icon_key" varchar,
  "icon_label" varchar,
  "icon_status" varchar not null default 'missing',
  foreign key("default_unit_id") references "units"("id") on delete set null
);
CREATE UNIQUE INDEX "ingredients_name_unique" on "ingredients"("name");
CREATE UNIQUE INDEX "ingredients_public_id_unique" on "ingredients"(
  "public_id"
);
CREATE UNIQUE INDEX "ingredients_slug_unique" on "ingredients"("slug");
CREATE TABLE IF NOT EXISTS "ingredient_aliases"(
  "id" integer primary key autoincrement not null,
  "ingredient_id" integer not null,
  "alias" varchar not null,
  "normalized_alias" varchar not null,
  "locale" varchar not null default 'tr',
  "source_type" varchar not null default 'editorial',
  "confidence_score" numeric not null default '100',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("ingredient_id") references "ingredients"("id") on delete cascade
);
CREATE UNIQUE INDEX "ing_alias_scope_uq" on "ingredient_aliases"(
  "ingredient_id",
  "normalized_alias",
  "locale"
);
CREATE INDEX "ing_alias_lookup_ix" on "ingredient_aliases"(
  "normalized_alias",
  "locale"
);
CREATE TABLE IF NOT EXISTS "ingredient_substitutions"(
  "id" integer primary key autoincrement not null,
  "ingredient_id" integer not null,
  "substitute_ingredient_id" integer not null,
  "substitution_type" varchar not null,
  "ratio" numeric not null default '1',
  "unit_compatibility" varchar,
  "context" varchar,
  "notes" text,
  "confidence_score" numeric not null default '100',
  "is_verified" tinyint(1) not null default '0',
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("ingredient_id") references "ingredients"("id") on delete cascade,
  foreign key("substitute_ingredient_id") references "ingredients"("id") on delete cascade
);
CREATE UNIQUE INDEX "ing_sub_type_uq" on "ingredient_substitutions"(
  "ingredient_id",
  "substitute_ingredient_id",
  "substitution_type"
);
CREATE TABLE IF NOT EXISTS "pantry_items"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "ingredient_id" integer not null,
  "quantity" numeric,
  "unit" varchar,
  "expires_at" date,
  "created_at" datetime,
  "updated_at" datetime,
  "unit_id" integer,
  "opened_at" datetime,
  "storage_location" varchar not null default 'pantry',
  "notes" text,
  "is_available" tinyint(1) not null default '1',
  "canonical_quantity" numeric,
  "canonical_unit" varchar,
  "conversion_confidence" varchar not null default 'unknown',
  foreign key("ingredient_id") references ingredients("id") on delete cascade on update no action,
  foreign key("user_id") references users("id") on delete cascade on update no action,
  foreign key("unit_id") references "units"("id") on delete set null
);
CREATE UNIQUE INDEX "pantry_items_user_id_ingredient_id_unique" on "pantry_items"(
  "user_id",
  "ingredient_id"
);
CREATE UNIQUE INDEX "nutrition_fact_source_unique" on "nutrition_facts"(
  "nutritionable_type",
  "nutritionable_id",
  "nutrient_id",
  "basis_type",
  "basis_amount",
  "source_type"
);
CREATE TABLE IF NOT EXISTS "ingredient_allergen_details"(
  "id" integer primary key autoincrement not null,
  "ingredient_id" integer not null,
  "allergen_id" integer not null,
  "relation_type" varchar not null default 'contains',
  "confidence_score" integer not null default '100',
  "source_type" varchar not null default 'editorial',
  "is_verified" tinyint(1) not null default '0',
  "notes" text,
  "created_at" datetime,
  "updated_at" datetime,
  "source_name" varchar,
  "is_active" tinyint(1) not null default '1',
  foreign key("ingredient_id") references "ingredients"("id") on delete cascade,
  foreign key("allergen_id") references "allergens"("id") on delete cascade
);
CREATE UNIQUE INDEX "ingredient_allergen_details_ingredient_id_allergen_id_unique" on "ingredient_allergen_details"(
  "ingredient_id",
  "allergen_id"
);
CREATE TABLE IF NOT EXISTS "recipe_nutrition_snapshots"(
  "id" integer primary key autoincrement not null,
  "recipe_id" integer not null,
  "servings" integer not null,
  "payload" text not null,
  "completeness_percentage" integer not null,
  "confidence_score" integer not null,
  "source_version" varchar not null default 'v1',
  "calculated_at" datetime not null,
  "invalidated_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("recipe_id") references "recipes"("id") on delete cascade
);
CREATE UNIQUE INDEX "rns_recipe_servings_source_uq" on "recipe_nutrition_snapshots"(
  "recipe_id",
  "servings",
  "source_version"
);
CREATE TABLE IF NOT EXISTS "recipe_allergens"(
  "recipe_id" integer not null,
  "allergen_id" integer not null,
  "risk_type" varchar not null default('contains'),
  "derived_from" varchar,
  "source_type" varchar not null default 'editorial',
  "is_verified" tinyint(1) not null default '0',
  "verified_at" datetime,
  "updated_by" integer,
  "updated_at" datetime,
  "source_name" varchar,
  "notes" text,
  "is_active" tinyint(1) not null default '1',
  foreign key("allergen_id") references allergens("id") on delete cascade on update no action,
  foreign key("recipe_id") references recipes("id") on delete cascade on update no action,
  foreign key("updated_by") references "users"("id") on delete set null,
  primary key("recipe_id", "allergen_id")
);
CREATE TABLE IF NOT EXISTS "audit_logs"(
  "id" integer primary key autoincrement not null,
  "user_id" integer,
  "action" varchar not null,
  "auditable_type" varchar,
  "auditable_id" integer,
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete set null
);
CREATE INDEX "audit_logs_auditable_type_auditable_id_index" on "audit_logs"(
  "auditable_type",
  "auditable_id"
);
CREATE INDEX "audit_logs_action_created_at_index" on "audit_logs"(
  "action",
  "created_at"
);
CREATE TABLE IF NOT EXISTS "nutrition_settings"(
  "id" integer primary key autoincrement not null,
  "key" varchar not null,
  "value" numeric not null,
  "unit" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "nutrition_settings_key_unique" on "nutrition_settings"(
  "key"
);
CREATE TABLE IF NOT EXISTS "nutrition_recalculation_runs"(
  "id" integer primary key autoincrement not null,
  "recipe_id" integer not null,
  "triggered_by" integer,
  "status" varchar not null default 'pending',
  "attempt" integer not null default '1',
  "started_at" datetime,
  "finished_at" datetime,
  "error_code" varchar,
  "error_message" varchar,
  "previous_confidence" integer,
  "new_confidence" integer,
  "previous_completeness" integer,
  "new_completeness" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("recipe_id") references "recipes"("id") on delete cascade,
  foreign key("triggered_by") references "users"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "data_quality_issues"(
  "id" integer primary key autoincrement not null,
  "issue_type" varchar not null,
  "severity" varchar not null,
  "entity_type" varchar not null,
  "entity_public_id" varchar,
  "message" varchar not null,
  "detected_at" datetime not null,
  "resolved_at" datetime,
  "resolution_status" varchar not null default 'open',
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  "first_detected_at" datetime,
  "last_detected_at" datetime,
  "category" varchar,
  "suggested_action" varchar
);
CREATE INDEX "data_quality_issues_resolution_status_severity_index" on "data_quality_issues"(
  "resolution_status",
  "severity"
);
CREATE INDEX "data_quality_issues_issue_type_entity_public_id_index" on "data_quality_issues"(
  "issue_type",
  "entity_public_id"
);
CREATE INDEX "pantry_available_expiry_index" on "pantry_items"(
  "user_id",
  "is_available",
  "expires_at"
);
CREATE INDEX "substitutions_active_lookup_index" on "ingredient_substitutions"(
  "ingredient_id",
  "is_active"
);
CREATE INDEX "ingredients_canonical_name_index" on "ingredients"(
  "canonical_name"
);
CREATE INDEX "nutrition_facts_lookup_index" on "nutrition_facts"(
  "nutritionable_type",
  "nutritionable_id",
  "nutrient_id",
  "basis_type"
);
CREATE INDEX "nutrition_snapshots_active_index" on "recipe_nutrition_snapshots"(
  "recipe_id",
  "invalidated_at"
);
CREATE INDEX "ingredient_allergens_active_index" on "ingredient_allergen_details"(
  "ingredient_id",
  "is_active"
);
CREATE INDEX "quality_issue_open_lookup_index" on "data_quality_issues"(
  "resolution_status",
  "entity_type",
  "entity_public_id"
);
CREATE INDEX "jobs_queue_reserved_index" on "jobs"("queue", "reserved_at");
CREATE INDEX "failed_jobs_failed_at_index" on "failed_jobs"("failed_at");
CREATE INDEX "audit_user_created_index" on "audit_logs"(
  "user_id",
  "created_at"
);
CREATE TABLE IF NOT EXISTS "system_events"(
  "id" integer primary key autoincrement not null,
  "event_type" varchar not null,
  "severity" varchar not null,
  "message" varchar not null,
  "correlation_id" varchar,
  "metadata" text,
  "resolved_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "system_events_severity_created_at_index" on "system_events"(
  "severity",
  "created_at"
);
CREATE TABLE IF NOT EXISTS "system_metrics"(
  "id" integer primary key autoincrement not null,
  "metric" varchar not null,
  "dimension" varchar not null default 'all',
  "value" numeric not null,
  "recorded_at" datetime not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "system_metrics_metric_dimension_unique" on "system_metrics"(
  "metric",
  "dimension"
);
CREATE TABLE IF NOT EXISTS "restore_histories"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "backup_id" varchar not null,
  "target" varchar not null,
  "triggered_by_user_id" integer,
  "triggered_by_type" varchar not null default 'system',
  "started_at" datetime not null,
  "finished_at" datetime,
  "status" varchar not null,
  "checksum_verified" tinyint(1) not null default '0',
  "database_opened" tinyint(1) not null default '0',
  "migration_table_verified" tinyint(1) not null default '0',
  "user_count_before" integer,
  "user_count_after" integer,
  "recipe_count_before" integer,
  "recipe_count_after" integer,
  "ingredient_count_before" integer,
  "ingredient_count_after" integer,
  "relations_verified" tinyint(1) not null default '0',
  "health_check_status" varchar,
  "safe_error_code" varchar,
  "safe_error_message" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("triggered_by_user_id") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "restore_histories_public_id_unique" on "restore_histories"(
  "public_id"
);
CREATE TABLE IF NOT EXISTS "recipe_ingredient_groups"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "recipe_id" integer not null,
  "name" varchar not null,
  "sort_order" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("recipe_id") references "recipes"("id") on delete cascade
);
CREATE UNIQUE INDEX "recipe_ingredient_groups_recipe_id_name_unique" on "recipe_ingredient_groups"(
  "recipe_id",
  "name"
);
CREATE INDEX "recipe_ingredient_groups_recipe_id_sort_order_index" on "recipe_ingredient_groups"(
  "recipe_id",
  "sort_order"
);
CREATE UNIQUE INDEX "recipe_ingredient_groups_public_id_unique" on "recipe_ingredient_groups"(
  "public_id"
);
CREATE TABLE IF NOT EXISTS "content_sources"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "entity_type" varchar not null,
  "entity_public_id" varchar,
  "source_type" varchar not null,
  "source_name" varchar not null,
  "source_url" varchar,
  "source_title" varchar,
  "publisher" varchar,
  "author" varchar,
  "published_at" datetime,
  "accessed_at" datetime,
  "license" varchar,
  "attribution_required" tinyint(1) not null default '0',
  "language" varchar not null default 'tr',
  "country" varchar,
  "notes" text,
  "verification_status" varchar not null default 'unreviewed',
  "confidence" integer not null default '0',
  "reviewed_at" datetime,
  "reviewed_by_user_id" integer,
  "last_checked_at" datetime,
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("reviewed_by_user_id") references "users"("id") on delete set null
);
CREATE INDEX "content_sources_entity_active_index" on "content_sources"(
  "entity_type",
  "entity_public_id",
  "is_active"
);
CREATE UNIQUE INDEX "content_sources_public_id_unique" on "content_sources"(
  "public_id"
);
CREATE INDEX "content_sources_entity_type_index" on "content_sources"(
  "entity_type"
);
CREATE INDEX "content_sources_entity_public_id_index" on "content_sources"(
  "entity_public_id"
);
CREATE INDEX "content_sources_source_type_index" on "content_sources"(
  "source_type"
);
CREATE INDEX "content_sources_verification_status_index" on "content_sources"(
  "verification_status"
);
CREATE INDEX "content_sources_is_active_index" on "content_sources"(
  "is_active"
);
CREATE TABLE IF NOT EXISTS "recipe_provenances"(
  "id" integer primary key autoincrement not null,
  "recipe_id" integer not null,
  "data_source_id" integer,
  "field_name" varchar,
  "source_type" varchar not null,
  "license" varchar,
  "source_url" varchar,
  "content_hash" varchar,
  "retrieved_at" datetime,
  "raw_payload" text,
  "is_ai_generated" tinyint(1) not null default('0'),
  "is_user_generated" tinyint(1) not null default('0'),
  "created_at" datetime,
  "updated_at" datetime,
  "public_id" varchar,
  "content_source_id" integer,
  "verification_status" varchar not null default 'unreviewed',
  "confidence" integer not null default '0',
  "reviewed_at" datetime,
  "reviewed_by_user_id" integer,
  "last_checked_at" datetime,
  "is_active" tinyint(1) not null default '1',
  foreign key("data_source_id") references data_sources("id") on delete set null on update no action,
  foreign key("recipe_id") references recipes("id") on delete cascade on update no action,
  foreign key("content_source_id") references "content_sources"("id") on delete set null,
  foreign key("reviewed_by_user_id") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "recipe_provenances_public_id_unique" on "recipe_provenances"(
  "public_id"
);
CREATE INDEX "recipe_provenances_verification_status_index" on "recipe_provenances"(
  "verification_status"
);
CREATE INDEX "recipe_provenances_is_active_index" on "recipe_provenances"(
  "is_active"
);
CREATE INDEX "ingredients_icon_key_index" on "ingredients"("icon_key");
CREATE INDEX "ingredients_icon_status_index" on "ingredients"("icon_status");
CREATE INDEX "data_quality_issues_category_index" on "data_quality_issues"(
  "category"
);
CREATE TABLE IF NOT EXISTS "ai_image_generations"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "recipe_id" integer not null,
  "media_id" integer,
  "provider" varchar not null,
  "model" varchar,
  "prompt" text not null,
  "negative_prompt" text,
  "prompt_version" varchar not null,
  "aspect_ratio" varchar not null default '4:3',
  "variation_number" integer not null default '1',
  "status" varchar not null default 'planned',
  "estimated_cost_cents" integer not null default '0',
  "actual_cost_cents" integer,
  "error_code" varchar,
  "error_message" varchar,
  "review_notes" text,
  "requested_at" datetime,
  "generated_at" datetime,
  "reviewed_at" datetime,
  "reviewed_by_user_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  "generation_scope" varchar not null default 'pilot',
  "response_status" integer,
  "provider_request_id" varchar,
  "response_content_type" varchar,
  "response_body_present" tinyint(1) not null default '0',
  "candidate_count" integer not null default '0',
  "part_count" integer not null default '0',
  "image_part_count" integer not null default '0',
  "text_part_count" integer not null default '0',
  "finish_reason" varchar,
  "moderation_status" varchar,
  "request_bytes" integer not null default '0',
  "response_bytes" integer not null default '0',
  "provider_latency_ms" integer,
  "billing_status" varchar not null default 'unknown',
  "parser_result" varchar,
  "diagnostic_snapshot" text,
  "status_transitions" text,
  foreign key("recipe_id") references "recipes"("id") on delete cascade,
  foreign key("media_id") references "media"("id") on delete set null,
  foreign key("reviewed_by_user_id") references "users"("id") on delete set null
);
CREATE INDEX "aig_recipe_fk_ix" on "ai_image_generations"("recipe_id");
CREATE INDEX "ai_image_generations_provider_status_index" on "ai_image_generations"(
  "provider",
  "status"
);
CREATE UNIQUE INDEX "ai_image_generations_public_id_unique" on "ai_image_generations"(
  "public_id"
);
CREATE INDEX "ai_image_generations_status_index" on "ai_image_generations"(
  "status"
);
CREATE INDEX "ai_image_generations_generation_scope_index" on "ai_image_generations"(
  "generation_scope"
);
CREATE UNIQUE INDEX "ai_image_generation_recipe_provider_variation_scope_unique" on "ai_image_generations"(
  "recipe_id",
  "provider",
  "variation_number",
  "generation_scope"
);
CREATE TABLE IF NOT EXISTS "blog_categories"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "blog_categories_public_id_unique" on "blog_categories"(
  "public_id"
);
CREATE UNIQUE INDEX "blog_categories_slug_unique" on "blog_categories"("slug");
CREATE TABLE IF NOT EXISTS "blog_tags"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "name" varchar not null,
  "slug" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "blog_tags_public_id_unique" on "blog_tags"("public_id");
CREATE UNIQUE INDEX "blog_tags_slug_unique" on "blog_tags"("slug");
CREATE TABLE IF NOT EXISTS "blog_post_blog_tag"(
  "blog_post_id" integer not null,
  "blog_tag_id" integer not null,
  foreign key("blog_post_id") references "blog_posts"("id") on delete cascade,
  foreign key("blog_tag_id") references "blog_tags"("id") on delete cascade,
  primary key("blog_post_id", "blog_tag_id")
);
CREATE TABLE IF NOT EXISTS "blog_post_recipe"(
  "blog_post_id" integer not null,
  "recipe_id" integer not null,
  foreign key("blog_post_id") references "blog_posts"("id") on delete cascade,
  foreign key("recipe_id") references "recipes"("id") on delete cascade,
  primary key("blog_post_id", "recipe_id")
);
CREATE INDEX "users_account_status_index" on "users"("account_status");
CREATE INDEX "users_last_login_at_index" on "users"("last_login_at");
CREATE INDEX "users_last_activity_at_index" on "users"("last_activity_at");
CREATE INDEX "users_last_login_ip_hash_index" on "users"("last_login_ip_hash");
CREATE INDEX "users_last_country_code_index" on "users"("last_country_code");
CREATE TABLE IF NOT EXISTS "user_activities"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "user_id" integer not null,
  "event_type" varchar not null,
  "route_name" varchar,
  "ip_masked" varchar,
  "ip_hash" varchar,
  "device_summary" varchar,
  "status" varchar not null default 'success',
  "metadata" text,
  "occurred_at" datetime not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE INDEX "user_activities_user_id_occurred_at_index" on "user_activities"(
  "user_id",
  "occurred_at"
);
CREATE UNIQUE INDEX "user_activities_public_id_unique" on "user_activities"(
  "public_id"
);
CREATE INDEX "user_activities_event_type_index" on "user_activities"(
  "event_type"
);
CREATE INDEX "user_activities_route_name_index" on "user_activities"(
  "route_name"
);
CREATE INDEX "user_activities_ip_hash_index" on "user_activities"("ip_hash");
CREATE INDEX "user_activities_status_index" on "user_activities"("status");
CREATE INDEX "user_activities_occurred_at_index" on "user_activities"(
  "occurred_at"
);
CREATE TABLE IF NOT EXISTS "seo_metadata"(
  "id" integer primary key autoincrement not null,
  "seoable_type" varchar not null,
  "seoable_id" integer not null,
  "meta_title" varchar,
  "meta_description" varchar,
  "canonical_override" varchar,
  "robots" varchar not null default 'index,follow',
  "og_title" varchar,
  "og_description" varchar,
  "og_media_public_id" varchar,
  "x_title" varchar,
  "x_description" varchar,
  "x_media_public_id" varchar,
  "focus_topic" varchar,
  "primary_query" varchar,
  "search_intent" varchar,
  "index_status" varchar not null default 'pending',
  "schema_status" varchar not null default 'pending',
  "quality_score" integer,
  "last_audited_at" datetime,
  "last_refreshed_at" datetime,
  "reviewed_by" integer,
  "reviewed_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("reviewed_by") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "seo_metadata_seoable_type_seoable_id_unique" on "seo_metadata"(
  "seoable_type",
  "seoable_id"
);
CREATE TABLE IF NOT EXISTS "keyword_topics"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "primary_keyword" varchar not null,
  "normalized_keyword" varchar not null,
  "locale" varchar not null default 'tr',
  "country" varchar not null default 'CY',
  "intent" varchar not null,
  "topic_cluster" varchar not null,
  "parent_topic_id" integer,
  "priority" integer not null default '50',
  "difficulty_source" varchar,
  "volume_source" varchar,
  "trend_direction" varchar,
  "data_confidence" numeric not null default '0',
  "status" varchar not null default 'idea',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("parent_topic_id") references "keyword_topics"("id") on delete set null
);
CREATE UNIQUE INDEX "keyword_topics_normalized_keyword_locale_country_unique" on "keyword_topics"(
  "normalized_keyword",
  "locale",
  "country"
);
CREATE UNIQUE INDEX "keyword_topics_public_id_unique" on "keyword_topics"(
  "public_id"
);
CREATE TABLE IF NOT EXISTS "content_briefs"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "keyword_topic_id" integer,
  "proposed_title" varchar not null,
  "target_audience" varchar,
  "search_intent" varchar not null,
  "user_question" text,
  "direct_answer" text,
  "required_sections" text,
  "required_facts" text,
  "related_recipe_public_ids" text,
  "internal_links" text,
  "prohibited_claims" text,
  "source_notes" text,
  "freshness_requirement" varchar,
  "risk_level" varchar not null default 'low',
  "status" varchar not null default 'idea',
  "created_by" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("keyword_topic_id") references "keyword_topics"("id") on delete set null,
  foreign key("created_by") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "content_briefs_public_id_unique" on "content_briefs"(
  "public_id"
);
CREATE TABLE IF NOT EXISTS "content_opportunities"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "kind" varchar not null,
  "title" varchar not null,
  "normalized_key" varchar not null,
  "search_intent" varchar,
  "topic_cluster" varchar,
  "evidence" text,
  "related_public_ids" text,
  "risk_level" varchar not null default 'low',
  "priority" integer not null default '50',
  "status" varchar not null default 'open',
  "reviewed_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "content_opportunities_status_priority_index" on "content_opportunities"(
  "status",
  "priority"
);
CREATE UNIQUE INDEX "content_opportunities_public_id_unique" on "content_opportunities"(
  "public_id"
);
CREATE UNIQUE INDEX "content_opportunities_normalized_key_unique" on "content_opportunities"(
  "normalized_key"
);
CREATE TABLE IF NOT EXISTS "zero_result_searches"(
  "id" integer primary key autoincrement not null,
  "normalized_query" varchar not null,
  "locale" varchar not null default 'tr',
  "search_count" integer not null default '0',
  "last_searched_at" datetime not null default CURRENT_TIMESTAMP,
  "suggested_action" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "zero_result_searches_normalized_query_unique" on "zero_result_searches"(
  "normalized_query"
);
CREATE TABLE IF NOT EXISTS "seo_issues"(
  "id" integer primary key autoincrement not null,
  "fingerprint" varchar not null,
  "category" varchar not null,
  "entity_type" varchar,
  "entity_public_id" varchar,
  "url" varchar,
  "severity" varchar not null,
  "message" varchar not null,
  "safe_metadata" text,
  "first_detected_at" datetime not null default CURRENT_TIMESTAMP,
  "last_detected_at" datetime not null default CURRENT_TIMESTAMP,
  "resolved_at" datetime,
  "reopened_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "seo_issues_severity_resolved_at_index" on "seo_issues"(
  "severity",
  "resolved_at"
);
CREATE INDEX "seo_issues_entity_type_entity_public_id_index" on "seo_issues"(
  "entity_type",
  "entity_public_id"
);
CREATE UNIQUE INDEX "seo_issues_fingerprint_unique" on "seo_issues"(
  "fingerprint"
);
CREATE TABLE IF NOT EXISTS "search_performance_daily"(
  "id" integer primary key autoincrement not null,
  "performance_date" date not null,
  "page" varchar not null,
  "query_hash" varchar,
  "country" varchar,
  "device" varchar,
  "clicks" integer not null default '0',
  "impressions" integer not null default '0',
  "ctr" numeric not null default '0',
  "average_position" numeric,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "seo_performance_daily_unique" on "search_performance_daily"(
  "performance_date",
  "page",
  "query_hash",
  "country",
  "device"
);
CREATE TABLE IF NOT EXISTS "indexing_events"(
  "id" integer primary key autoincrement not null,
  "url" varchar not null,
  "event_type" varchar not null,
  "provider" varchar not null,
  "status" varchar not null,
  "safe_error_code" varchar,
  "submitted_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "indexing_events_provider_status_index" on "indexing_events"(
  "provider",
  "status"
);
CREATE TABLE IF NOT EXISTS "content_revisions"(
  "id" integer primary key autoincrement not null,
  "blog_post_id" integer not null,
  "revision_number" integer not null,
  "reason" varchar not null,
  "previous_body" text,
  "proposed_body" text,
  "changed_seo_fields" text,
  "created_by" integer,
  "reviewed_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("blog_post_id") references "blog_posts"("id") on delete cascade,
  foreign key("created_by") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "content_revisions_blog_post_id_revision_number_unique" on "content_revisions"(
  "blog_post_id",
  "revision_number"
);
CREATE TABLE IF NOT EXISTS "social_distribution_drafts"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "blog_post_id" integer,
  "channel" varchar not null,
  "body" text not null,
  "safe_metadata" text,
  "status" varchar not null default 'draft',
  "approved_at" datetime,
  "published_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("blog_post_id") references "blog_posts"("id") on delete set null
);
CREATE INDEX "social_distribution_drafts_channel_status_index" on "social_distribution_drafts"(
  "channel",
  "status"
);
CREATE UNIQUE INDEX "social_distribution_drafts_public_id_unique" on "social_distribution_drafts"(
  "public_id"
);
CREATE TABLE IF NOT EXISTS "recipe_media_candidates"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "recipe_id" integer not null,
  "provider" varchar not null default 'pexels',
  "external_photo_id" varchar not null,
  "review_batch" integer,
  "rank" integer,
  "review_score" integer not null default '0',
  "score_breakdown" text,
  "review_warnings" text,
  "status" varchar not null default 'pending_review',
  "rejection_code" varchar,
  "rejection_reason" text,
  "photographer" varchar,
  "photographer_url" varchar,
  "pexels_page_url" varchar,
  "source_url" varchar,
  "alt_text" varchar,
  "width" integer,
  "height" integer,
  "provider_metadata" text,
  "scanned_at" datetime,
  "selected_at" datetime,
  "selected_by_user_id" integer,
  "media_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  "description" text,
  "alt_description" text,
  "external_page_url" text,
  "download_location" text,
  "small_url" text,
  "thumb_url" text,
  "orientation" varchar,
  "color" varchar,
  "blur_hash" varchar,
  "utm_source" varchar,
  "utm_medium" varchar,
  "download_tracked_at" datetime,
  "tracking_status" varchar,
  "tracking_error_code" varchar,
  "fidelity_status" varchar,
  "fidelity_confidence" integer,
  "fidelity_reasons" text,
  "license_status" varchar not null default 'pending',
  "attribution_status" varchar not null default 'pending',
  "fidelity_scanned_at" datetime,
  foreign key("recipe_id") references "recipes"("id") on delete cascade,
  foreign key("selected_by_user_id") references "users"("id") on delete set null,
  foreign key("media_id") references "media"("id") on delete set null
);
CREATE UNIQUE INDEX "rmc_recipe_provider_photo_uq" on "recipe_media_candidates"(
  "recipe_id",
  "provider",
  "external_photo_id"
);
CREATE INDEX "recipe_media_candidates_review_batch_status_index" on "recipe_media_candidates"(
  "review_batch",
  "status"
);
CREATE INDEX "recipe_media_candidates_provider_external_photo_id_index" on "recipe_media_candidates"(
  "provider",
  "external_photo_id"
);
CREATE UNIQUE INDEX "recipe_media_candidates_public_id_unique" on "recipe_media_candidates"(
  "public_id"
);
CREATE TABLE IF NOT EXISTS "content_factory_batches"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "content_type" varchar not null,
  "sequence" integer not null,
  "status" varchar not null default 'planned',
  "planned_items" integer not null default '0',
  "created_items" integer not null default '0',
  "technical_passed_items" integer not null default '0',
  "failed_items" integer not null default '0',
  "distribution" text,
  "started_at" datetime,
  "finished_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "content_factory_batches_content_type_sequence_unique" on "content_factory_batches"(
  "content_type",
  "sequence"
);
CREATE UNIQUE INDEX "content_factory_batches_public_id_unique" on "content_factory_batches"(
  "public_id"
);
CREATE INDEX "content_factory_batches_content_type_index" on "content_factory_batches"(
  "content_type"
);
CREATE INDEX "content_factory_batches_status_index" on "content_factory_batches"(
  "status"
);
CREATE TABLE IF NOT EXISTS "content_factory_reviews"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "content_factory_batch_id" integer,
  "content_type" varchar not null,
  "entity_type" varchar not null,
  "entity_public_id" varchar not null,
  "stage" varchar not null,
  "status" varchar not null default 'pending',
  "fact_check_required" tinyint(1) not null default '0',
  "findings" text,
  "reviewed_by_user_id" integer,
  "reviewed_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  "editorial_comment" text,
  foreign key("content_factory_batch_id") references "content_factory_batches"("id") on delete set null,
  foreign key("reviewed_by_user_id") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "cfr_entity_stage_uq" on "content_factory_reviews"(
  "entity_type",
  "entity_public_id",
  "stage"
);
CREATE UNIQUE INDEX "content_factory_reviews_public_id_unique" on "content_factory_reviews"(
  "public_id"
);
CREATE INDEX "content_factory_reviews_content_type_index" on "content_factory_reviews"(
  "content_type"
);
CREATE INDEX "content_factory_reviews_entity_public_id_index" on "content_factory_reviews"(
  "entity_public_id"
);
CREATE INDEX "content_factory_reviews_stage_index" on "content_factory_reviews"(
  "stage"
);
CREATE INDEX "content_factory_reviews_status_index" on "content_factory_reviews"(
  "status"
);
CREATE TABLE IF NOT EXISTS "content_topic_clusters"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "name" varchar not null,
  "slug" varchar not null,
  "search_intent" varchar not null,
  "pillar_title" varchar not null,
  "review_interval" varchar not null default '6 ay',
  "internal_link_plan" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "content_topic_clusters_public_id_unique" on "content_topic_clusters"(
  "public_id"
);
CREATE UNIQUE INDEX "content_topic_clusters_slug_unique" on "content_topic_clusters"(
  "slug"
);
CREATE TABLE IF NOT EXISTS "recipes"(
  "id" integer primary key autoincrement not null,
  "cuisine_id" integer,
  "category_id" integer,
  "title" varchar not null,
  "slug" varchar not null,
  "excerpt" text,
  "description" text,
  "country" varchar,
  "region" varchar,
  "prep_minutes" integer not null default('0'),
  "cook_minutes" integer not null default('0'),
  "rest_minutes" integer not null default('0'),
  "servings" integer not null default('2'),
  "difficulty" varchar not null default('Kolay'),
  "dietary_tags" text,
  "allergens" text,
  "nutrition" text,
  "storage_notes" text,
  "source_name" varchar,
  "source_url" varchar,
  "reliability_score" integer not null default('100'),
  "status" varchar not null default('published'),
  "is_featured" tinyint(1) not null default('0'),
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "public_id" varchar,
  "data_source_id" integer,
  "external_id" varchar,
  "content_origin" varchar not null default('editorial'),
  "original_hash" varchar,
  "first_imported_at" datetime,
  "last_verified_at" datetime,
  "verification_status" varchar not null default('needs_review'),
  "editorial_status" varchar not null default('draft'),
  "completeness_score" integer not null default('0'),
  "completeness_status" varchar not null default('incomplete'),
  "editorial_reviewed_at" datetime,
  "editorial_reviewed_by_user_id" integer,
  "published_at" datetime,
  "equipment" text,
  "tips" text,
  "common_mistakes" text,
  "serving_suggestions" text,
  "reheating_notes" text,
  "freezing_notes" text,
  "cultural_notes" text,
  "seasonality" text,
  "image_status" varchar not null default('missing'),
  "image_license" varchar,
  "image_attribution" varchar,
  "image_reviewed_at" datetime,
  "content_factory_batch_id" integer,
  "content_factory_state" varchar,
  "fact_check_required" tinyint(1) not null default '0',
  "human_approved_by_user_id" integer,
  "human_approved_at" datetime,
  "content_factory_readiness_score" integer,
  "content_factory_rule_version" varchar,
  "content_factory_checked_at" datetime,
  "content_factory_metadata" text,
  foreign key("editorial_reviewed_by_user_id") references users("id") on delete set null on update no action,
  foreign key("category_id") references categories("id") on delete set null on update no action,
  foreign key("cuisine_id") references cuisines("id") on delete set null on update no action,
  foreign key("data_source_id") references data_sources("id") on delete set null on update no action,
  foreign key("content_factory_batch_id") references "content_factory_batches"("id") on delete set null,
  foreign key("human_approved_by_user_id") references "users"("id") on delete set null
);
CREATE INDEX "recipes_completeness_status_index" on "recipes"(
  "completeness_status"
);
CREATE UNIQUE INDEX "recipes_data_source_id_external_id_unique" on "recipes"(
  "data_source_id",
  "external_id"
);
CREATE INDEX "recipes_editorial_status_index" on "recipes"("editorial_status");
CREATE INDEX "recipes_image_status_index" on "recipes"("image_status");
CREATE INDEX "recipes_listing_index" on "recipes"(
  "status",
  "is_featured",
  "created_at"
);
CREATE UNIQUE INDEX "recipes_public_id_unique" on "recipes"("public_id");
CREATE UNIQUE INDEX "recipes_slug_unique" on "recipes"("slug");
CREATE INDEX "recipes_status_category_index" on "recipes"(
  "status",
  "category_id"
);
CREATE INDEX "recipes_status_cuisine_index" on "recipes"(
  "status",
  "cuisine_id"
);
CREATE INDEX "recipes_status_difficulty_index" on "recipes"(
  "status",
  "difficulty"
);
CREATE INDEX "recipes_verification_status_index" on "recipes"(
  "verification_status"
);
CREATE INDEX "recipes_content_factory_state_index" on "recipes"(
  "content_factory_state"
);
CREATE TABLE IF NOT EXISTS "blog_posts"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "author_id" integer,
  "blog_category_id" integer,
  "hero_media_id" integer,
  "title" varchar not null,
  "slug" varchar not null,
  "excerpt" text not null,
  "body" text not null,
  "status" varchar not null default('draft'),
  "scheduled_at" datetime,
  "published_at" datetime,
  "meta_title" varchar,
  "meta_description" varchar,
  "canonical_url" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "content_brief_id" integer,
  "content_purpose" varchar,
  "search_intent" varchar,
  "primary_keyword" varchar,
  "secondary_keywords" text,
  "source_notes" text,
  "faq_blocks" text,
  "fact_check_status" varchar not null default('pending'),
  "editorial_status" varchar not null default('draft'),
  "originality_status" varchar not null default('pending'),
  "freshness_class" varchar,
  "review_due_at" datetime,
  "last_refreshed_at" datetime,
  "content_factory_batch_id" integer,
  "content_factory_state" varchar,
  "fact_check_required" tinyint(1) not null default '0',
  "human_approved_by_user_id" integer,
  "human_approved_at" datetime,
  "content_factory_readiness_score" integer,
  "content_factory_rule_version" varchar,
  "content_factory_checked_at" datetime,
  "content_factory_metadata" text,
  foreign key("content_brief_id") references content_briefs("id") on delete set null on update no action,
  foreign key("author_id") references users("id") on delete set null on update no action,
  foreign key("blog_category_id") references blog_categories("id") on delete set null on update no action,
  foreign key("hero_media_id") references media("id") on delete set null on update no action,
  foreign key("content_factory_batch_id") references "content_factory_batches"("id") on delete set null,
  foreign key("human_approved_by_user_id") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "blog_posts_public_id_unique" on "blog_posts"("public_id");
CREATE INDEX "blog_posts_published_at_index" on "blog_posts"("published_at");
CREATE INDEX "blog_posts_scheduled_at_index" on "blog_posts"("scheduled_at");
CREATE UNIQUE INDEX "blog_posts_slug_unique" on "blog_posts"("slug");
CREATE INDEX "blog_posts_status_index" on "blog_posts"("status");
CREATE INDEX "blog_posts_status_published_at_index" on "blog_posts"(
  "status",
  "published_at"
);
CREATE INDEX "blog_posts_content_factory_state_index" on "blog_posts"(
  "content_factory_state"
);
CREATE INDEX "recipes_content_factory_readiness_score_index" on "recipes"(
  "content_factory_readiness_score"
);
CREATE INDEX "blog_posts_content_factory_readiness_score_index" on "blog_posts"(
  "content_factory_readiness_score"
);
CREATE TABLE IF NOT EXISTS "content_factory_issues"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "content_factory_batch_id" integer,
  "entity_type" varchar not null,
  "entity_public_id" varchar not null,
  "fingerprint" varchar not null,
  "issue_type" varchar not null,
  "field_name" varchar,
  "claim" text,
  "risk_level" varchar not null default 'medium',
  "source_requirement" varchar,
  "confidence_score" integer not null default '0',
  "status" varchar not null default 'pending',
  "reviewed_by_user_id" integer,
  "resolution_note" text,
  "resolved_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  "category" varchar,
  "risk_domain" varchar,
  "priority_score" integer not null default '0',
  "resolution_mode" varchar not null default 'human_required',
  "evidence_pack" text,
  "group_key" varchar,
  "triaged_at" datetime,
  foreign key("content_factory_batch_id") references "content_factory_batches"("id") on delete set null,
  foreign key("reviewed_by_user_id") references "users"("id") on delete set null
);
CREATE INDEX "content_factory_issues_entity_type_entity_public_id_status_index" on "content_factory_issues"(
  "entity_type",
  "entity_public_id",
  "status"
);
CREATE UNIQUE INDEX "content_factory_issues_public_id_unique" on "content_factory_issues"(
  "public_id"
);
CREATE INDEX "content_factory_issues_entity_public_id_index" on "content_factory_issues"(
  "entity_public_id"
);
CREATE UNIQUE INDEX "content_factory_issues_fingerprint_unique" on "content_factory_issues"(
  "fingerprint"
);
CREATE INDEX "content_factory_issues_issue_type_index" on "content_factory_issues"(
  "issue_type"
);
CREATE INDEX "content_factory_issues_risk_level_index" on "content_factory_issues"(
  "risk_level"
);
CREATE INDEX "content_factory_issues_status_index" on "content_factory_issues"(
  "status"
);
CREATE INDEX "content_factory_issues_category_index" on "content_factory_issues"(
  "category"
);
CREATE INDEX "content_factory_issues_risk_domain_index" on "content_factory_issues"(
  "risk_domain"
);
CREATE INDEX "content_factory_issues_priority_score_index" on "content_factory_issues"(
  "priority_score"
);
CREATE INDEX "content_factory_issues_resolution_mode_index" on "content_factory_issues"(
  "resolution_mode"
);
CREATE INDEX "content_factory_issues_group_key_index" on "content_factory_issues"(
  "group_key"
);
CREATE TABLE IF NOT EXISTS "content_factory_evidences"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "content_factory_issue_id" integer not null,
  "title" varchar not null,
  "publisher" varchar not null,
  "url" varchar not null,
  "published_at" date,
  "accessed_at" datetime not null,
  "source_type" varchar not null,
  "trust_level" varchar not null,
  "supported_claim" varchar,
  "confidence_score" integer not null default '0',
  "conflicting_evidence" text,
  "freshness_requirement" varchar,
  "review_status" varchar not null default 'source_required',
  "summary" text not null,
  "usage_note" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("content_factory_issue_id") references "content_factory_issues"("id") on delete cascade
);
CREATE UNIQUE INDEX "content_factory_evidences_public_id_unique" on "content_factory_evidences"(
  "public_id"
);
CREATE INDEX "content_factory_evidences_review_status_index" on "content_factory_evidences"(
  "review_status"
);
CREATE TABLE IF NOT EXISTS "content_factory_revisions"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "content_factory_issue_id" integer not null,
  "entity_type" varchar not null,
  "entity_public_id" varchar not null,
  "field_name" varchar,
  "before_value" text,
  "proposed_value" text,
  "reason" text not null,
  "source_evidence" text,
  "issue_public_id" varchar not null,
  "evidence_reference_ids" text,
  "created_by_user_id" integer,
  "status" varchar not null default 'proposed',
  "conflict_status" varchar not null default 'none',
  "current_value_hash" varchar,
  "before_value_hash" varchar,
  "audit_metadata" text,
  "approved_by_user_id" integer,
  "approved_at" datetime,
  "applied_by_user_id" integer,
  "applied_at" datetime,
  "rolled_back_by_user_id" integer,
  "rolled_back_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("content_factory_issue_id") references "content_factory_issues"("id") on delete cascade,
  foreign key("created_by_user_id") references "users"("id") on delete set null,
  foreign key("approved_by_user_id") references "users"("id") on delete set null,
  foreign key("applied_by_user_id") references "users"("id") on delete set null,
  foreign key("rolled_back_by_user_id") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "content_factory_revisions_public_id_unique" on "content_factory_revisions"(
  "public_id"
);
CREATE INDEX "content_factory_revisions_entity_public_id_index" on "content_factory_revisions"(
  "entity_public_id"
);
CREATE INDEX "content_factory_revisions_issue_public_id_index" on "content_factory_revisions"(
  "issue_public_id"
);
CREATE INDEX "content_factory_revisions_status_index" on "content_factory_revisions"(
  "status"
);
CREATE INDEX "content_factory_revisions_conflict_status_index" on "content_factory_revisions"(
  "conflict_status"
);
CREATE TABLE IF NOT EXISTS "content_factory_bulk_runs"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "group_key" varchar not null,
  "fingerprint" varchar not null,
  "result" text not null,
  "created_by_user_id" integer,
  "expires_at" datetime not null,
  "applied_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("created_by_user_id") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "content_factory_bulk_runs_public_id_unique" on "content_factory_bulk_runs"(
  "public_id"
);
CREATE INDEX "content_factory_bulk_runs_group_key_index" on "content_factory_bulk_runs"(
  "group_key"
);
CREATE INDEX "content_factory_bulk_runs_fingerprint_index" on "content_factory_bulk_runs"(
  "fingerprint"
);
CREATE INDEX "content_factory_bulk_runs_expires_at_index" on "content_factory_bulk_runs"(
  "expires_at"
);
CREATE TABLE IF NOT EXISTS "editorial_review_batches"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "planning_key" varchar not null,
  "title" varchar not null,
  "category" varchar not null,
  "severity_mix" text not null,
  "risk_mix" text not null,
  "total_issues" integer not null default '0',
  "source_required_count" integer not null default '0',
  "evidence_ready_count" integer not null default '0',
  "human_review_count" integer not null default '0',
  "duplicate_group_count" integer not null default '0',
  "estimated_review_minutes" integer not null default '0',
  "priority_score" integer not null default '0',
  "status" varchar not null default 'planned',
  "assigned_reviewer_id" integer,
  "started_at" datetime,
  "completed_at" datetime,
  "progress" integer not null default '0',
  "blocking_reasons" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("assigned_reviewer_id") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "editorial_review_batches_public_id_unique" on "editorial_review_batches"(
  "public_id"
);
CREATE UNIQUE INDEX "editorial_review_batches_planning_key_unique" on "editorial_review_batches"(
  "planning_key"
);
CREATE INDEX "editorial_review_batches_category_index" on "editorial_review_batches"(
  "category"
);
CREATE INDEX "editorial_review_batches_priority_score_index" on "editorial_review_batches"(
  "priority_score"
);
CREATE INDEX "editorial_review_batches_status_index" on "editorial_review_batches"(
  "status"
);
CREATE TABLE IF NOT EXISTS "editorial_review_batch_issue"(
  "id" integer primary key autoincrement not null,
  "editorial_review_batch_id" integer not null,
  "content_factory_issue_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("editorial_review_batch_id") references "editorial_review_batches"("id") on delete cascade,
  foreign key("content_factory_issue_id") references "content_factory_issues"("id") on delete cascade
);
CREATE UNIQUE INDEX "editorial_review_batch_issue_content_factory_issue_id_unique" on "editorial_review_batch_issue"(
  "content_factory_issue_id"
);
CREATE UNIQUE INDEX "editorial_batch_issue_unique" on "editorial_review_batch_issue"(
  "editorial_review_batch_id",
  "content_factory_issue_id"
);
CREATE TABLE IF NOT EXISTS "source_research_queue_items"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "content_factory_issue_id" integer not null,
  "claim" text not null,
  "risk_level" varchar not null,
  "risk_domain" varchar,
  "required_source_type" varchar,
  "suggested_search_terms" text,
  "candidate_source_title" varchar,
  "publisher" varchar,
  "url" varchar,
  "publication_date" date,
  "accessed_at" datetime,
  "source_type" varchar,
  "evidence_summary" text,
  "supported_claim" text,
  "conflicting_evidence" text,
  "confidence_score" integer not null default '0',
  "freshness_requirement" varchar,
  "copyright_safe_notes" text,
  "reviewer_status" varchar not null default 'source_required',
  "assigned_reviewer_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("content_factory_issue_id") references "content_factory_issues"("id") on delete cascade,
  foreign key("assigned_reviewer_id") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "source_research_queue_items_public_id_unique" on "source_research_queue_items"(
  "public_id"
);
CREATE INDEX "source_research_queue_items_risk_level_index" on "source_research_queue_items"(
  "risk_level"
);
CREATE INDEX "source_research_queue_items_risk_domain_index" on "source_research_queue_items"(
  "risk_domain"
);
CREATE INDEX "source_research_queue_items_reviewer_status_index" on "source_research_queue_items"(
  "reviewer_status"
);
CREATE TABLE IF NOT EXISTS "editorial_review_packs"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "content_factory_batch_id" integer,
  "content_type" varchar not null,
  "entity_type" varchar not null,
  "entity_public_id" varchar not null,
  "snapshot" text not null,
  "readiness_score" integer not null default '0',
  "blocking_reasons" text,
  "status" varchar not null default 'blocked',
  "assigned_reviewer_id" integer,
  "reviewed_by_user_id" integer,
  "reviewed_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("content_factory_batch_id") references "content_factory_batches"("id") on delete set null,
  foreign key("assigned_reviewer_id") references "users"("id") on delete set null,
  foreign key("reviewed_by_user_id") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "editorial_review_pack_entity_unique" on "editorial_review_packs"(
  "entity_type",
  "entity_public_id"
);
CREATE UNIQUE INDEX "editorial_review_packs_public_id_unique" on "editorial_review_packs"(
  "public_id"
);
CREATE INDEX "editorial_review_packs_content_type_index" on "editorial_review_packs"(
  "content_type"
);
CREATE INDEX "editorial_review_packs_readiness_score_index" on "editorial_review_packs"(
  "readiness_score"
);
CREATE INDEX "editorial_review_packs_status_index" on "editorial_review_packs"(
  "status"
);
CREATE TABLE IF NOT EXISTS "editorial_review_sections"(
  "id" integer primary key autoincrement not null,
  "editorial_review_pack_id" integer not null,
  "section_key" varchar not null,
  "status" varchar not null default 'pending',
  "note" text,
  "decided_by_user_id" integer,
  "decided_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("editorial_review_pack_id") references "editorial_review_packs"("id") on delete cascade,
  foreign key("decided_by_user_id") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "ers_pack_section_uq" on "editorial_review_sections"(
  "editorial_review_pack_id",
  "section_key"
);
CREATE INDEX "editorial_review_sections_status_index" on "editorial_review_sections"(
  "status"
);
CREATE TABLE IF NOT EXISTS "production_release_states"(
  "id" integer primary key autoincrement not null,
  "state" varchar not null default 'local_qa',
  "last_ci_commit" varchar,
  "last_ci_at" datetime,
  "last_smoke_at" datetime,
  "last_backup_at" datetime,
  "last_migration_at" datetime,
  "last_content_health_scan_at" datetime,
  "blockers" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "production_release_states_state_index" on "production_release_states"(
  "state"
);
CREATE TABLE IF NOT EXISTS "content_quality_profiles"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "entity_type" varchar not null,
  "entity_public_id" varchar not null,
  "content_type" varchar not null,
  "content_quality_status" varchar not null default('not_started'),
  "technical_quality_status" varchar not null default('not_started'),
  "editorial_quality_status" varchar not null default('not_started'),
  "fact_check_status" varchar not null default('not_started'),
  "source_coverage_status" varchar not null default('not_started'),
  "measurement_status" varchar not null default('not_started'),
  "nutrition_status" varchar not null default('not_started'),
  "allergen_status" varchar not null default('not_started'),
  "dietary_status" varchar not null default('not_started'),
  "media_status" varchar not null default('needs_real_media'),
  "seo_status" varchar not null default('not_started'),
  "accessibility_status" varchar not null default('not_started'),
  "final_approval_status" varchar not null default('not_started'),
  "publication_status" varchar not null default('draft'),
  "readiness_score" integer not null default('0'),
  "readiness_cap" integer not null default('100'),
  "rule_version" varchar not null,
  "last_scanned_at" datetime,
  "blocking_reasons" text,
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  "graduation_stage" varchar not null default 'draft',
  "human_approved_by_user_id" integer,
  "human_approved_at" datetime,
  "published_by_user_id" integer,
  foreign key("human_approved_by_user_id") references "users"("id") on delete set null,
  foreign key("published_by_user_id") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "content_quality_profile_entity_unique" on "content_quality_profiles"(
  "entity_type",
  "entity_public_id"
);
CREATE INDEX "content_quality_profiles_accessibility_status_index" on "content_quality_profiles"(
  "accessibility_status"
);
CREATE INDEX "content_quality_profiles_allergen_status_index" on "content_quality_profiles"(
  "allergen_status"
);
CREATE INDEX "content_quality_profiles_content_quality_status_index" on "content_quality_profiles"(
  "content_quality_status"
);
CREATE INDEX "content_quality_profiles_content_type_index" on "content_quality_profiles"(
  "content_type"
);
CREATE INDEX "content_quality_profiles_dietary_status_index" on "content_quality_profiles"(
  "dietary_status"
);
CREATE INDEX "content_quality_profiles_editorial_quality_status_index" on "content_quality_profiles"(
  "editorial_quality_status"
);
CREATE INDEX "content_quality_profiles_fact_check_status_index" on "content_quality_profiles"(
  "fact_check_status"
);
CREATE INDEX "content_quality_profiles_final_approval_status_index" on "content_quality_profiles"(
  "final_approval_status"
);
CREATE INDEX "content_quality_profiles_last_scanned_at_index" on "content_quality_profiles"(
  "last_scanned_at"
);
CREATE INDEX "content_quality_profiles_measurement_status_index" on "content_quality_profiles"(
  "measurement_status"
);
CREATE INDEX "content_quality_profiles_media_status_index" on "content_quality_profiles"(
  "media_status"
);
CREATE INDEX "content_quality_profiles_nutrition_status_index" on "content_quality_profiles"(
  "nutrition_status"
);
CREATE UNIQUE INDEX "content_quality_profiles_public_id_unique" on "content_quality_profiles"(
  "public_id"
);
CREATE INDEX "content_quality_profiles_publication_status_index" on "content_quality_profiles"(
  "publication_status"
);
CREATE INDEX "content_quality_profiles_readiness_score_index" on "content_quality_profiles"(
  "readiness_score"
);
CREATE INDEX "content_quality_profiles_seo_status_index" on "content_quality_profiles"(
  "seo_status"
);
CREATE INDEX "content_quality_profiles_source_coverage_status_index" on "content_quality_profiles"(
  "source_coverage_status"
);
CREATE INDEX "content_quality_profiles_technical_quality_status_index" on "content_quality_profiles"(
  "technical_quality_status"
);
CREATE INDEX "content_quality_profiles_graduation_stage_index" on "content_quality_profiles"(
  "graduation_stage"
);
CREATE TABLE IF NOT EXISTS "media"(
  "id" integer primary key autoincrement not null,
  "mediable_type" varchar not null,
  "mediable_id" integer not null,
  "kind" varchar not null,
  "disk" varchar not null default('public'),
  "path" varchar,
  "embed_url" varchar,
  "mime_type" varchar,
  "size" integer,
  "sort_order" integer not null default('0'),
  "created_at" datetime,
  "updated_at" datetime,
  "public_id" varchar,
  "visibility" varchar not null default('public'),
  "alt_text" varchar,
  "width" integer,
  "height" integer,
  "processing_status" varchar not null default('ready'),
  "thumbnail_status" varchar not null default('pending'),
  "thumbnail_paths" text,
  "processing_error_code" varchar,
  "processing_error_message" varchar,
  "processed_at" datetime,
  "source_name" varchar,
  "source_url" varchar,
  "license" varchar,
  "attribution" varchar,
  "verification_status" varchar not null default('unreviewed'),
  "confidence" integer not null default('0'),
  "reviewed_at" datetime,
  "is_placeholder" tinyint(1) not null default('0'),
  "generated_by_ai" tinyint(1) not null default('0'),
  "ai_provider" varchar,
  "ai_model" varchar,
  "ai_prompt_version" varchar,
  "ai_review_status" varchar,
  "focal_point_x" integer,
  "focal_point_y" integer,
  "checksum_sha256" varchar,
  "perceptual_hash" varchar,
  "approved_by_user_id" integer,
  "approved_at" datetime,
  "provider_metadata" text,
  "media_status" varchar not null default 'no_media',
  "fidelity_status" varchar,
  "fidelity_confidence" integer,
  "fidelity_reasons" text,
  "license_review_status" varchar not null default 'pending',
  "attribution_review_status" varchar not null default 'pending',
  "optimization_status" varchar not null default 'not_requested',
  "responsive_variants" text,
  "media_reviewed_at" datetime,
  "media_reviewer_user_id" integer,
  "license_reviewed_at" datetime,
  "attribution_reviewed_at" datetime,
  "health_scanned_at" datetime,
  "health_rule_version" varchar,
  "caption" varchar,
  foreign key("approved_by_user_id") references users("id") on delete set null on update no action,
  foreign key("media_reviewer_user_id") references "users"("id") on delete set null
);
CREATE INDEX "media_ai_review_status_index" on "media"("ai_review_status");
CREATE INDEX "media_checksum_sha256_index" on "media"("checksum_sha256");
CREATE INDEX "media_disk_created_index" on "media"("disk", "created_at");
CREATE INDEX "media_generated_by_ai_index" on "media"("generated_by_ai");
CREATE INDEX "media_is_placeholder_index" on "media"("is_placeholder");
CREATE INDEX "media_mediable_type_mediable_id_index" on "media"(
  "mediable_type",
  "mediable_id"
);
CREATE INDEX "media_perceptual_hash_index" on "media"("perceptual_hash");
CREATE INDEX "media_processing_status_index" on "media"("processing_status");
CREATE UNIQUE INDEX "media_public_id_unique" on "media"("public_id");
CREATE INDEX "media_thumbnail_status_index" on "media"("thumbnail_status");
CREATE INDEX "media_verification_status_index" on "media"(
  "verification_status"
);
CREATE INDEX "media_visibility_index" on "media"("visibility");
CREATE INDEX "media_entity_status_index" on "media"(
  "mediable_type",
  "mediable_id",
  "media_status"
);
CREATE INDEX "media_checksum_visibility_index" on "media"(
  "checksum_sha256",
  "visibility"
);
CREATE INDEX "media_media_status_index" on "media"("media_status");
CREATE INDEX "media_fidelity_status_index" on "media"("fidelity_status");
CREATE INDEX "media_license_review_status_index" on "media"(
  "license_review_status"
);
CREATE INDEX "media_attribution_review_status_index" on "media"(
  "attribution_review_status"
);
CREATE INDEX "media_optimization_status_index" on "media"(
  "optimization_status"
);
CREATE INDEX "recipe_media_candidates_fidelity_status_index" on "recipe_media_candidates"(
  "fidelity_status"
);
CREATE INDEX "recipe_media_candidates_license_status_index" on "recipe_media_candidates"(
  "license_status"
);
CREATE INDEX "recipe_media_candidates_attribution_status_index" on "recipe_media_candidates"(
  "attribution_status"
);
CREATE TABLE IF NOT EXISTS "ai_conversations"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "user_id" integer not null,
  "purpose" varchar not null,
  "memory" text,
  "last_interacted_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE INDEX "ai_conversations_user_id_purpose_last_interacted_at_index" on "ai_conversations"(
  "user_id",
  "purpose",
  "last_interacted_at"
);
CREATE UNIQUE INDEX "ai_conversations_public_id_unique" on "ai_conversations"(
  "public_id"
);
CREATE TABLE IF NOT EXISTS "ai_interactions"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "user_id" integer,
  "ai_conversation_id" integer,
  "purpose" varchar not null,
  "provider" varchar not null,
  "model" varchar,
  "prompt_version" varchar not null,
  "request_fingerprint" varchar not null,
  "safe_request_summary" text not null,
  "safe_response_summary" text,
  "response_payload" text,
  "input_tokens" integer not null default '0',
  "output_tokens" integer not null default '0',
  "cost_cents" integer not null default '0',
  "latency_ms" integer not null default '0',
  "cache_hit" tinyint(1) not null default '0',
  "fallback_used" tinyint(1) not null default '0',
  "status" varchar not null default 'completed',
  "safe_error_code" varchar,
  "safety_events" text,
  "completed_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete set null,
  foreign key("ai_conversation_id") references "ai_conversations"("id") on delete set null
);
CREATE INDEX "ai_interactions_purpose_status_created_at_index" on "ai_interactions"(
  "purpose",
  "status",
  "created_at"
);
CREATE INDEX "ai_interactions_provider_created_at_index" on "ai_interactions"(
  "provider",
  "created_at"
);
CREATE UNIQUE INDEX "ai_interactions_public_id_unique" on "ai_interactions"(
  "public_id"
);
CREATE INDEX "ai_interactions_request_fingerprint_index" on "ai_interactions"(
  "request_fingerprint"
);
CREATE TABLE IF NOT EXISTS "ai_meal_plans"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "user_id" integer not null,
  "ai_interaction_id" integer,
  "days" integer not null,
  "constraints" text,
  "plan" text not null,
  "status" varchar not null default 'draft',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("ai_interaction_id") references "ai_interactions"("id") on delete set null
);
CREATE INDEX "ai_meal_plans_user_id_status_created_at_index" on "ai_meal_plans"(
  "user_id",
  "status",
  "created_at"
);
CREATE UNIQUE INDEX "ai_meal_plans_public_id_unique" on "ai_meal_plans"(
  "public_id"
);
CREATE TABLE IF NOT EXISTS "ai_shopping_lists"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "user_id" integer not null,
  "ai_meal_plan_id" integer,
  "ai_interaction_id" integer,
  "items" text not null,
  "status" varchar not null default 'draft',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("ai_meal_plan_id") references "ai_meal_plans"("id") on delete set null,
  foreign key("ai_interaction_id") references "ai_interactions"("id") on delete set null
);
CREATE INDEX "ai_shopping_lists_user_id_status_created_at_index" on "ai_shopping_lists"(
  "user_id",
  "status",
  "created_at"
);
CREATE UNIQUE INDEX "ai_shopping_lists_public_id_unique" on "ai_shopping_lists"(
  "public_id"
);
CREATE TABLE IF NOT EXISTS "personalization_profiles"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "user_id" integer not null,
  "personalization_enabled" tinyint(1) not null default '1',
  "onboarding_completed" tinyint(1) not null default '0',
  "profile_version" integer not null default '1',
  "household_size" integer,
  "adults" integer,
  "children" integer,
  "default_portions" integer,
  "weekly_cooking_days" integer,
  "preferred_duration_max" integer,
  "preferred_difficulty" varchar,
  "preferred_budget_level" varchar,
  "settings" text,
  "last_recalculated_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE UNIQUE INDEX "personalization_profiles_public_id_unique" on "personalization_profiles"(
  "public_id"
);
CREATE UNIQUE INDEX "personalization_profiles_user_id_unique" on "personalization_profiles"(
  "user_id"
);
CREATE TABLE IF NOT EXISTS "personalization_preferences"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "personalization_profile_id" integer not null,
  "type" varchar not null,
  "value" varchar not null,
  "source" varchar not null default 'explicitly_selected',
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("personalization_profile_id") references "personalization_profiles"("id") on delete cascade
);
CREATE UNIQUE INDEX "personalization_preference_unique" on "personalization_preferences"(
  "personalization_profile_id",
  "type",
  "value"
);
CREATE INDEX "pp_profile_type_active_ix" on "personalization_preferences"(
  "personalization_profile_id",
  "type",
  "is_active"
);
CREATE UNIQUE INDEX "personalization_preferences_public_id_unique" on "personalization_preferences"(
  "public_id"
);
CREATE TABLE IF NOT EXISTS "personalized_recommendations"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "user_id" integer not null,
  "recipe_id" integer not null,
  "score" integer not null default '0',
  "score_version" varchar not null,
  "reason_codes" text not null,
  "soft_factors" text,
  "status" varchar not null default 'active',
  "generated_at" datetime not null,
  "expires_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("recipe_id") references "recipes"("id") on delete cascade
);
CREATE UNIQUE INDEX "personalized_recommendation_unique" on "personalized_recommendations"(
  "user_id",
  "recipe_id",
  "score_version"
);
CREATE INDEX "personalized_recommendations_user_id_status_expires_at_index" on "personalized_recommendations"(
  "user_id",
  "status",
  "expires_at"
);
CREATE UNIQUE INDEX "personalized_recommendations_public_id_unique" on "personalized_recommendations"(
  "public_id"
);
CREATE TABLE IF NOT EXISTS "personalization_feedback"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "user_id" integer not null,
  "recipe_id" integer,
  "personalized_recommendation_id" integer,
  "type" varchar not null,
  "note" text,
  "profile_impact" text,
  "processed_at" datetime,
  "withdrawn_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("recipe_id") references "recipes"("id") on delete set null,
  foreign key("personalized_recommendation_id") references "personalized_recommendations"("id") on delete set null
);
CREATE INDEX "personalization_feedback_user_id_type_withdrawn_at_index" on "personalization_feedback"(
  "user_id",
  "type",
  "withdrawn_at"
);
CREATE UNIQUE INDEX "personalization_feedback_public_id_unique" on "personalization_feedback"(
  "public_id"
);
CREATE TABLE IF NOT EXISTS "personalization_export_requests"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "user_id" integer not null,
  "status" varchar not null default 'requested',
  "requested_at" datetime not null,
  "completed_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE INDEX "personalization_export_requests_user_id_status_index" on "personalization_export_requests"(
  "user_id",
  "status"
);
CREATE UNIQUE INDEX "personalization_export_requests_public_id_unique" on "personalization_export_requests"(
  "public_id"
);
CREATE TABLE IF NOT EXISTS "ingredient_unit_conversions"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "ingredient_id" integer not null,
  "household_measure_code" varchar not null,
  "household_quantity" numeric not null default('1'),
  "canonical_quantity" numeric not null,
  "canonical_unit" varchar not null,
  "density_g_per_ml" numeric,
  "source_type" varchar not null,
  "source_name" varchar,
  "source_url" varchar,
  "verification_status" varchar not null default('unknown'),
  "confidence_score" integer not null default('0'),
  "conversion_method" varchar not null default 'ingredient_specific',
  "reviewed_at" datetime,
  "reviewed_by_user_id" integer,
  "notes" text,
  "is_active" tinyint(1) not null default('1'),
  "valid_from" datetime,
  "valid_until" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("reviewed_by_user_id") references users("id") on delete set null on update no action,
  foreign key("ingredient_id") references ingredients("id") on delete cascade on update no action
);
CREATE UNIQUE INDEX "ingredient_unit_conversion_unique" on "ingredient_unit_conversions"(
  "ingredient_id",
  "household_measure_code",
  "household_quantity",
  "canonical_unit",
  "is_active"
);
CREATE UNIQUE INDEX "ingredient_unit_conversions_public_id_unique" on "ingredient_unit_conversions"(
  "public_id"
);
CREATE INDEX "iuc_ing_measure_active_ix" on "ingredient_unit_conversions"(
  "ingredient_id",
  "household_measure_code",
  "is_active"
);
CREATE TABLE IF NOT EXISTS "recipe_ingredients"(
  "id" integer primary key autoincrement not null,
  "recipe_id" integer not null,
  "ingredient_id" integer not null,
  "quantity" numeric,
  "unit" varchar,
  "grams" numeric,
  "preparation" varchar,
  "substitution" varchar,
  "is_optional" tinyint(1) not null default('0'),
  "sort_order" integer not null default('0'),
  "created_at" datetime,
  "updated_at" datetime,
  "unit_id" integer,
  "is_required" tinyint(1) not null default('1'),
  "group_name" varchar,
  "preparation_note" varchar,
  "can_be_substituted" tinyint(1) not null default('1'),
  "display_quantity" numeric,
  "display_measure_code" varchar,
  "display_note" varchar,
  "canonical_quantity" numeric,
  "canonical_unit" varchar,
  "ingredient_unit_conversion_id" integer,
  "conversion_confidence" varchar not null default('unknown'),
  "conversion_method" varchar not null default 'not_convertible',
  "scaling_behavior" varchar not null default('scalable'),
  "scaling_note" varchar,
  "manual_override_reason" varchar,
  "public_id" varchar,
  "recipe_ingredient_group_id" integer,
  foreign key("recipe_ingredient_group_id") references recipe_ingredient_groups("id") on delete set null on update no action,
  foreign key("unit_id") references units("id") on delete set null on update no action,
  foreign key("recipe_id") references recipes("id") on delete cascade on update no action,
  foreign key("ingredient_id") references ingredients("id") on delete cascade on update no action,
  foreign key("ingredient_unit_conversion_id") references ingredient_unit_conversions("id") on delete set null on update no action
);
CREATE INDEX "recipe_ing_display_measure_ix" on "recipe_ingredients"(
  "recipe_id",
  "display_measure_code"
);
CREATE INDEX "recipe_ing_recipe_fk_ix" on "recipe_ingredients"("recipe_id");
CREATE INDEX "recipe_ing_sort_ix" on "recipe_ingredients"(
  "recipe_id",
  "sort_order"
);
CREATE INDEX "recipe_ingredients_ingredient_recipe_index" on "recipe_ingredients"(
  "ingredient_id",
  "recipe_id"
);
CREATE UNIQUE INDEX "recipe_ingredients_public_id_unique" on "recipe_ingredients"(
  "public_id"
);
CREATE INDEX "recipe_ingredients_recipe_ingredient_group_id_sort_order_index" on "recipe_ingredients"(
  "recipe_ingredient_group_id",
  "sort_order"
);
CREATE TABLE IF NOT EXISTS "contact_requests"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "first_name" varchar not null,
  "last_name" varchar not null,
  "email" varchar not null,
  "phone" varchar,
  "subject" varchar not null,
  "message" text not null,
  "status" varchar not null default 'new',
  "priority" varchar not null default 'normal',
  "assigned_admin_id" integer,
  "user_id" integer,
  "source_page" varchar,
  "referrer_url" varchar,
  "utm_source" varchar,
  "utm_medium" varchar,
  "utm_campaign" varchar,
  "ip_hash" varchar,
  "user_agent" varchar,
  "privacy_consent_at" datetime not null,
  "first_responded_at" datetime,
  "resolved_at" datetime,
  "last_activity_at" datetime,
  "submitted_at" datetime not null,
  "anonymized_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  foreign key("assigned_admin_id") references "users"("id") on delete set null,
  foreign key("user_id") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "contact_requests_public_id_unique" on "contact_requests"(
  "public_id"
);
CREATE INDEX "contact_requests_email_index" on "contact_requests"("email");
CREATE INDEX "contact_requests_phone_index" on "contact_requests"("phone");
CREATE INDEX "contact_requests_subject_index" on "contact_requests"("subject");
CREATE INDEX "contact_requests_status_index" on "contact_requests"("status");
CREATE INDEX "contact_requests_priority_index" on "contact_requests"(
  "priority"
);
CREATE INDEX "contact_requests_utm_source_index" on "contact_requests"(
  "utm_source"
);
CREATE INDEX "contact_requests_utm_campaign_index" on "contact_requests"(
  "utm_campaign"
);
CREATE INDEX "contact_requests_submitted_at_index" on "contact_requests"(
  "submitted_at"
);
CREATE TABLE IF NOT EXISTS "contact_request_replies"(
  "id" integer primary key autoincrement not null,
  "contact_request_id" integer not null,
  "admin_user_id" integer,
  "body" text not null,
  "sent_at" datetime not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("contact_request_id") references "contact_requests"("id") on delete cascade,
  foreign key("admin_user_id") references "users"("id") on delete set null
);
CREATE INDEX "contact_request_replies_sent_at_index" on "contact_request_replies"(
  "sent_at"
);
CREATE TABLE IF NOT EXISTS "contact_request_notes"(
  "id" integer primary key autoincrement not null,
  "contact_request_id" integer not null,
  "admin_user_id" integer,
  "body" text not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("contact_request_id") references "contact_requests"("id") on delete cascade,
  foreign key("admin_user_id") references "users"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "contact_request_status_histories"(
  "id" integer primary key autoincrement not null,
  "contact_request_id" integer not null,
  "admin_user_id" integer,
  "from_status" varchar,
  "to_status" varchar not null,
  "note" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("contact_request_id") references "contact_requests"("id") on delete cascade,
  foreign key("admin_user_id") references "users"("id") on delete set null
);
CREATE INDEX "users_country_idx" on "users"("country");
CREATE INDEX "users_reg_source_idx" on "users"("registration_source");
CREATE TABLE IF NOT EXISTS "member_registration_notifications"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "user_id" integer not null,
  "idempotency_key" varchar not null,
  "recipient" varchar not null,
  "status" varchar not null default 'pending',
  "attempt_count" integer not null default '0',
  "sent_at" datetime,
  "failed_at" datetime,
  "failure_code" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE UNIQUE INDEX "member_reg_notif_public_unique" on "member_registration_notifications"(
  "public_id"
);
CREATE UNIQUE INDEX "member_reg_notif_idem_unique" on "member_registration_notifications"(
  "idempotency_key"
);
CREATE INDEX "member_reg_notif_status_idx" on "member_registration_notifications"(
  "status"
);
CREATE TABLE IF NOT EXISTS "recipe_collections"(
  "id" integer primary key autoincrement not null,
  "public_id" varchar not null,
  "user_id" integer not null,
  "name" varchar not null,
  "description" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE UNIQUE INDEX "rc_user_name_uq" on "recipe_collections"(
  "user_id",
  "name"
);
CREATE UNIQUE INDEX "rc_public_id_uq" on "recipe_collections"("public_id");
CREATE TABLE IF NOT EXISTS "recipe_collection_items"(
  "id" integer primary key autoincrement not null,
  "recipe_collection_id" integer not null,
  "recipe_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("recipe_collection_id") references "recipe_collections"("id") on delete cascade,
  foreign key("recipe_id") references "recipes"("id") on delete cascade
);
CREATE UNIQUE INDEX "rci_collection_recipe_uq" on "recipe_collection_items"(
  "recipe_collection_id",
  "recipe_id"
);

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2026_07_26_150925_create_cuisines_table',1);
INSERT INTO migrations VALUES(5,'2026_07_26_150926_create_categories_table',1);
INSERT INTO migrations VALUES(6,'2026_07_26_150927_create_ingredients_table',1);
INSERT INTO migrations VALUES(7,'2026_07_26_150928_create_recipes_table',1);
INSERT INTO migrations VALUES(8,'2026_07_26_150929_create_recipe_ingredients_table',1);
INSERT INTO migrations VALUES(9,'2026_07_26_150930_create_recipe_steps_table',1);
INSERT INTO migrations VALUES(10,'2026_07_26_150931_create_favorites_table',1);
INSERT INTO migrations VALUES(11,'2026_07_26_150932_create_pantry_items_table',1);
INSERT INTO migrations VALUES(12,'2026_07_26_150933_create_media_table',1);
INSERT INTO migrations VALUES(13,'2026_07_26_150934_create_data_sources_table',1);
INSERT INTO migrations VALUES(14,'2026_07_26_150935_add_role_to_users_table',1);
INSERT INTO migrations VALUES(15,'2026_07_26_160600_create_units_table',1);
INSERT INTO migrations VALUES(16,'2026_07_26_160601_create_nutrients_table',1);
INSERT INTO migrations VALUES(17,'2026_07_26_160602_create_nutrition_facts_table',1);
INSERT INTO migrations VALUES(18,'2026_07_26_160603_create_allergens_table',1);
INSERT INTO migrations VALUES(19,'2026_07_26_160604_create_dietary_tags_table',1);
INSERT INTO migrations VALUES(20,'2026_07_26_160605_create_recipe_provenances_table',1);
INSERT INTO migrations VALUES(21,'2026_07_26_160606_create_import_runs_table',1);
INSERT INTO migrations VALUES(22,'2026_07_26_160607_add_phase_two_fields_to_core_models',1);
INSERT INTO migrations VALUES(23,'2026_07_26_160950_create_phase_two_relationships',1);
INSERT INTO migrations VALUES(24,'2026_07_26_161420_add_import_identity_to_recipes',1);
INSERT INTO migrations VALUES(25,'2026_07_26_200000_upgrade_legacy_recipe_steps',1);
INSERT INTO migrations VALUES(26,'2026_07_26_200001_create_cooking_sessions_table',1);
INSERT INTO migrations VALUES(27,'2026_07_26_200002_create_cooking_timers_table',1);
INSERT INTO migrations VALUES(28,'2026_07_26_210000_expand_ingredient_pantry_and_matching_domain',1);
INSERT INTO migrations VALUES(29,'2026_07_26_220000_expand_nutrition_allergen_domain',1);
INSERT INTO migrations VALUES(30,'2026_07_26_230000_add_nutrition_admin_and_audit_domain',1);
INSERT INTO migrations VALUES(31,'2026_07_26_231000_complete_nutrition_admin_closure',1);
INSERT INTO migrations VALUES(32,'2026_07_26_240000_create_data_quality_issues_table',1);
INSERT INTO migrations VALUES(33,'2026_07_26_240001_add_issue_lifecycle_columns',1);
INSERT INTO migrations VALUES(34,'2026_07_27_080000_add_performance_indexes',1);
INSERT INTO migrations VALUES(35,'2026_07_27_120000_add_media_operations_fields',1);
INSERT INTO migrations VALUES(36,'2026_07_27_130000_create_observability_tables',1);
INSERT INTO migrations VALUES(37,'2026_07_27_140000_create_restore_histories_table',1);
INSERT INTO migrations VALUES(38,'2026_07_27_180000_add_household_measurement_domain',1);
INSERT INTO migrations VALUES(39,'2026_07_27_190000_add_public_id_to_recipe_ingredients',1);
INSERT INTO migrations VALUES(40,'2026_07_27_200000_create_recipe_ingredient_groups',1);
INSERT INTO migrations VALUES(41,'2026_07_27_210000_add_editorial_content_trust_domain',1);
INSERT INTO migrations VALUES(42,'2026_07_27_211000_add_ingredient_icon_metadata',1);
INSERT INTO migrations VALUES(43,'2026_07_28_090000_expand_data_quality_issue_explanations',1);
INSERT INTO migrations VALUES(44,'2026_07_28_100000_create_ai_image_generation_pilot_tables',1);
INSERT INTO migrations VALUES(45,'2026_07_28_210000_add_safe_provider_diagnostics_to_ai_image_generations',1);
INSERT INTO migrations VALUES(46,'2026_07_29_090000_allow_provider_comparison_per_recipe_variation',1);
INSERT INTO migrations VALUES(47,'2026_07_29_120000_create_blog_content_tables',1);
INSERT INTO migrations VALUES(48,'2026_07_29_210000_add_privacy_safe_user_management',1);
INSERT INTO migrations VALUES(49,'2026_07_29_230000_create_seo_content_operations_tables',1);
INSERT INTO migrations VALUES(50,'2026_07_31_070000_create_recipe_media_candidates_table',1);
INSERT INTO migrations VALUES(51,'2026_07_31_090000_create_controlled_content_factory_tables',1);
INSERT INTO migrations VALUES(52,'2026_07_31_120000_add_unsplash_metadata_to_recipe_media_candidates_table',1);
INSERT INTO migrations VALUES(53,'2026_07_31_150000_add_content_factory_phase_two_operational_data',1);
INSERT INTO migrations VALUES(54,'2026_07_31_170000_add_fact_check_operations',1);
INSERT INTO migrations VALUES(55,'2026_07_31_180000_create_content_factory_bulk_runs_table',1);
INSERT INTO migrations VALUES(56,'2026_08_01_100000_create_editorial_operations_tables',1);
INSERT INTO migrations VALUES(57,'2026_08_01_110000_create_production_content_operations_tables',1);
INSERT INTO migrations VALUES(58,'2026_08_01_111000_add_graduation_lifecycle_to_content_quality_profiles',1);
INSERT INTO migrations VALUES(59,'2026_08_01_140000_add_media_excellence_fields',1);
INSERT INTO migrations VALUES(60,'2026_08_01_140100_add_caption_to_media',1);
INSERT INTO migrations VALUES(61,'2026_08_01_160000_create_ai_kitchen_tables',1);
INSERT INTO migrations VALUES(62,'2026_08_02_060000_create_personalization_tables',1);
INSERT INTO migrations VALUES(63,'2026_08_02_080000_extend_personalization_operations',1);
INSERT INTO migrations VALUES(64,'2026_08_02_090000_expand_ingredient_unit_conversion_method',1);
INSERT INTO migrations VALUES(65,'2026_08_03_090000_create_contact_request_tables',1);
INSERT INTO migrations VALUES(66,'2026_08_03_160000_add_member_registration_profile_and_notifications',1);
INSERT INTO migrations VALUES(67,'2026_08_03_170000_create_recipe_collections',1);
