<?php
/**
 * Generate Test Data for Development
 * Run this script to populate your local environment with fake data
 * 
 * Usage: php tests/generate_test_data.php
 */

require_once __DIR__ . '/../src/database_connection.php';
require_once __DIR__ . '/../src/database_queries.php';

// Configuration
$num_test_patients = 10;
$num_test_images = 5;
$num_test_pdfs = 3;

// Fake patient names
$nombres = ['Carlos', 'María', 'José', 'Ana', 'Luis', 'Carmen', 'Pedro', 'Isabel', 'Miguel', 'Rosa'];
$apellidos = ['García', 'Rodríguez', 'Martínez', 'López', 'González', 'Pérez', 'Sánchez', 'Ramírez'];

// Colors for test images (solid color squares)
$colors = [
    ['red' => 255, 'green' => 0, 'blue' => 0],      // Red
    ['red' => 0, 'green' => 255, 'blue' => 0],      // Green
    ['red' => 0, 'green' => 0, 'blue' => 255],      // Blue
    ['red' => 255, 'green' => 255, 'blue' => 0],    // Yellow
    ['red' => 255, 'green' => 0, 'blue' => 255],    // Magenta
];

echo "🚀 Generating test data...\n\n";

// 1. Create test images (colored squares with text)
echo "📸 Creating test images...\n";
$imageDir = __DIR__ . '/../src/Images/ImgMedicas/';

for ($i = 1; $i <= $num_test_images; $i++) {
    $color = $colors[($i - 1) % count($colors)];
    
    // Create 400x400 image
    $image = imagecreatetruecolor(400, 400);
    $bgColor = imagecolorallocate($image, $color['red'], $color['green'], $color['blue']);
    $textColor = imagecolorallocate($image, 255, 255, 255);
    
    // Fill background
    imagefill($image, 0, 0, $bgColor);
    
    // Add text
    $text = "TEST IMAGE #$i";
    imagestring($image, 5, 120, 190, $text, $textColor);
    
    // Save as JPEG
    $filename = "test_image_$i.jpg";
    imagejpeg($image, $imageDir . $filename, 90);
    imagedestroy($image);
    
    echo "  ✓ Created: $filename\n";
}

// 2. Create test PDFs (using FPDF or simple text file)
echo "\n📄 Creating test PDFs...\n";
$pdfDir = __DIR__ . '/../src/consentimientos/';

for ($i = 1; $i <= $num_test_pdfs; $i++) {
    $nombre = $nombres[array_rand($nombres)];
    $apellido = $apellidos[array_rand($apellidos)];
    $date = date('Y-m-d');
    
    // Simple PDF-like content (for demonstration - use a PDF library in production)
    $content = <<<PDF
TEST CONSENT FORM
==================

Patient: $nombre $apellido
Date: $date
Service: Test Service

This is a TEST consent form for development purposes only.
This file contains NO real patient data.

Signature: _________________
PDF;
    
    $filename = "consentimiento_{$nombre}_{$apellido}_usuario_test{$i}_{$date}.pdf";
    
    // For now, save as text file (in production, use FPDF library)
    file_put_contents($pdfDir . $filename, $content);
    
    echo "  ✓ Created: $filename\n";
}

echo "\n✅ Test data generation complete!\n";
echo "\n📝 Note: These are FAKE files for testing only.\n";
echo "   They contain NO real patient information.\n";

?>
