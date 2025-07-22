-- Add new columns to maintenance table for enhanced reporting
ALTER TABLE maintenance 
ADD COLUMN maintenance_cost DECIMAL(10, 2) DEFAULT NULL,
ADD COLUMN parts_replaced BOOLEAN DEFAULT 0,
ADD COLUMN parts_details TEXT DEFAULT NULL,
ADD COLUMN issues_found TEXT DEFAULT NULL,
ADD COLUMN resolution TEXT DEFAULT NULL,
ADD COLUMN device_health_impact ENUM('significant_improvement', 'minor_improvement', 'no_change', 'deterioration') DEFAULT 'no_change',
ADD COLUMN next_recommended_date DATE DEFAULT NULL; 