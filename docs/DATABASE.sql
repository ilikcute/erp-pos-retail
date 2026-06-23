-- DATABASE.sql - Corrected consolidated schema
-- ERP POS / Inventory / Purchasing / Accounting
-- MySQL 8.x

SET NAMES utf8mb4;
-- DROP TABLES
SET FOREIGN_KEY_CHECKS = 0;

-- Ditambahkan saat perbaikan (lihat catatan di masing-masing CREATE TABLE)
DROP TABLE IF EXISTS planograms;
DROP TABLE IF EXISTS shifts;
DROP TABLE IF EXISTS day_closings;
DROP TABLE IF EXISTS month_closings;
DROP TABLE IF EXISTS payment_methods;

DROP TABLE IF EXISTS financial_report_lines;
DROP TABLE IF EXISTS financial_report_snapshots;
DROP TABLE IF EXISTS general_ledgers;
DROP TABLE IF EXISTS journal_template_lines;
DROP TABLE IF EXISTS journal_templates;
DROP TABLE IF EXISTS sales_return_items;
DROP TABLE IF EXISTS sales_returns;
DROP TABLE IF EXISTS sales_hold_items;
DROP TABLE IF EXISTS sales_holds;

DROP TABLE IF EXISTS job_queue;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS system_outbox;
DROP TABLE IF EXISTS system_commands;
DROP TABLE IF EXISTS system_events;
DROP TABLE IF EXISTS trial_balances;
DROP TABLE IF EXISTS posting_events;
DROP TABLE IF EXISTS accounting_rule_lines;
DROP TABLE IF EXISTS accounting_rules;
DROP TABLE IF EXISTS fiscal_periods;
DROP TABLE IF EXISTS journal_lines;
DROP TABLE IF EXISTS journal_entries;
DROP TABLE IF EXISTS sales_voids;
DROP TABLE IF EXISTS sales_discounts;
DROP TABLE IF EXISTS sales_payment_allocations;
DROP TABLE IF EXISTS sales_payments;
DROP TABLE IF EXISTS sales_transaction_items;
DROP TABLE IF EXISTS sales_transactions;
DROP TABLE IF EXISTS sales_sessions;
DROP TABLE IF EXISTS inventory_transaction_types;
DROP TABLE IF EXISTS stock_reservations;
DROP TABLE IF EXISTS stock_opname_items;
DROP TABLE IF EXISTS stock_opnames;
DROP TABLE IF EXISTS stock_adjustment_items;
DROP TABLE IF EXISTS stock_adjustments;
DROP TABLE IF EXISTS stock_transfer_items;
DROP TABLE IF EXISTS stock_transfers;
DROP TABLE IF EXISTS inventory_ledger_snapshots;
DROP TABLE IF EXISTS inventory_cost_layers;
DROP TABLE IF EXISTS inventory_ledgers;
DROP TABLE IF EXISTS inventory_balances;
DROP TABLE IF EXISTS inventory_locations;
DROP TABLE IF EXISTS supplier_price_lists;
DROP TABLE IF EXISTS supplier_performances;
DROP TABLE IF EXISTS landed_cost_allocations;
DROP TABLE IF EXISTS landed_costs;
DROP TABLE IF EXISTS purchase_return_items;
DROP TABLE IF EXISTS purchase_returns;
DROP TABLE IF EXISTS supplier_payment_allocations;
DROP TABLE IF EXISTS supplier_payments;
DROP TABLE IF EXISTS accounts_payable;
DROP TABLE IF EXISTS supplier_invoice_items;
DROP TABLE IF EXISTS supplier_invoices;
DROP TABLE IF EXISTS inventory_batches;
DROP TABLE IF EXISTS goods_receipt_items;
DROP TABLE IF EXISTS goods_receipts;
DROP TABLE IF EXISTS purchase_order_items;
DROP TABLE IF EXISTS purchase_orders;
DROP TABLE IF EXISTS purchase_request_items;
DROP TABLE IF EXISTS purchase_requests;
DROP TABLE IF EXISTS purchase_plan_items;
DROP TABLE IF EXISTS purchase_plans;
DROP TABLE IF EXISTS loyalty_adjustments;
DROP TABLE IF EXISTS loyalty_vouchers;
DROP TABLE IF EXISTS reward_redemptions;
DROP TABLE IF EXISTS reward_catalogs;
DROP TABLE IF EXISTS loyalty_configurations;
DROP TABLE IF EXISTS loyalty_expirations;
DROP TABLE IF EXISTS loyalty_transactions;
DROP TABLE IF EXISTS loyalty_accounts;
DROP TABLE IF EXISTS customer_memberships;
DROP TABLE IF EXISTS membership_tiers;
DROP TABLE IF EXISTS promotion_limits;
DROP TABLE IF EXISTS promotion_schedules;
DROP TABLE IF EXISTS promotion_settings;
DROP TABLE IF EXISTS promotion_applications;
DROP TABLE IF EXISTS promotion_customer_categories;
DROP TABLE IF EXISTS promotion_targets;
DROP TABLE IF EXISTS promotion_rewards;
DROP TABLE IF EXISTS promotion_conditions;
DROP TABLE IF EXISTS promotions;
DROP TABLE IF EXISTS price_rule_items;
DROP TABLE IF EXISTS price_rules;
DROP TABLE IF EXISTS price_histories;
DROP TABLE IF EXISTS price_change_request_items;
DROP TABLE IF EXISTS price_change_requests;
DROP TABLE IF EXISTS customer_category_price_lists;
DROP TABLE IF EXISTS price_list_items;
DROP TABLE IF EXISTS price_lists;
DROP TABLE IF EXISTS product_account_mappings;
DROP TABLE IF EXISTS product_cost_profiles;
DROP TABLE IF EXISTS product_supplier_links;
DROP TABLE IF EXISTS product_tag_maps;
DROP TABLE IF EXISTS product_tags;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS product_barcodes;
DROP TABLE IF EXISTS product_variant_attributes;
DROP TABLE IF EXISTS product_variants;
DROP TABLE IF EXISTS product_attribute_values;
DROP TABLE IF EXISTS product_attributes;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS product_brands;
DROP TABLE IF EXISTS business_profiles;
DROP TABLE IF EXISTS system_settings;
DROP TABLE IF EXISTS document_sequences;
DROP TABLE IF EXISTS document_types;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS approval_histories;
DROP TABLE IF EXISTS approval_requests;
DROP TABLE IF EXISTS approval_levels;
DROP TABLE IF EXISTS approval_types;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS password_histories;
DROP TABLE IF EXISTS user_sessions;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS chart_of_accounts;
DROP TABLE IF EXISTS warehouse_locations;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS customer_categories;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS unit_conversions;
DROP TABLE IF EXISTS units;
DROP TABLE IF EXISTS product_categories;
SET FOREIGN_KEY_CHECKS = 1;


-- =========================================================
-- product_categories
-- =========================================================
CREATE TABLE product_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id BIGINT UNSIGNED NULL,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY uk_product_category_code (code),
    INDEX idx_product_category_parent(parent_id),
    CONSTRAINT fk_product_category_parent FOREIGN KEY (parent_id) REFERENCES product_categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- units
-- =========================================================
CREATE TABLE units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_base TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY uk_units_code(code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- unit_conversions
-- =========================================================
CREATE TABLE unit_conversions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    from_unit_id BIGINT UNSIGNED NOT NULL,
    to_unit_id BIGINT UNSIGNED NOT NULL,
    multiplier DECIMAL(18,6) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uk_unit_conversion(from_unit_id, to_unit_id),
    CONSTRAINT fk_uc_from_unit FOREIGN KEY (from_unit_id) REFERENCES units(id),
    CONSTRAINT fk_uc_to_unit FOREIGN KEY (to_unit_id) REFERENCES units(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- suppliers
-- =========================================================
CREATE TABLE suppliers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_code VARCHAR(50) NOT NULL,
    supplier_name VARCHAR(200) NOT NULL,
    phone VARCHAR(50),
    email VARCHAR(100),
    address TEXT,
    contact_person VARCHAR(100),
    payment_term_days INT DEFAULT 0,
    tax_number VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY uk_supplier_code(supplier_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- customer_categories
-- =========================================================
CREATE TABLE customer_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_code VARCHAR(50) NOT NULL,
    category_name VARCHAR(100) NOT NULL,
    point_multiplier DECIMAL(10,2) DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY uk_customer_category_code(category_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- customers
-- =========================================================
CREATE TABLE customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_category_id BIGINT UNSIGNED NOT NULL,
    customer_code VARCHAR(50) NOT NULL,
    customer_name VARCHAR(200) NOT NULL,
    phone VARCHAR(50),
    email VARCHAR(100),
    address TEXT,
    birth_date DATE NULL,
    join_date DATE NULL,
    total_point BIGINT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY uk_customer_code(customer_code),
    INDEX idx_customer_category(customer_category_id),
    CONSTRAINT fk_customer_category FOREIGN KEY (customer_category_id) REFERENCES customer_categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- warehouse_locations
-- CATATAN PERBAIKAN: tabel ini sekarang menjadi SATU-SATUNYA master lokasi
-- (menggantikan inventory_locations yang sebelumnya duplikat & tidak
-- terpakai konsisten). Mendukung 2 jenis penggunaan:
--   1) Lokasi BUKAN stock-bearing -> planogram saja (RACK/DISPLAY) =
--      hanya menampilkan posisi barang, tidak memiliki saldo stok sendiri.
--   2) Lokasi stock-bearing (WAREHOUSE/RENTED_WAREHOUSE/RECEIVING/
--      RETURN_AREA/DAMAGED_AREA) -> punya saldo di inventory_balances dan
--      bisa jadi sumber/tujuan stock_transfers. is_external membedakan
--      gudang sendiri (di lokasi toko) vs gudang sewa musiman (Lebaran,
--      Tahun Baru, dll) yang berada di alamat berbeda.
-- =========================================================
CREATE TABLE warehouse_locations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id BIGINT UNSIGNED NULL,
    location_code VARCHAR(50) NOT NULL,
    location_name VARCHAR(150) NOT NULL,
    location_type ENUM('STORE_WAREHOUSE','RENTED_WAREHOUSE','RACK','DISPLAY','RECEIVING','RETURN_AREA','DAMAGED_AREA') NOT NULL,
    is_stock_bearing TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=punya saldo stok sendiri (warehouse/receiving/dll), 0=hanya planogram (rack/display di dalam toko)',
    is_external TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=gudang sewa/lokasi luar (mis. gudang musiman Lebaran), 0=lokasi menyatu dengan toko',
    address TEXT NULL COMMENT 'Diisi jika is_external=1, alamat gudang sewa',
    valid_from DATE NULL COMMENT 'Untuk gudang sewa musiman: tanggal mulai sewa',
    valid_to DATE NULL COMMENT 'Untuk gudang sewa musiman: tanggal akhir sewa',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY uk_location_code(location_code),
    INDEX idx_location_parent(parent_id),
    CONSTRAINT fk_location_parent FOREIGN KEY(parent_id) REFERENCES warehouse_locations(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- chart_of_accounts
-- =========================================================
CREATE TABLE chart_of_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id BIGINT UNSIGNED NULL,
    account_code VARCHAR(50) NOT NULL,
    account_name VARCHAR(200) NOT NULL,
    account_type ENUM('ASSET','LIABILITY','EQUITY','REVENUE','EXPENSE') NOT NULL,
    normal_balance ENUM('DEBIT','CREDIT') NOT NULL,
    is_postable TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY uk_account_code(account_code),
    INDEX idx_coa_parent(parent_id),
    CONSTRAINT fk_coa_parent FOREIGN KEY(parent_id) REFERENCES chart_of_accounts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- payment_methods
-- CATATAN PERBAIKAN: tabel master ditambahkan kembali (sebelumnya
-- metode bayar hanya ENUM lepas di sales_payments tanpa keterkaitan
-- akun). Setiap metode bayar wajib di-mapping ke 1 akun COA (kas/bank)
-- supaya jurnal otomatis per metode bayar bisa dikonfigurasi dari sini,
-- bukan hardcode di accounting_rules. method_type LOYALTY_POINT
-- ditambahkan supaya poin bisa dipakai sebagai salah satu baris
-- pembayaran pada split bill (lihat perubahan sales_payments).
-- =========================================================
CREATE TABLE payment_methods (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    method_code VARCHAR(30) NOT NULL,
    method_name VARCHAR(100) NOT NULL,
    method_type ENUM('CASH','QRIS','DEBIT','CREDIT_CARD','TRANSFER','LOYALTY_POINT','OTHER') NOT NULL,
    is_cash TINYINT(1) NOT NULL DEFAULT 0,
    account_id BIGINT UNSIGNED NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uk_payment_method_code(method_code),
    CONSTRAINT fk_payment_method_account FOREIGN KEY(account_id) REFERENCES chart_of_accounts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- users
-- =========================================================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_code VARCHAR(50) NULL,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) NULL,
    full_name VARCHAR(200) NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY uk_username(username),
    UNIQUE KEY uk_email(email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- user_sessions
-- =========================================================
CREATE TABLE user_sessions (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL, session_token VARCHAR(255) NOT NULL, ip_address VARCHAR(100) NULL, user_agent TEXT NULL, login_at DATETIME NOT NULL, logout_at DATETIME NULL, INDEX idx_user_session_user(user_id), CONSTRAINT fk_session_user FOREIGN KEY(user_id) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- password_histories
-- =========================================================
CREATE TABLE password_histories (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL, password_hash VARCHAR(255) NOT NULL, changed_at DATETIME NOT NULL, CONSTRAINT fk_password_history_user FOREIGN KEY(user_id) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- roles
-- =========================================================
CREATE TABLE roles (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, role_code VARCHAR(50) NOT NULL, role_name VARCHAR(100) NOT NULL, description TEXT NULL, is_system TINYINT(1) DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_role_code(role_code)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- permissions
-- =========================================================
CREATE TABLE permissions (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, permission_code VARCHAR(150) NOT NULL, permission_name VARCHAR(200) NOT NULL, module_name VARCHAR(100) NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_permission_code(permission_code)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- user_roles
-- =========================================================
CREATE TABLE user_roles (user_id BIGINT UNSIGNED NOT NULL, role_id BIGINT UNSIGNED NOT NULL, PRIMARY KEY(user_id, role_id), CONSTRAINT fk_user_role_user FOREIGN KEY(user_id) REFERENCES users(id), CONSTRAINT fk_user_role_role FOREIGN KEY(role_id) REFERENCES roles(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- role_permissions
-- =========================================================
CREATE TABLE role_permissions (role_id BIGINT UNSIGNED NOT NULL, permission_id BIGINT UNSIGNED NOT NULL, PRIMARY KEY(role_id, permission_id), CONSTRAINT fk_role_permission_role FOREIGN KEY(role_id) REFERENCES roles(id), CONSTRAINT fk_role_permission_permission FOREIGN KEY(permission_id) REFERENCES permissions(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- approval_types
-- =========================================================
CREATE TABLE approval_types (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, approval_code VARCHAR(100) NOT NULL, approval_name VARCHAR(200) NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_approval_code(approval_code)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- approval_levels
-- =========================================================
CREATE TABLE approval_levels (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, approval_type_id BIGINT UNSIGNED NOT NULL, sequence_no INT NOT NULL, role_id BIGINT UNSIGNED NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_approval_level(approval_type_id, sequence_no), CONSTRAINT fk_approval_level_type FOREIGN KEY(approval_type_id) REFERENCES approval_types(id), CONSTRAINT fk_approval_level_role FOREIGN KEY(role_id) REFERENCES roles(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- approval_requests
-- =========================================================
CREATE TABLE approval_requests (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, approval_type_id BIGINT UNSIGNED NOT NULL, reference_table VARCHAR(100) NOT NULL, reference_id BIGINT UNSIGNED NOT NULL, requested_by BIGINT UNSIGNED NOT NULL, current_level INT NOT NULL DEFAULT 1, status ENUM('PENDING','APPROVED','REJECTED','CANCELLED') NOT NULL DEFAULT 'PENDING', requested_at DATETIME NOT NULL, completed_at DATETIME NULL, INDEX idx_approval_reference(reference_table, reference_id), CONSTRAINT fk_approval_request_type FOREIGN KEY(approval_type_id) REFERENCES approval_types(id), CONSTRAINT fk_approval_request_user FOREIGN KEY(requested_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- approval_histories
-- =========================================================
CREATE TABLE approval_histories (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, approval_request_id BIGINT UNSIGNED NOT NULL, approver_id BIGINT UNSIGNED NOT NULL, action ENUM('APPROVED','REJECTED') NOT NULL, notes TEXT NULL, action_at DATETIME NOT NULL, CONSTRAINT fk_approval_history_request FOREIGN KEY(approval_request_id) REFERENCES approval_requests(id), CONSTRAINT fk_approval_history_user FOREIGN KEY(approver_id) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- audit_logs
-- =========================================================
CREATE TABLE audit_logs (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NULL, table_name VARCHAR(100) NOT NULL, record_id BIGINT UNSIGNED NOT NULL, action ENUM('CREATE','UPDATE','DELETE') NOT NULL, old_values JSON NULL, new_values JSON NULL, ip_address VARCHAR(100) NULL, user_agent TEXT NULL, created_at DATETIME NOT NULL, INDEX idx_audit_table(table_name), INDEX idx_audit_record(record_id), INDEX idx_audit_entity(table_name, record_id), CONSTRAINT fk_audit_user FOREIGN KEY(user_id) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- activity_logs
-- =========================================================
CREATE TABLE activity_logs (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NULL, module_name VARCHAR(100) NOT NULL, activity VARCHAR(255) NOT NULL, reference_type VARCHAR(100) NULL, reference_id BIGINT UNSIGNED NULL, ip_address VARCHAR(100) NULL, created_at DATETIME NOT NULL, INDEX idx_activity_module(module_name), INDEX idx_activity_reference(reference_type, reference_id), CONSTRAINT fk_activity_user FOREIGN KEY(user_id) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- document_types
-- =========================================================
CREATE TABLE document_types (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, document_code VARCHAR(20) NOT NULL, document_name VARCHAR(100) NOT NULL, prefix VARCHAR(20) NOT NULL, reset_period ENUM('NONE','YEARLY','MONTHLY','DAILY') NOT NULL DEFAULT 'MONTHLY', description TEXT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_document_code(document_code)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- document_sequences
-- =========================================================
CREATE TABLE document_sequences (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, document_type_id BIGINT UNSIGNED NOT NULL, sequence_year INT NOT NULL, sequence_month INT NULL, last_number BIGINT UNSIGNED NOT NULL DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_doc_sequence (document_type_id, sequence_year, sequence_month), CONSTRAINT fk_doc_sequence_type FOREIGN KEY(document_type_id) REFERENCES document_types(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- system_settings
-- =========================================================
CREATE TABLE system_settings (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(150) NOT NULL, setting_value TEXT NULL, description TEXT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_setting_key(setting_key)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- business_profiles
-- =========================================================
CREATE TABLE business_profiles (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, business_name VARCHAR(255) NOT NULL, tax_number VARCHAR(100), phone VARCHAR(50), email VARCHAR(150), address TEXT, logo_path VARCHAR(255), created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- product_brands
-- =========================================================
CREATE TABLE product_brands (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, brand_code VARCHAR(50) NOT NULL, brand_name VARCHAR(150) NOT NULL, description TEXT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, deleted_at TIMESTAMP NULL, UNIQUE KEY uk_brand_code(brand_code)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- products
-- =========================================================
CREATE TABLE products (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_category_id BIGINT UNSIGNED NOT NULL, brand_id BIGINT UNSIGNED NULL, product_code VARCHAR(50) NOT NULL, product_name VARCHAR(255) NOT NULL, description TEXT NULL, is_sellable TINYINT(1) DEFAULT 1, is_purchasable TINYINT(1) DEFAULT 1, is_active TINYINT(1) DEFAULT 1, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, deleted_at TIMESTAMP NULL, UNIQUE KEY uk_product_code(product_code), CONSTRAINT fk_product_category FOREIGN KEY(product_category_id) REFERENCES product_categories(id), CONSTRAINT fk_product_brand FOREIGN KEY(brand_id) REFERENCES product_brands(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- product_attributes
-- =========================================================
CREATE TABLE product_attributes (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, attribute_code VARCHAR(50) NOT NULL, attribute_name VARCHAR(100) NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_attribute_code(attribute_code)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- product_attribute_values
-- =========================================================
CREATE TABLE product_attribute_values (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, attribute_id BIGINT UNSIGNED NOT NULL, value_code VARCHAR(50) NOT NULL, value_name VARCHAR(100) NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_attribute_value(attribute_id, value_code), CONSTRAINT fk_attribute_value_attribute FOREIGN KEY(attribute_id) REFERENCES product_attributes(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- product_variants
-- =========================================================
CREATE TABLE product_variants (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_id BIGINT UNSIGNED NOT NULL, sku VARCHAR(100) NOT NULL, variant_name VARCHAR(255) NOT NULL, base_unit_id BIGINT UNSIGNED NOT NULL, weight DECIMAL(18,4) DEFAULT 0, is_active TINYINT(1) DEFAULT 1, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, deleted_at TIMESTAMP NULL, UNIQUE KEY uk_variant_sku(sku), CONSTRAINT fk_variant_product FOREIGN KEY(product_id) REFERENCES products(id), CONSTRAINT fk_variant_unit FOREIGN KEY(base_unit_id) REFERENCES units(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- product_variant_attributes
-- =========================================================
CREATE TABLE product_variant_attributes (product_variant_id BIGINT UNSIGNED NOT NULL, attribute_value_id BIGINT UNSIGNED NOT NULL, PRIMARY KEY(product_variant_id, attribute_value_id), CONSTRAINT fk_pva_variant FOREIGN KEY(product_variant_id) REFERENCES product_variants(id), CONSTRAINT fk_pva_attribute FOREIGN KEY(attribute_value_id) REFERENCES product_attribute_values(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- product_barcodes
-- =========================================================
CREATE TABLE product_barcodes (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_variant_id BIGINT UNSIGNED NOT NULL, barcode VARCHAR(100) NOT NULL, is_primary TINYINT(1) DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_barcode(barcode), CONSTRAINT fk_barcode_variant FOREIGN KEY(product_variant_id) REFERENCES product_variants(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- product_images
-- =========================================================
CREATE TABLE product_images (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_id BIGINT UNSIGNED NOT NULL, image_path VARCHAR(255) NOT NULL, sort_order INT DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, CONSTRAINT fk_product_image FOREIGN KEY(product_id) REFERENCES products(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- product_tags
-- =========================================================
CREATE TABLE product_tags (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, tag_name VARCHAR(100) NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_product_tag_name(tag_name)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- product_tag_maps
-- =========================================================
CREATE TABLE product_tag_maps (product_id BIGINT UNSIGNED NOT NULL, tag_id BIGINT UNSIGNED NOT NULL, PRIMARY KEY(product_id, tag_id), CONSTRAINT fk_tag_product FOREIGN KEY(product_id) REFERENCES products(id), CONSTRAINT fk_tag_master FOREIGN KEY(tag_id) REFERENCES product_tags(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- product_supplier_links
-- =========================================================
CREATE TABLE product_supplier_links (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_variant_id BIGINT UNSIGNED NOT NULL, supplier_id BIGINT UNSIGNED NOT NULL, supplier_product_code VARCHAR(100) NULL, is_primary TINYINT(1) DEFAULT 0, lead_time_days INT DEFAULT 0, last_purchase_price DECIMAL(18,2) DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_product_supplier(product_variant_id, supplier_id), CONSTRAINT fk_psl_variant FOREIGN KEY(product_variant_id) REFERENCES product_variants(id), CONSTRAINT fk_psl_supplier FOREIGN KEY(supplier_id) REFERENCES suppliers(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- product_cost_profiles
-- =========================================================
CREATE TABLE product_cost_profiles (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_variant_id BIGINT UNSIGNED NOT NULL, costing_method ENUM('FIFO','AVERAGE') DEFAULT 'FIFO', reorder_point DECIMAL(18,4) DEFAULT 0, reorder_qty DECIMAL(18,4) DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_cost_profile_variant(product_variant_id), FOREIGN KEY(product_variant_id) REFERENCES product_variants(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- product_account_mappings
-- =========================================================
CREATE TABLE product_account_mappings (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_category_id BIGINT UNSIGNED NOT NULL, inventory_account_id BIGINT UNSIGNED NOT NULL, cogs_account_id BIGINT UNSIGNED NOT NULL, sales_account_id BIGINT UNSIGNED NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_product_account_category(product_category_id), FOREIGN KEY(product_category_id) REFERENCES product_categories(id), FOREIGN KEY(inventory_account_id) REFERENCES chart_of_accounts(id), FOREIGN KEY(cogs_account_id) REFERENCES chart_of_accounts(id), FOREIGN KEY(sales_account_id) REFERENCES chart_of_accounts(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- price_lists
-- =========================================================
CREATE TABLE price_lists (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, price_list_code VARCHAR(50) NOT NULL, price_list_name VARCHAR(150) NOT NULL, description TEXT NULL, is_default TINYINT(1) DEFAULT 0, is_active TINYINT(1) DEFAULT 1, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, deleted_at TIMESTAMP NULL, UNIQUE KEY uk_price_list_code(price_list_code)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- price_list_items
-- =========================================================
CREATE TABLE price_list_items (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, price_list_id BIGINT UNSIGNED NOT NULL, product_variant_id BIGINT UNSIGNED NOT NULL, selling_price DECIMAL(18,2) NOT NULL, minimum_qty DECIMAL(18,4) DEFAULT 1, valid_from DATETIME NULL, valid_until DATETIME NULL, is_active TINYINT(1) DEFAULT 1, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, INDEX idx_price_list_variant (price_list_id, product_variant_id), FOREIGN KEY(price_list_id) REFERENCES price_lists(id), FOREIGN KEY(product_variant_id) REFERENCES product_variants(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- customer_category_price_lists
-- =========================================================
CREATE TABLE customer_category_price_lists (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, customer_category_id BIGINT UNSIGNED NOT NULL, price_list_id BIGINT UNSIGNED NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_ccpl (customer_category_id, price_list_id), FOREIGN KEY(customer_category_id) REFERENCES customer_categories(id), FOREIGN KEY(price_list_id) REFERENCES price_lists(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- price_change_requests
-- =========================================================
CREATE TABLE price_change_requests (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, request_no VARCHAR(100) NOT NULL, requested_by BIGINT UNSIGNED NOT NULL, request_date DATETIME NOT NULL, status ENUM('DRAFT','PENDING','APPROVED','REJECTED') NOT NULL DEFAULT 'DRAFT', notes TEXT NULL, approved_by BIGINT UNSIGNED NULL, approved_at DATETIME NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_price_change_request_no(request_no), FOREIGN KEY(requested_by) REFERENCES users(id), FOREIGN KEY(approved_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- price_change_request_items
-- =========================================================
CREATE TABLE price_change_request_items (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, price_change_request_id BIGINT UNSIGNED NOT NULL, product_variant_id BIGINT UNSIGNED NOT NULL, old_price DECIMAL(18,2) NOT NULL, new_price DECIMAL(18,2) NOT NULL, reason TEXT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(price_change_request_id) REFERENCES price_change_requests(id), FOREIGN KEY(product_variant_id) REFERENCES product_variants(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- price_histories
-- =========================================================
CREATE TABLE price_histories (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_variant_id BIGINT UNSIGNED NOT NULL, price_list_id BIGINT UNSIGNED NOT NULL, old_price DECIMAL(18,2) NOT NULL, new_price DECIMAL(18,2) NOT NULL, changed_by BIGINT UNSIGNED NOT NULL, changed_at DATETIME NOT NULL, reference_type VARCHAR(100) NULL, reference_id BIGINT UNSIGNED NULL, FOREIGN KEY(product_variant_id) REFERENCES product_variants(id), FOREIGN KEY(price_list_id) REFERENCES price_lists(id), FOREIGN KEY(changed_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- price_rules
-- =========================================================
CREATE TABLE price_rules (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, rule_code VARCHAR(50) NOT NULL, rule_name VARCHAR(150) NOT NULL, priority INT DEFAULT 1, valid_from DATETIME NULL, valid_until DATETIME NULL, is_active TINYINT(1) DEFAULT 1, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_price_rule_code(rule_code)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- price_rule_items
-- =========================================================
CREATE TABLE price_rule_items (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, price_rule_id BIGINT UNSIGNED NOT NULL, product_variant_id BIGINT UNSIGNED NOT NULL, override_price DECIMAL(18,2) NOT NULL, FOREIGN KEY(price_rule_id) REFERENCES price_rules(id), FOREIGN KEY(product_variant_id) REFERENCES product_variants(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- promotions
-- =========================================================
CREATE TABLE promotions (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, promotion_code VARCHAR(50) NOT NULL, promotion_name VARCHAR(200) NOT NULL, description TEXT NULL, priority INT NOT NULL DEFAULT 1, stackable TINYINT(1) DEFAULT 0, valid_from DATETIME NOT NULL, valid_until DATETIME NOT NULL, status ENUM('DRAFT','ACTIVE','INACTIVE','EXPIRED') DEFAULT 'DRAFT', earn_point_allowed TINYINT(1) DEFAULT 1, redeem_point_allowed TINYINT(1) DEFAULT 1, created_by BIGINT UNSIGNED NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_promotion_code(promotion_code), FOREIGN KEY(created_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- promotion_conditions
-- =========================================================
CREATE TABLE promotion_conditions (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, promotion_id BIGINT UNSIGNED NOT NULL, condition_type ENUM('MIN_AMOUNT','MIN_QTY','DAY_OF_WEEK','CUSTOMER_CATEGORY','PRODUCT','CATEGORY') NOT NULL, operator ENUM('=','>','<','>=','<=') NULL, condition_value VARCHAR(255) NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(promotion_id) REFERENCES promotions(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- promotion_rewards
-- =========================================================
CREATE TABLE promotion_rewards (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, promotion_id BIGINT UNSIGNED NOT NULL, reward_type ENUM('PERCENTAGE','FIXED_AMOUNT','FREE_PRODUCT','SPECIAL_PRICE') NOT NULL, reward_value DECIMAL(18,2) NOT NULL, free_product_variant_id BIGINT UNSIGNED NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(promotion_id) REFERENCES promotions(id), FOREIGN KEY(free_product_variant_id) REFERENCES product_variants(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- promotion_targets
-- =========================================================
CREATE TABLE promotion_targets (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, promotion_id BIGINT UNSIGNED NOT NULL, target_type ENUM('PRODUCT','CATEGORY','ALL_PRODUCT') NOT NULL, target_id BIGINT UNSIGNED NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(promotion_id) REFERENCES promotions(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- promotion_customer_categories
-- =========================================================
CREATE TABLE promotion_customer_categories (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, promotion_id BIGINT UNSIGNED NOT NULL, customer_category_id BIGINT UNSIGNED NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_promo_customer(promotion_id, customer_category_id), FOREIGN KEY(promotion_id) REFERENCES promotions(id), FOREIGN KEY(customer_category_id) REFERENCES customer_categories(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- promotion_applications
-- =========================================================
CREATE TABLE promotion_applications (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, promotion_id BIGINT UNSIGNED NOT NULL, transaction_type VARCHAR(50) NOT NULL, transaction_id BIGINT UNSIGNED NOT NULL, discount_amount DECIMAL(18,2) NOT NULL, applied_at DATETIME NOT NULL, INDEX idx_promo_application_transaction(transaction_type, transaction_id), FOREIGN KEY(promotion_id) REFERENCES promotions(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- promotion_settings
-- =========================================================
CREATE TABLE promotion_settings (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, margin_protection_mode ENUM('BLOCK','WARNING','ALLOW') NOT NULL DEFAULT 'WARNING', allow_negative_margin TINYINT(1) DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- promotion_schedules
-- =========================================================
CREATE TABLE promotion_schedules (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, promotion_id BIGINT UNSIGNED NOT NULL, day_of_week TINYINT NULL, start_time TIME NULL, end_time TIME NULL, FOREIGN KEY(promotion_id) REFERENCES promotions(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- promotion_limits
-- =========================================================
CREATE TABLE promotion_limits (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, promotion_id BIGINT UNSIGNED NOT NULL, max_usage INT NULL, max_usage_per_customer INT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(promotion_id) REFERENCES promotions(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- DOMAIN 6 - LOYALTY
-- =========================================================
CREATE TABLE membership_tiers (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, tier_code VARCHAR(50) NOT NULL, tier_name VARCHAR(100) NOT NULL, minimum_spending DECIMAL(18,2) DEFAULT 0, minimum_points BIGINT DEFAULT 0, point_multiplier DECIMAL(10,2) DEFAULT 1.00, benefits TEXT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_membership_tier_code(tier_code)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE customer_memberships (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, customer_id BIGINT UNSIGNED NOT NULL, membership_tier_id BIGINT UNSIGNED NOT NULL, start_date DATE NOT NULL, end_date DATE NULL, status ENUM('ACTIVE','INACTIVE','EXPIRED') DEFAULT 'ACTIVE', created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(customer_id) REFERENCES customers(id), FOREIGN KEY(membership_tier_id) REFERENCES membership_tiers(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE loyalty_accounts (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, customer_id BIGINT UNSIGNED NOT NULL, account_no VARCHAR(50) NOT NULL, current_balance BIGINT DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_loyalty_account(customer_id), UNIQUE KEY uk_loyalty_account_no(account_no), FOREIGN KEY(customer_id) REFERENCES customers(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE loyalty_transactions (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, loyalty_account_id BIGINT UNSIGNED NOT NULL, transaction_type ENUM('EARN','REDEEM','EXPIRE','ADJUSTMENT','REVERSAL') NOT NULL, reference_type VARCHAR(100) NULL, reference_id BIGINT UNSIGNED NULL, point_in BIGINT DEFAULT 0, point_out BIGINT DEFAULT 0, balance_after BIGINT NOT NULL, notes TEXT NULL, transaction_date DATETIME NOT NULL, created_by BIGINT UNSIGNED NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(loyalty_account_id) REFERENCES loyalty_accounts(id), FOREIGN KEY(created_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE loyalty_expirations (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, loyalty_transaction_id BIGINT UNSIGNED NOT NULL, earned_points BIGINT NOT NULL, remaining_points BIGINT NOT NULL, expiry_date DATE NOT NULL, is_expired TINYINT(1) DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(loyalty_transaction_id) REFERENCES loyalty_transactions(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE loyalty_configurations (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, point_expiry_months INT DEFAULT 12, minimum_redeem_points BIGINT DEFAULT 100, point_value DECIMAL(18,4) DEFAULT 0, allow_negative_point TINYINT(1) DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE reward_catalogs (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, reward_code VARCHAR(50) NOT NULL, reward_name VARCHAR(150) NOT NULL, reward_type ENUM('VOUCHER','PRODUCT','LUCKY_DRAW') NOT NULL, point_required BIGINT NOT NULL, product_variant_id BIGINT UNSIGNED NULL, voucher_amount DECIMAL(18,2) NULL, stock_qty DECIMAL(18,4) DEFAULT 0, is_active TINYINT(1) DEFAULT 1, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_reward_code(reward_code), FOREIGN KEY(product_variant_id) REFERENCES product_variants(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE reward_redemptions (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, redemption_no VARCHAR(50) NOT NULL, customer_id BIGINT UNSIGNED NOT NULL, reward_catalog_id BIGINT UNSIGNED NOT NULL, points_used BIGINT NOT NULL, status ENUM('REQUESTED','APPROVED','REJECTED','COMPLETED') DEFAULT 'REQUESTED', redeemed_at DATETIME NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_redemption_no(redemption_no), FOREIGN KEY(customer_id) REFERENCES customers(id), FOREIGN KEY(reward_catalog_id) REFERENCES reward_catalogs(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE loyalty_vouchers (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, voucher_code VARCHAR(100) NOT NULL, customer_id BIGINT UNSIGNED NOT NULL, reward_redemption_id BIGINT UNSIGNED NOT NULL, voucher_value DECIMAL(18,2) NOT NULL, valid_from DATE NULL, valid_until DATE NULL, is_used TINYINT(1) DEFAULT 0, used_at DATETIME NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_loyalty_voucher_code(voucher_code), FOREIGN KEY(customer_id) REFERENCES customers(id), FOREIGN KEY(reward_redemption_id) REFERENCES reward_redemptions(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE loyalty_adjustments (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, adjustment_no VARCHAR(50) NOT NULL, customer_id BIGINT UNSIGNED NOT NULL, adjustment_type ENUM('ADD','DEDUCT') NOT NULL, points BIGINT NOT NULL, reason TEXT NOT NULL, status ENUM('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING', approved_by BIGINT UNSIGNED NULL, approved_at DATETIME NULL, created_by BIGINT UNSIGNED NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_loyalty_adjustment_no(adjustment_no), FOREIGN KEY(customer_id) REFERENCES customers(id), FOREIGN KEY(approved_by) REFERENCES users(id), FOREIGN KEY(created_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- DOMAIN 7 - PURCHASING ERP
-- =========================================================
CREATE TABLE purchase_plans (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, plan_no VARCHAR(50) NOT NULL, plan_date DATE NOT NULL, remarks TEXT NULL, status ENUM('DRAFT','APPROVED','CANCELLED') DEFAULT 'DRAFT', created_by BIGINT UNSIGNED NOT NULL, approved_by BIGINT UNSIGNED NULL, approved_at DATETIME NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_purchase_plan_no(plan_no), FOREIGN KEY(created_by) REFERENCES users(id), FOREIGN KEY(approved_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE purchase_plan_items (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, purchase_plan_id BIGINT UNSIGNED NOT NULL, product_variant_id BIGINT UNSIGNED NOT NULL, current_stock DECIMAL(18,4) NOT NULL, reorder_point DECIMAL(18,4) NOT NULL, suggested_qty DECIMAL(18,4) NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(purchase_plan_id) REFERENCES purchase_plans(id), FOREIGN KEY(product_variant_id) REFERENCES product_variants(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE purchase_requests (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, request_no VARCHAR(50) NOT NULL, request_date DATE NOT NULL, remarks TEXT NULL, status ENUM('DRAFT','PENDING','APPROVED','REJECTED','CLOSED') DEFAULT 'DRAFT', created_by BIGINT UNSIGNED NOT NULL, approved_by BIGINT UNSIGNED NULL, approved_at DATETIME NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_purchase_request_no(request_no), FOREIGN KEY(created_by) REFERENCES users(id), FOREIGN KEY(approved_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE purchase_request_items (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, purchase_request_id BIGINT UNSIGNED NOT NULL, product_variant_id BIGINT UNSIGNED NOT NULL, requested_qty DECIMAL(18,4) NOT NULL, notes TEXT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(purchase_request_id) REFERENCES purchase_requests(id), FOREIGN KEY(product_variant_id) REFERENCES product_variants(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE purchase_orders (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, po_no VARCHAR(50) NOT NULL, supplier_id BIGINT UNSIGNED NOT NULL, order_date DATE NOT NULL, expected_date DATE NULL, subtotal DECIMAL(18,2) DEFAULT 0, discount_amount DECIMAL(18,2) DEFAULT 0, tax_amount DECIMAL(18,2) DEFAULT 0, total_amount DECIMAL(18,2) DEFAULT 0, status ENUM('DRAFT','APPROVED','PARTIAL_RECEIVED','RECEIVED','CANCELLED') DEFAULT 'DRAFT', created_by BIGINT UNSIGNED NOT NULL, approved_by BIGINT UNSIGNED NULL, approved_at DATETIME NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_po_no(po_no), FOREIGN KEY(supplier_id) REFERENCES suppliers(id), FOREIGN KEY(created_by) REFERENCES users(id), FOREIGN KEY(approved_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE purchase_order_items (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, purchase_order_id BIGINT UNSIGNED NOT NULL, product_variant_id BIGINT UNSIGNED NOT NULL, ordered_qty DECIMAL(18,4) NOT NULL, received_qty DECIMAL(18,4) DEFAULT 0, unit_cost DECIMAL(18,2) NOT NULL, discount_amount DECIMAL(18,2) DEFAULT 0, tax_amount DECIMAL(18,2) DEFAULT 0, line_total DECIMAL(18,2) NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(purchase_order_id) REFERENCES purchase_orders(id), FOREIGN KEY(product_variant_id) REFERENCES product_variants(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE goods_receipts (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, gr_no VARCHAR(50) NOT NULL, purchase_order_id BIGINT UNSIGNED NOT NULL, receipt_date DATE NOT NULL, remarks TEXT NULL, status ENUM('DRAFT','POSTED','CANCELLED') DEFAULT 'DRAFT', posted_by BIGINT UNSIGNED NULL, posted_at DATETIME NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_gr_no(gr_no), FOREIGN KEY(purchase_order_id) REFERENCES purchase_orders(id), FOREIGN KEY(posted_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE goods_receipt_items (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, goods_receipt_id BIGINT UNSIGNED NOT NULL, purchase_order_item_id BIGINT UNSIGNED NOT NULL, product_variant_id BIGINT UNSIGNED NOT NULL, received_qty DECIMAL(18,4) NOT NULL, unit_cost DECIMAL(18,2) NOT NULL, batch_no VARCHAR(100) NULL, expiry_date DATE NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(goods_receipt_id) REFERENCES goods_receipts(id), FOREIGN KEY(purchase_order_item_id) REFERENCES purchase_order_items(id), FOREIGN KEY(product_variant_id) REFERENCES product_variants(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE inventory_batches (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_variant_id BIGINT UNSIGNED NOT NULL, goods_receipt_item_id BIGINT UNSIGNED NOT NULL, batch_no VARCHAR(100) NOT NULL, expiry_date DATE NULL, received_qty DECIMAL(18,4) NOT NULL, remaining_qty DECIMAL(18,4) NOT NULL, unit_cost DECIMAL(18,4) NOT NULL, received_date DATE NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, INDEX idx_batch_variant(product_variant_id), INDEX idx_batch_expiry(expiry_date), FOREIGN KEY(product_variant_id) REFERENCES product_variants(id), FOREIGN KEY(goods_receipt_item_id) REFERENCES goods_receipt_items(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE supplier_invoices (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, invoice_no VARCHAR(100) NOT NULL, supplier_id BIGINT UNSIGNED NOT NULL, goods_receipt_id BIGINT UNSIGNED NULL, supplier_invoice_no VARCHAR(100) NOT NULL, invoice_date DATE NOT NULL, due_date DATE NOT NULL, subtotal DECIMAL(18,2) NOT NULL, discount_amount DECIMAL(18,2) DEFAULT 0, tax_amount DECIMAL(18,2) DEFAULT 0, total_amount DECIMAL(18,2) NOT NULL, status ENUM('DRAFT','POSTED','PARTIAL_PAID','PAID','CANCELLED') DEFAULT 'DRAFT', created_by BIGINT UNSIGNED NOT NULL, posted_by BIGINT UNSIGNED NULL, posted_at DATETIME NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_supplier_invoice_no(invoice_no), FOREIGN KEY(supplier_id) REFERENCES suppliers(id), FOREIGN KEY(goods_receipt_id) REFERENCES goods_receipts(id), FOREIGN KEY(created_by) REFERENCES users(id), FOREIGN KEY(posted_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE supplier_invoice_items (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, supplier_invoice_id BIGINT UNSIGNED NOT NULL, product_variant_id BIGINT UNSIGNED NOT NULL, qty DECIMAL(18,4) NOT NULL, unit_cost DECIMAL(18,2) NOT NULL, discount_amount DECIMAL(18,2) DEFAULT 0, tax_amount DECIMAL(18,2) DEFAULT 0, line_total DECIMAL(18,2) NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(supplier_invoice_id) REFERENCES supplier_invoices(id), FOREIGN KEY(product_variant_id) REFERENCES product_variants(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE accounts_payable (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, supplier_invoice_id BIGINT UNSIGNED NOT NULL, supplier_id BIGINT UNSIGNED NOT NULL, payable_date DATE NOT NULL, due_date DATE NOT NULL, original_amount DECIMAL(18,2) NOT NULL, paid_amount DECIMAL(18,2) DEFAULT 0, outstanding_amount DECIMAL(18,2) NOT NULL, status ENUM('OPEN','PARTIAL','PAID','OVERDUE','CANCELLED') DEFAULT 'OPEN', created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_ap_invoice (supplier_invoice_id), FOREIGN KEY(supplier_invoice_id) REFERENCES supplier_invoices(id), FOREIGN KEY(supplier_id) REFERENCES suppliers(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE supplier_payments (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, payment_no VARCHAR(100) NOT NULL, supplier_id BIGINT UNSIGNED NOT NULL, payment_date DATE NOT NULL, payment_method ENUM('CASH','TRANSFER','GIRO','CHEQUE') NOT NULL, reference_no VARCHAR(100) NULL, total_amount DECIMAL(18,2) NOT NULL, remarks TEXT NULL, status ENUM('DRAFT','POSTED','CANCELLED') DEFAULT 'DRAFT', created_by BIGINT UNSIGNED NOT NULL, posted_by BIGINT UNSIGNED NULL, posted_at DATETIME NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_supplier_payment_no(payment_no), FOREIGN KEY(supplier_id) REFERENCES suppliers(id), FOREIGN KEY(created_by) REFERENCES users(id), FOREIGN KEY(posted_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE supplier_payment_allocations (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, supplier_payment_id BIGINT UNSIGNED NOT NULL, accounts_payable_id BIGINT UNSIGNED NOT NULL, allocated_amount DECIMAL(18,2) NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(supplier_payment_id) REFERENCES supplier_payments(id), FOREIGN KEY(accounts_payable_id) REFERENCES accounts_payable(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE purchase_returns (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, return_no VARCHAR(100) NOT NULL, supplier_id BIGINT UNSIGNED NOT NULL, goods_receipt_id BIGINT UNSIGNED NULL, return_date DATE NOT NULL, reason TEXT NULL, total_amount DECIMAL(18,2) DEFAULT 0, status ENUM('DRAFT','POSTED','CANCELLED') DEFAULT 'DRAFT', created_by BIGINT UNSIGNED NOT NULL, posted_by BIGINT UNSIGNED NULL, posted_at DATETIME NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_purchase_return_no(return_no), FOREIGN KEY(supplier_id) REFERENCES suppliers(id), FOREIGN KEY(goods_receipt_id) REFERENCES goods_receipts(id), FOREIGN KEY(created_by) REFERENCES users(id), FOREIGN KEY(posted_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE purchase_return_items (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, purchase_return_id BIGINT UNSIGNED NOT NULL, inventory_batch_id BIGINT UNSIGNED NOT NULL, return_qty DECIMAL(18,4) NOT NULL, unit_cost DECIMAL(18,2) NOT NULL, line_total DECIMAL(18,2) NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(purchase_return_id) REFERENCES purchase_returns(id), FOREIGN KEY(inventory_batch_id) REFERENCES inventory_batches(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE landed_costs (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, landed_cost_no VARCHAR(100) NOT NULL, goods_receipt_id BIGINT UNSIGNED NOT NULL, cost_type VARCHAR(100) NOT NULL, amount DECIMAL(18,2) NOT NULL, notes TEXT NULL, posted_at DATETIME NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_landed_cost_no(landed_cost_no), FOREIGN KEY(goods_receipt_id) REFERENCES goods_receipts(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE landed_cost_allocations (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, landed_cost_id BIGINT UNSIGNED NOT NULL, inventory_batch_id BIGINT UNSIGNED NOT NULL, allocated_amount DECIMAL(18,2) NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(landed_cost_id) REFERENCES landed_costs(id), FOREIGN KEY(inventory_batch_id) REFERENCES inventory_batches(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE supplier_performances (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, supplier_id BIGINT UNSIGNED NOT NULL, evaluation_period DATE NOT NULL, on_time_delivery_score DECIMAL(5,2), price_score DECIMAL(5,2), quality_score DECIMAL(5,2), service_score DECIMAL(5,2), overall_score DECIMAL(5,2), notes TEXT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(supplier_id) REFERENCES suppliers(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE supplier_price_lists (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, supplier_id BIGINT UNSIGNED NOT NULL, product_variant_id BIGINT UNSIGNED NOT NULL, purchase_price DECIMAL(18,2) NOT NULL, valid_from DATE NULL, valid_until DATE NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(supplier_id) REFERENCES suppliers(id), FOREIGN KEY(product_variant_id) REFERENCES product_variants(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- DOMAIN 8 - INVENTORY ENGINE
-- CATATAN PERBAIKAN: inventory_locations (duplikat, yatim/tidak terpakai)
-- dihapus. Semua FK lokasi di domain ini sekarang menunjuk ke
-- warehouse_locations (lihat definisi di atas) sebagai satu-satunya
-- master lokasi, supaya gudang sewa musiman & rak toko konsisten 1 sumber.
-- =========================================================

-- =========================================================
-- planograms — lapisan informasi penempatan barang (TIDAK memengaruhi
-- saldo stok). Dipakai untuk lokasi is_stock_bearing=0 (RACK/DISPLAY)
-- maupun untuk menandai posisi fisik di dalam lokasi stock-bearing
-- (mis. baris rak tertentu di gudang). Beda dengan inventory_balances
-- yang mencatat SALDO; planogram murni "di mana barang ini diletakkan".
-- =========================================================
CREATE TABLE planograms (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_variant_id BIGINT UNSIGNED NOT NULL, location_id BIGINT UNSIGNED NOT NULL, position_code VARCHAR(50) NULL, notes TEXT NULL, is_active TINYINT(1) DEFAULT 1, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_planogram_variant_location(product_variant_id, location_id), FOREIGN KEY(product_variant_id) REFERENCES product_variants(id), FOREIGN KEY(location_id) REFERENCES warehouse_locations(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE inventory_balances (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_variant_id BIGINT UNSIGNED NOT NULL, inventory_location_id BIGINT UNSIGNED NOT NULL, inventory_batch_id BIGINT UNSIGNED NULL, qty_on_hand DECIMAL(18,4) NOT NULL DEFAULT 0, qty_reserved DECIMAL(18,4) NOT NULL DEFAULT 0, qty_available DECIMAL(18,4) AS (qty_on_hand - qty_reserved) STORED, updated_at TIMESTAMP NULL, UNIQUE KEY uk_balance (product_variant_id, inventory_location_id, inventory_batch_id), FOREIGN KEY(product_variant_id) REFERENCES product_variants(id), FOREIGN KEY(inventory_location_id) REFERENCES warehouse_locations(id), FOREIGN KEY(inventory_batch_id) REFERENCES inventory_batches(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE inventory_ledgers (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, transaction_date DATETIME NOT NULL, transaction_type ENUM('PURCHASE','SALE','TRANSFER_IN','TRANSFER_OUT','ADJUSTMENT_IN','ADJUSTMENT_OUT','RETURN_IN','RETURN_OUT','OPNAME') NOT NULL, reference_type VARCHAR(100) NOT NULL, reference_id BIGINT UNSIGNED NOT NULL, product_variant_id BIGINT UNSIGNED NOT NULL, inventory_location_id BIGINT UNSIGNED NOT NULL, inventory_batch_id BIGINT UNSIGNED NULL, qty_in DECIMAL(18,4) DEFAULT 0, qty_out DECIMAL(18,4) DEFAULT 0, unit_cost DECIMAL(18,4) DEFAULT 0, created_by BIGINT UNSIGNED NULL, created_at TIMESTAMP NULL, INDEX idx_inventory_lookup (product_variant_id, inventory_location_id, inventory_batch_id), INDEX idx_reference (reference_type, reference_id), FOREIGN KEY(product_variant_id) REFERENCES product_variants(id), FOREIGN KEY(inventory_location_id) REFERENCES warehouse_locations(id), FOREIGN KEY(inventory_batch_id) REFERENCES inventory_batches(id), FOREIGN KEY(created_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE inventory_cost_layers (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, inventory_batch_id BIGINT UNSIGNED NOT NULL, product_variant_id BIGINT UNSIGNED NOT NULL, original_qty DECIMAL(18,4) NOT NULL, remaining_qty DECIMAL(18,4) NOT NULL, unit_cost DECIMAL(18,4) NOT NULL, received_date DATE NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(inventory_batch_id) REFERENCES inventory_batches(id), FOREIGN KEY(product_variant_id) REFERENCES product_variants(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE inventory_ledger_snapshots (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_variant_id BIGINT UNSIGNED NOT NULL, inventory_location_id BIGINT UNSIGNED NOT NULL, snapshot_date DATETIME NOT NULL, qty_on_hand DECIMAL(18,4) NOT NULL, created_at TIMESTAMP NULL, INDEX idx_snapshot_lookup(product_variant_id, inventory_location_id, snapshot_date), FOREIGN KEY(product_variant_id) REFERENCES product_variants(id), FOREIGN KEY(inventory_location_id) REFERENCES warehouse_locations(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE stock_transfers (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, transfer_no VARCHAR(100) NOT NULL, source_location_id BIGINT UNSIGNED NOT NULL, destination_location_id BIGINT UNSIGNED NOT NULL, transfer_date DATE NOT NULL, remarks TEXT NULL, status ENUM('DRAFT','POSTED','CANCELLED') DEFAULT 'DRAFT', created_by BIGINT UNSIGNED NOT NULL, posted_by BIGINT UNSIGNED NULL, posted_at DATETIME NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_transfer_no(transfer_no), FOREIGN KEY(source_location_id) REFERENCES warehouse_locations(id), FOREIGN KEY(destination_location_id) REFERENCES warehouse_locations(id), FOREIGN KEY(created_by) REFERENCES users(id), FOREIGN KEY(posted_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE stock_transfer_items (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, stock_transfer_id BIGINT UNSIGNED NOT NULL, inventory_batch_id BIGINT UNSIGNED NOT NULL, transfer_qty DECIMAL(18,4) NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY(stock_transfer_id) REFERENCES stock_transfers(id), FOREIGN KEY(inventory_batch_id) REFERENCES inventory_batches(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE stock_adjustments (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, adjustment_no VARCHAR(100) NOT NULL, adjustment_date DATE NOT NULL, adjustment_type ENUM('PLUS','MINUS') NOT NULL, reason TEXT NOT NULL, status ENUM('DRAFT','PENDING','APPROVED','REJECTED') DEFAULT 'DRAFT', created_by BIGINT UNSIGNED NOT NULL, approved_by BIGINT UNSIGNED NULL, approved_at DATETIME NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_adjustment_no(adjustment_no), FOREIGN KEY(created_by) REFERENCES users(id), FOREIGN KEY(approved_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE stock_adjustment_items (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, stock_adjustment_id BIGINT UNSIGNED NOT NULL, inventory_batch_id BIGINT UNSIGNED NOT NULL, adjustment_qty DECIMAL(18,4) NOT NULL, notes TEXT NULL, FOREIGN KEY(stock_adjustment_id) REFERENCES stock_adjustments(id), FOREIGN KEY(inventory_batch_id) REFERENCES inventory_batches(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE stock_opnames (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, opname_no VARCHAR(100) NOT NULL, inventory_location_id BIGINT UNSIGNED NOT NULL, opname_date DATE NOT NULL, status ENUM('DRAFT','COUNTING','APPROVED','POSTED') DEFAULT 'DRAFT', created_by BIGINT UNSIGNED NOT NULL, approved_by BIGINT UNSIGNED NULL, approved_at DATETIME NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_opname_no(opname_no), FOREIGN KEY(inventory_location_id) REFERENCES warehouse_locations(id), FOREIGN KEY(created_by) REFERENCES users(id), FOREIGN KEY(approved_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE stock_opname_items (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, stock_opname_id BIGINT UNSIGNED NOT NULL, inventory_batch_id BIGINT UNSIGNED NOT NULL, system_qty DECIMAL(18,4) NOT NULL, physical_qty DECIMAL(18,4) NOT NULL, variance_qty DECIMAL(18,4) NOT NULL, notes TEXT NULL, FOREIGN KEY(stock_opname_id) REFERENCES stock_opnames(id), FOREIGN KEY(inventory_batch_id) REFERENCES inventory_batches(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE stock_reservations (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, reservation_no VARCHAR(100) NOT NULL, product_variant_id BIGINT UNSIGNED NOT NULL, inventory_location_id BIGINT UNSIGNED NOT NULL, reserved_qty DECIMAL(18,4) NOT NULL, reference_type VARCHAR(100) NULL, reference_id BIGINT UNSIGNED NULL, expires_at DATETIME NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_reservation_no(reservation_no), INDEX idx_reservation_reference(reference_type, reference_id), FOREIGN KEY(product_variant_id) REFERENCES product_variants(id), FOREIGN KEY(inventory_location_id) REFERENCES warehouse_locations(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE inventory_transaction_types (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, transaction_code VARCHAR(50) NOT NULL, transaction_name VARCHAR(100) NOT NULL, inventory_effect ENUM('IN','OUT','NONE') NOT NULL, UNIQUE KEY uk_inventory_transaction_code(transaction_code)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- DOMAIN 8B - SHIFT & CLOSING (PERBAIKAN: sebelumnya tidak ada di schema,
-- padahal wajib di BRD: FR-023 shift kasir, FR-034 tutup harian,
-- FR-035/036 tutup bulanan)
-- =========================================================
CREATE TABLE shifts (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, shift_code VARCHAR(20) NOT NULL, shift_name VARCHAR(50) NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, is_active TINYINT(1) DEFAULT 1, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_shift_code(shift_code)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE day_closings (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, closing_date DATE NOT NULL, total_sales DECIMAL(18,2) DEFAULT 0, total_transactions INT DEFAULT 0, total_cash DECIMAL(18,2) DEFAULT 0, total_non_cash DECIMAL(18,2) DEFAULT 0, status ENUM('OPEN','CLOSED') DEFAULT 'OPEN', closed_by BIGINT UNSIGNED NULL, closed_at DATETIME NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_day_closing_date(closing_date), FOREIGN KEY(closed_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE month_closings (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, closing_year INT NOT NULL, closing_month INT NOT NULL, notes TEXT NULL, status ENUM('OPEN','CLOSED') DEFAULT 'OPEN', closed_by BIGINT UNSIGNED NULL, closed_at DATETIME NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE KEY uk_month_closing(closing_year, closing_month), FOREIGN KEY(closed_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- DOMAIN 9 - POS ENGINE
-- =========================================================
CREATE TABLE sales_sessions (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, session_no VARCHAR(100) NOT NULL, shift_id BIGINT UNSIGNED NULL, user_id BIGINT UNSIGNED NOT NULL, opened_at DATETIME NOT NULL, closed_at DATETIME NULL, opening_cash DECIMAL(18,2) DEFAULT 0, closing_cash DECIMAL(18,2) DEFAULT 0, cash_difference DECIMAL(18,2) DEFAULT 0, status ENUM('OPEN','CLOSED') DEFAULT 'OPEN', day_closing_id BIGINT UNSIGNED NULL, created_at TIMESTAMP NULL, UNIQUE KEY uk_session_no(session_no), FOREIGN KEY(shift_id) REFERENCES shifts(id), FOREIGN KEY(user_id) REFERENCES users(id), FOREIGN KEY(day_closing_id) REFERENCES day_closings(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE sales_transactions (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, transaction_no VARCHAR(100) NOT NULL, sales_session_id BIGINT UNSIGNED NOT NULL, customer_id BIGINT UNSIGNED NULL, transaction_date DATETIME NOT NULL, subtotal DECIMAL(18,2) NOT NULL DEFAULT 0, discount_total DECIMAL(18,2) DEFAULT 0, tax_total DECIMAL(18,2) DEFAULT 0, grand_total DECIMAL(18,2) NOT NULL, payment_status ENUM('UNPAID','PARTIAL','PAID','VOID') DEFAULT 'UNPAID', loyalty_points_earned BIGINT DEFAULT 0, loyalty_points_redeemed BIGINT DEFAULT 0, loyalty_points_value_redeemed DECIMAL(18,2) DEFAULT 0, created_at TIMESTAMP NULL, UNIQUE KEY uk_transaction_no(transaction_no), FOREIGN KEY(sales_session_id) REFERENCES sales_sessions(id), FOREIGN KEY(customer_id) REFERENCES customers(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE sales_transaction_items (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, sales_transaction_id BIGINT UNSIGNED NOT NULL, product_variant_id BIGINT UNSIGNED NOT NULL, qty DECIMAL(18,4) NOT NULL, unit_price DECIMAL(18,2) NOT NULL, discount_amount DECIMAL(18,2) DEFAULT 0, tax_amount DECIMAL(18,2) DEFAULT 0, line_total DECIMAL(18,2) NOT NULL, cost_price DECIMAL(18,2) DEFAULT 0, created_at TIMESTAMP NULL, FOREIGN KEY(sales_transaction_id) REFERENCES sales_transactions(id), FOREIGN KEY(product_variant_id) REFERENCES product_variants(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- CATATAN PERBAIKAN: payment_method ENUM diganti payment_method_id (FK ke
-- payment_methods) supaya metode bayar dikonfigurasi dari tabel master,
-- bukan hardcode. points_used ditambahkan supaya poin loyalty bisa
-- menjadi salah satu baris pembayaran pada split bill (payment_method_id
-- mengarah ke baris payment_methods bertipe LOYALTY_POINT, amount diisi
-- nilai Rupiah-nya, points_used diisi jumlah poin aktualnya).
CREATE TABLE sales_payments (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, sales_transaction_id BIGINT UNSIGNED NOT NULL, payment_method_id BIGINT UNSIGNED NOT NULL, amount DECIMAL(18,2) NOT NULL, points_used BIGINT UNSIGNED NULL, reference_no VARCHAR(100) NULL, paid_at DATETIME NOT NULL, created_at TIMESTAMP NULL, FOREIGN KEY(sales_transaction_id) REFERENCES sales_transactions(id), FOREIGN KEY(payment_method_id) REFERENCES payment_methods(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE sales_payment_allocations (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, sales_payment_id BIGINT UNSIGNED NOT NULL, sales_transaction_id BIGINT UNSIGNED NOT NULL, allocated_amount DECIMAL(18,2) NOT NULL, FOREIGN KEY(sales_payment_id) REFERENCES sales_payments(id), FOREIGN KEY(sales_transaction_id) REFERENCES sales_transactions(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE sales_discounts (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, sales_transaction_id BIGINT UNSIGNED NOT NULL, promotion_id BIGINT UNSIGNED NULL, discount_type ENUM('PERCENT','FIXED','PRICE_OVERRIDE') NOT NULL, amount DECIMAL(18,2) NOT NULL, created_at TIMESTAMP NULL, FOREIGN KEY(sales_transaction_id) REFERENCES sales_transactions(id), FOREIGN KEY(promotion_id) REFERENCES promotions(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE sales_voids (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, sales_transaction_id BIGINT UNSIGNED NOT NULL, void_reason TEXT NOT NULL, voided_by BIGINT UNSIGNED NOT NULL, voided_at DATETIME NOT NULL, created_at TIMESTAMP NULL, FOREIGN KEY(sales_transaction_id) REFERENCES sales_transactions(id), FOREIGN KEY(voided_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- DOMAIN 9 EXTENSION - HOLD BILL & SALES RETURN
-- Added to satisfy BRD scope: Hold Bill, Return
-- =========================================================

CREATE TABLE sales_holds (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hold_no VARCHAR(100) NOT NULL,
    sales_session_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NULL,
    hold_date DATETIME NOT NULL,
    subtotal DECIMAL(18,2) NOT NULL DEFAULT 0,
    discount_total DECIMAL(18,2) DEFAULT 0,
    tax_total DECIMAL(18,2) DEFAULT 0,
    grand_total DECIMAL(18,2) NOT NULL DEFAULT 0,
    status ENUM('HELD','RECALLED','CANCELLED','CONVERTED') DEFAULT 'HELD',
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    recalled_by BIGINT UNSIGNED NULL,
    recalled_at DATETIME NULL,
    converted_sales_transaction_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uk_sales_hold_no(hold_no),
    INDEX idx_sales_hold_session(sales_session_id),
    INDEX idx_sales_hold_status(status),
    FOREIGN KEY(sales_session_id) REFERENCES sales_sessions(id),
    FOREIGN KEY(customer_id) REFERENCES customers(id),
    FOREIGN KEY(created_by) REFERENCES users(id),
    FOREIGN KEY(recalled_by) REFERENCES users(id),
    FOREIGN KEY(converted_sales_transaction_id) REFERENCES sales_transactions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sales_hold_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sales_hold_id BIGINT UNSIGNED NOT NULL,
    product_variant_id BIGINT UNSIGNED NOT NULL,
    qty DECIMAL(18,4) NOT NULL,
    unit_price DECIMAL(18,2) NOT NULL,
    discount_amount DECIMAL(18,2) DEFAULT 0,
    tax_amount DECIMAL(18,2) DEFAULT 0,
    line_total DECIMAL(18,2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY(sales_hold_id) REFERENCES sales_holds(id),
    FOREIGN KEY(product_variant_id) REFERENCES product_variants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sales_returns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    return_no VARCHAR(100) NOT NULL,
    sales_transaction_id BIGINT UNSIGNED NOT NULL,
    sales_session_id BIGINT UNSIGNED NULL,
    customer_id BIGINT UNSIGNED NULL,
    return_date DATETIME NOT NULL,
    reason TEXT NULL,
    subtotal DECIMAL(18,2) NOT NULL DEFAULT 0,
    discount_total DECIMAL(18,2) DEFAULT 0,
    tax_total DECIMAL(18,2) DEFAULT 0,
    refund_total DECIMAL(18,2) NOT NULL DEFAULT 0,
    status ENUM('DRAFT','POSTED','CANCELLED') DEFAULT 'DRAFT',
    created_by BIGINT UNSIGNED NOT NULL,
    posted_by BIGINT UNSIGNED NULL,
    posted_at DATETIME NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uk_sales_return_no(return_no),
    INDEX idx_sales_return_transaction(sales_transaction_id),
    FOREIGN KEY(sales_transaction_id) REFERENCES sales_transactions(id),
    FOREIGN KEY(sales_session_id) REFERENCES sales_sessions(id),
    FOREIGN KEY(customer_id) REFERENCES customers(id),
    FOREIGN KEY(created_by) REFERENCES users(id),
    FOREIGN KEY(posted_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sales_return_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sales_return_id BIGINT UNSIGNED NOT NULL,
    sales_transaction_item_id BIGINT UNSIGNED NOT NULL,
    product_variant_id BIGINT UNSIGNED NOT NULL,
    inventory_batch_id BIGINT UNSIGNED NULL,
    return_qty DECIMAL(18,4) NOT NULL,
    unit_price DECIMAL(18,2) NOT NULL,
    discount_amount DECIMAL(18,2) DEFAULT 0,
    tax_amount DECIMAL(18,2) DEFAULT 0,
    line_total DECIMAL(18,2) NOT NULL,
    cost_price DECIMAL(18,2) DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY(sales_return_id) REFERENCES sales_returns(id),
    FOREIGN KEY(sales_transaction_item_id) REFERENCES sales_transaction_items(id),
    FOREIGN KEY(product_variant_id) REFERENCES product_variants(id),
    FOREIGN KEY(inventory_batch_id) REFERENCES inventory_batches(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- DOMAIN 10 - ACCOUNTING ENGINE
-- =========================================================
CREATE TABLE journal_entries (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, journal_no VARCHAR(100) NOT NULL, entry_date DATETIME NOT NULL, reference_type VARCHAR(100) NOT NULL, reference_id BIGINT UNSIGNED NOT NULL, description TEXT NULL, total_debit DECIMAL(18,2) NOT NULL DEFAULT 0, total_credit DECIMAL(18,2) NOT NULL DEFAULT 0, status ENUM('DRAFT','POSTED','VOID') DEFAULT 'DRAFT', created_by BIGINT UNSIGNED NOT NULL, posted_by BIGINT UNSIGNED NULL, posted_at DATETIME NULL, created_at TIMESTAMP NULL, UNIQUE KEY uk_journal_no(journal_no), INDEX idx_journal_reference(reference_type, reference_id), FOREIGN KEY(created_by) REFERENCES users(id), FOREIGN KEY(posted_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE journal_lines (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, journal_entry_id BIGINT UNSIGNED NOT NULL, account_id BIGINT UNSIGNED NOT NULL, debit DECIMAL(18,2) DEFAULT 0, credit DECIMAL(18,2) DEFAULT 0, description TEXT NULL, created_at TIMESTAMP NULL, FOREIGN KEY(journal_entry_id) REFERENCES journal_entries(id), FOREIGN KEY(account_id) REFERENCES chart_of_accounts(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE fiscal_periods (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, period_name VARCHAR(50) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, status ENUM('OPEN','CLOSED') DEFAULT 'OPEN', closed_by BIGINT UNSIGNED NULL, closed_at DATETIME NULL, created_at TIMESTAMP NULL, UNIQUE KEY uk_fiscal_period(start_date, end_date), FOREIGN KEY(closed_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE accounting_rules (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, event_type VARCHAR(100) NOT NULL, description TEXT NULL, is_active TINYINT(1) DEFAULT 1, created_at TIMESTAMP NULL, UNIQUE KEY uk_accounting_rule_event(event_type)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE accounting_rule_lines (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, accounting_rule_id BIGINT UNSIGNED NOT NULL, account_id BIGINT UNSIGNED NOT NULL, direction ENUM('DEBIT','CREDIT') NOT NULL, formula TEXT NULL, FOREIGN KEY(accounting_rule_id) REFERENCES accounting_rules(id), FOREIGN KEY(account_id) REFERENCES chart_of_accounts(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE posting_events (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, event_type VARCHAR(100) NOT NULL, reference_type VARCHAR(100) NOT NULL, reference_id BIGINT UNSIGNED NOT NULL, journal_entry_id BIGINT UNSIGNED NULL, status ENUM('PENDING','POSTED','FAILED') DEFAULT 'PENDING', error_message TEXT NULL, created_at TIMESTAMP NULL, INDEX idx_posting_reference(reference_type, reference_id), FOREIGN KEY(journal_entry_id) REFERENCES journal_entries(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE trial_balances (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, fiscal_period_id BIGINT UNSIGNED NOT NULL, account_id BIGINT UNSIGNED NOT NULL, debit_balance DECIMAL(18,2) DEFAULT 0, credit_balance DECIMAL(18,2) DEFAULT 0, updated_at TIMESTAMP NULL, UNIQUE KEY uk_trial_balance(fiscal_period_id, account_id), FOREIGN KEY(fiscal_period_id) REFERENCES fiscal_periods(id), FOREIGN KEY(account_id) REFERENCES chart_of_accounts(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- DOMAIN 10 EXTENSION - JOURNAL TEMPLATE, GENERAL LEDGER, FINANCIAL REPORT
-- Added to satisfy BRD scope: Journal Template, General Ledger, Financial Statement
-- =========================================================

CREATE TABLE journal_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_code VARCHAR(100) NOT NULL,
    template_name VARCHAR(200) NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uk_journal_template_code(template_code),
    INDEX idx_journal_template_event(event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE journal_template_lines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    journal_template_id BIGINT UNSIGNED NOT NULL,
    account_id BIGINT UNSIGNED NOT NULL,
    direction ENUM('DEBIT','CREDIT') NOT NULL,
    formula TEXT NULL,
    description TEXT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY(journal_template_id) REFERENCES journal_templates(id),
    FOREIGN KEY(account_id) REFERENCES chart_of_accounts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE general_ledgers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    journal_entry_id BIGINT UNSIGNED NOT NULL,
    journal_line_id BIGINT UNSIGNED NOT NULL,
    account_id BIGINT UNSIGNED NOT NULL,
    posting_date DATETIME NOT NULL,
    reference_type VARCHAR(100) NOT NULL,
    reference_id BIGINT UNSIGNED NOT NULL,
    debit DECIMAL(18,2) DEFAULT 0,
    credit DECIMAL(18,2) DEFAULT 0,
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    INDEX idx_gl_account_date(account_id, posting_date),
    INDEX idx_gl_reference(reference_type, reference_id),
    FOREIGN KEY(journal_entry_id) REFERENCES journal_entries(id),
    FOREIGN KEY(journal_line_id) REFERENCES journal_lines(id),
    FOREIGN KEY(account_id) REFERENCES chart_of_accounts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE financial_report_snapshots (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fiscal_period_id BIGINT UNSIGNED NOT NULL,
    report_type ENUM('PROFIT_LOSS','BALANCE_SHEET','CASH_FLOW','CHANGES_IN_EQUITY') NOT NULL,
    report_name VARCHAR(200) NOT NULL,
    generated_at DATETIME NOT NULL,
    generated_by BIGINT UNSIGNED NULL,
    status ENUM('DRAFT','FINAL') DEFAULT 'DRAFT',
    total_debit DECIMAL(18,2) DEFAULT 0,
    total_credit DECIMAL(18,2) DEFAULT 0,
    payload JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_financial_report_period(fiscal_period_id, report_type),
    FOREIGN KEY(fiscal_period_id) REFERENCES fiscal_periods(id),
    FOREIGN KEY(generated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE financial_report_lines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    financial_report_snapshot_id BIGINT UNSIGNED NOT NULL,
    account_id BIGINT UNSIGNED NULL,
    line_code VARCHAR(100) NULL,
    line_name VARCHAR(200) NOT NULL,
    line_type ENUM('HEADER','DETAIL','SUBTOTAL','TOTAL') DEFAULT 'DETAIL',
    amount DECIMAL(18,2) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY(financial_report_snapshot_id) REFERENCES financial_report_snapshots(id),
    FOREIGN KEY(account_id) REFERENCES chart_of_accounts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- DOMAIN 11 - SYSTEM CORE
-- =========================================================
CREATE TABLE system_events (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, event_id VARCHAR(100) NOT NULL, event_type VARCHAR(100) NOT NULL, aggregate_type VARCHAR(100) NOT NULL, aggregate_id BIGINT UNSIGNED NOT NULL, payload JSON NOT NULL, status ENUM('PENDING','PROCESSED','FAILED') DEFAULT 'PENDING', created_at TIMESTAMP NULL, UNIQUE KEY uk_system_event_id(event_id), INDEX idx_event_type(event_type), INDEX idx_aggregate(aggregate_type, aggregate_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE system_commands (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, command_id VARCHAR(100) NOT NULL, command_type VARCHAR(100) NOT NULL, payload JSON NOT NULL, status ENUM('RECEIVED','EXECUTED','FAILED') DEFAULT 'RECEIVED', executed_at DATETIME NULL, created_at TIMESTAMP NULL, UNIQUE KEY uk_system_command_id(command_id), INDEX idx_command_type(command_type)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE system_outbox (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, event_type VARCHAR(100) NOT NULL, payload JSON NOT NULL, status ENUM('PENDING','SENT','FAILED') DEFAULT 'PENDING', created_at TIMESTAMP NULL, sent_at DATETIME NULL, INDEX idx_outbox_status(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE notifications (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL, title VARCHAR(255) NOT NULL, message TEXT NOT NULL, type VARCHAR(100) NOT NULL, is_read TINYINT(1) DEFAULT 0, created_at TIMESTAMP NULL, INDEX idx_notification_user(user_id, is_read), FOREIGN KEY(user_id) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE job_queue (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, job_type VARCHAR(100) NOT NULL, payload JSON NOT NULL, status ENUM('PENDING','RUNNING','DONE','FAILED') DEFAULT 'PENDING', attempts INT DEFAULT 0, run_at DATETIME NULL, created_at TIMESTAMP NULL, INDEX idx_job_status(status), INDEX idx_job_run_at(run_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- END OF DATABASE.sql
-- =========================================================
