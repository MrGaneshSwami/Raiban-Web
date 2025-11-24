-- //table for products

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_code VARCHAR(50) UNIQUE NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    quantity INT DEFAULT 0,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- table for suppliers

CREATE TABLE suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE,
    phone VARCHAR(20) NOT NULL,
    alt_phone VARCHAR(20),
    pan_no VARCHAR(20),
    aadhar_no VARCHAR(20),
    bank_name VARCHAR(100),
    bank_branch VARCHAR(100),
    account_no VARCHAR(30),
    gst_no VARCHAR(30),
    ifsc_code VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- table for contractors
CREATE TABLE contractors (
    contractor_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    alt_phone VARCHAR(15),
    pan_no VARCHAR(20),
    aadhar_no VARCHAR(20),
    bank_name VARCHAR(100),
    bank_branch VARCHAR(100),
    account_no VARCHAR(20),
    gst_no VARCHAR(20),
    ifsc_code VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- table for site engineers

CREATE TABLE site_engineers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100),
  employee_code VARCHAR(50),
  phone VARCHAR(15),
  alt_phone VARCHAR(15),
  pan_no VARCHAR(20),
  aadhar_no VARCHAR(20),
  address TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- table for site incharge

CREATE TABLE site_incharge (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100),
  employee_code VARCHAR(50),
  phone VARCHAR(15),
  alt_phone VARCHAR(15),
  pan_no VARCHAR(20),
  aadhar_no VARCHAR(20),
  address TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- employee login 
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mobile VARCHAR(15) UNIQUE,
    password VARCHAR(255) NOT NULL, -- can be plain text for now (but recommend password_hash later)
    role ENUM('admin','manager','employee','Store Incharge') DEFAULT 'employee',
    otp VARCHAR(6),                -- stores last OTP if you enable OTP login
    otp_expire DATETIME,           -- expiry time for OTP
    is_verified TINYINT(1) DEFAULT 0, -- 0 = not verified, 1 = verified
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


-- demand_note table query

-- Master table for demand notes
CREATE TABLE demand_note (
    demand_id INT AUTO_INCREMENT PRIMARY KEY,
    store_incharge VARCHAR(100) NOT NULL,
    site_name VARCHAR(150) NOT NULL,
    site_incharge VARCHAR(100) NOT NULL,
    site_supervisor VARCHAR(100) NOT NULL,
    contractor VARCHAR(100) NOT NULL,
    circle VARCHAR(100),
    division VARCHAR(100),
    subdivision VARCHAR(100),
    section_name VARCHAR(100),
    location VARCHAR(150),
    demand_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending'
);

-- Items linked to demand note
CREATE TABLE demand_note_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    demand_id INT NOT NULL,
    material_code VARCHAR(50),
    material_name VARCHAR(150),
    unit VARCHAR(50),
    qty INT NOT NULL,
    remarks VARCHAR(255),
    FOREIGN KEY (demand_id) REFERENCES demand_note(demand_id) ON DELETE CASCADE
);



-- purchase table query

CREATE TABLE purchases (
    purchase_id INT AUTO_INCREMENT PRIMARY KEY,
    store_incharge VARCHAR(100) NOT NULL,
    supplier_name VARCHAR(150) NOT NULL,
    supplier_gst VARCHAR(50) NOT NULL,
    contact_no VARCHAR(20),
    payment_type ENUM('Paid', 'Unpaid', 'Other') NOT NULL,
    final_cost DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE purchase_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT NOT NULL,
    material_name VARCHAR(150) NOT NULL,
    material_code VARCHAR(100) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(50),
    location VARCHAR(150),
    rate DECIMAL(10,2) NOT NULL,
    gst DECIMAL(10,2) NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (purchase_id) REFERENCES purchases(purchase_id) ON DELETE CASCADE
);



-- issue table query

CREATE TABLE stock_issue (
    issue_id INT AUTO_INCREMENT PRIMARY KEY,
    store_incharge VARCHAR(255) NOT NULL,
    site_name VARCHAR(255) NOT NULL,
    site_incharge VARCHAR(255) NOT NULL,
    site_supervisor VARCHAR(255) NOT NULL,
    contractor VARCHAR(255) NOT NULL,
    circle VARCHAR(255),
    division VARCHAR(255),
    subdivision VARCHAR(255),
    section_name VARCHAR(255),
    location VARCHAR(255),
    final_cost DECIMAL(10,2) DEFAULT 0,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE site_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_name VARCHAR(255) NOT NULL,
    material_code VARCHAR(100) NOT NULL,
    material_name VARCHAR(255) NOT NULL,
    unit VARCHAR(50),
    qty INT DEFAULT 0,
    UNIQUE(site_name, material_code)
);
CREATE TABLE stock_issue_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    issue_id INT,
    material_code VARCHAR(100),
    material_name VARCHAR(255),
    unit VARCHAR(50),
    qty INT NOT NULL,
    rate DECIMAL(10,2),
    total DECIMAL(10,2),
    FOREIGN KEY (issue_id) REFERENCES stock_issue(issue_id) ON DELETE CASCADE
);



-- table for sites
CREATE TABLE sites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_name VARCHAR(255) NOT NULL,
    site_incharge VARCHAR(255) NOT NULL,
    site_supervisor VARCHAR(255) NOT NULL,
    contractor VARCHAR(255) NOT NULL,
    circle VARCHAR(255) NOT NULL,
    division VARCHAR(255) NOT NULL,
    sub_division VARCHAR(255) NOT NULL,
    section VARCHAR(255) NOT NULL,
    location TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Main return record
CREATE TABLE return_stock (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_incharge VARCHAR(100) NOT NULL,
  site_name VARCHAR(150) NOT NULL,
  site_incharge VARCHAR(100),
  site_supervisor VARCHAR(100),
  contractor VARCHAR(100),
  circle VARCHAR(100),
  division VARCHAR(100),
  subdivision VARCHAR(100),
  section_name VARCHAR(100),
  location VARCHAR(150),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Normal return items
CREATE TABLE return_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  return_id INT NOT NULL,
  material_code VARCHAR(100) NOT NULL,
  material_name VARCHAR(150) NOT NULL,
  unit VARCHAR(50) NOT NULL,
  quantity INT NOT NULL,
  consumption DECIMAL(12,2) NOT NULL DEFAULT 0,
  rate DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  remark VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(return_id),
  CONSTRAINT fk_return_items_return
    FOREIGN KEY (return_id) REFERENCES return_stock(id)
    ON DELETE CASCADE
);

-- Scrap items (separate table)
CREATE TABLE return_scrap (
  id INT AUTO_INCREMENT PRIMARY KEY,
  return_id INT NOT NULL,
  scrap_type VARCHAR(50) NOT NULL,
  scrap_qty DECIMAL(12,2) NOT NULL DEFAULT 0,
  rate DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  remark VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(return_id),
  CONSTRAINT fk_return_scrap_return
    FOREIGN KEY (return_id) REFERENCES return_stock(id)
    ON DELETE CASCADE
);

-- Recycle items (separate table)
CREATE TABLE return_recycle (
  id INT AUTO_INCREMENT PRIMARY KEY,
  return_id INT NOT NULL,
  recycle_code VARCHAR(100),
  recycle_name VARCHAR(150),
  recycle_unit VARCHAR(50),
  recycle_qty DECIMAL(12,2) NOT NULL DEFAULT 0,
  rate DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  remark VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(return_id),
  CONSTRAINT fk_return_recycle_return
    FOREIGN KEY (return_id) REFERENCES return_stock(id)
    ON DELETE CASCADE
);