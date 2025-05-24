<?php
// PDF generation using FPDF (https://www.fpdf.org/)
require_once __DIR__ . '/vendor/fpdf.php';
if (!class_exists('FPDF')) {
    die('FPDF library not found. Please download fpdf.php and place it in the vendor directory.');
}

function generate_invoice_pdf($invoice, $items, $client) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Invoice #' . $invoice['id'], 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Client: ' . ($client['name'] ?? ''), 0, 1);
    $pdf->Cell(0, 10, 'Date: ' . $invoice['created_at'], 0, 1);
    $pdf->Cell(0, 10, 'Status: ' . $invoice['status'], 0, 1);
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(80, 10, 'Description', 1);
    $pdf->Cell(30, 10, 'Quantity', 1);
    $pdf->Cell(30, 10, 'Rate', 1);
    $pdf->Cell(30, 10, 'Total', 1);
    $pdf->Ln();
    $pdf->SetFont('Arial', '', 12);
    foreach ($items as $item) {
        $pdf->Cell(80, 10, $item['description'], 1);
        $pdf->Cell(30, 10, $item['quantity'], 1);
        $pdf->Cell(30, 10, $item['rate'], 1);
        $pdf->Cell(30, 10, $item['total'], 1);
        $pdf->Ln();
    }
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Total: ' . $invoice['total'], 0, 1, 'R');
    return $pdf;
}
