<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/include/config.php';

// Create investment_plans table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS `investment_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `plan_type` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `duration` varchar(100) NOT NULL,
  `risk_level` varchar(50) NOT NULL,
  `min_investment` decimal(15,2) NOT NULL,
  `max_investment` decimal(15,2) DEFAULT NULL,
  `return_rate` decimal(10,2) NOT NULL,
  `return_interval` varchar(50) NOT NULL,
  `availability` int NOT NULL DEFAULT 30,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

if ($conn_back->query($create_table_sql) === TRUE) {
    echo "Table investment_plans created successfully<br>";
} else {
    echo "Error creating table: " . $conn_back->error . "<br>";
}

// Check if there are already plans in the table
$check_query = "SELECT COUNT(*) as count FROM investment_plans";
$result = $conn_back->query($check_query);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Insert sample investment plans
    $sample_plans = [
        [
            'name' => 'EV and Automotive Fund',
            'plan_type' => 'NFO',
            'category' => 'Direct',
            'duration' => 'Growth',
            'risk_level' => 'Thematic',
            'min_investment' => 100.00,
            'max_investment' => 10000.00,
            'return_rate' => 12.50,
            'return_interval' => 'Quarterly',
            'availability' => 30
        ],
        [
            'name' => 'Technology Growth Fund',
            'plan_type' => 'MF',
            'category' => 'Direct',
            'duration' => 'Long-term',
            'risk_level' => 'Moderate',
            'min_investment' => 250.00,
            'max_investment' => 25000.00,
            'return_rate' => 15.75,
            'return_interval' => 'Annually',
            'availability' => 60
        ],
        [
            'name' => 'Green Energy Fund',
            'plan_type' => 'ETF',
            'category' => 'Direct',
            'duration' => 'Growth',
            'risk_level' => 'High',
            'min_investment' => 500.00,
            'max_investment' => 50000.00,
            'return_rate' => 18.25,
            'return_interval' => 'Quarterly',
            'availability' => 45
        ],
        [
            'name' => 'Blue Chip Stock Fund',
            'plan_type' => 'SIP',
            'category' => 'Regular',
            'duration' => 'Long-term',
            'risk_level' => 'Low',
            'min_investment' => 150.00,
            'max_investment' => 15000.00,
            'return_rate' => 10.50,
            'return_interval' => 'Monthly',
            'availability' => 90
        ]
    ];

    foreach ($sample_plans as $plan) {
        $insert_sql = "INSERT INTO investment_plans 
                      (name, plan_type, category, duration, risk_level, min_investment, max_investment, return_rate, return_interval, availability) 
                      VALUES 
                      ('{$plan['name']}', '{$plan['plan_type']}', '{$plan['category']}', '{$plan['duration']}', '{$plan['risk_level']}', 
                      {$plan['min_investment']}, {$plan['max_investment']}, {$plan['return_rate']}, '{$plan['return_interval']}', {$plan['availability']})";
        
        if ($conn_back->query($insert_sql) === TRUE) {
            echo "Plan '{$plan['name']}' inserted successfully<br>";
        } else {
            echo "Error inserting plan: " . $conn_back->error . "<br>";
        }
    }
} else {
    echo "Sample plans already exist in the database";
}

echo "<p>Done. <a href='/user/investment'>Go to Investments Page</a></p>";
?> 