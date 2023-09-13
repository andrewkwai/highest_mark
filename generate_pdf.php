<?php
require('tcpdf/tcpdf.php'); // Include the TCPDF library

// Function to generate PDF
function generatePDF($data) {
    // Sort the data by course name alphabetically
    ksort($data);

    $pdf = new TCPDF();
    $pdf->SetPrintHeader(true); // Print the header on each page
    $pdf->SetPrintFooter(false); // Do not print the footer on each page
    $pdf->SetAutoPageBreak(true, 10); // Enable automatic page breaks with a 10 mm margin

    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Highest Marks Report', 0, 1, 'C');

    // Define table column widths as numeric values
    $colWidths = [40, 70, 50]; // Adjusted column widths

    $pdf->SetFont('helvetica', '', 12);

    // Initialize row color counter
    $rowColor = false;

    // Add table headers
    $pdf->SetFillColor(200, 200, 200); // Header background color
    $pdf->Cell($colWidths[0], 10, 'Course', 1, 0, 'C', 1); // Course header
    $pdf->Cell($colWidths[1], 10, 'Students with Highest Mark', 1, 0, 'C', 1); // Students header
    $pdf->Cell($colWidths[2], 10, 'Highest Mark', 1, 1, 'C', 1); // Highest Mark header

    foreach ($data as $course => $courseData) {
        // Alternate row colors
        $rowColor = !$rowColor;
        $pdf->SetFillColor($rowColor ? 240 : 255);

        // Replace line breaks (\n) with <br> for HTML display
        $studentsHTML = str_replace("\n", "<br>", implode("\n", $courseData['students']));

        $highestMark = $courseData['highest_mark'];

        // Calculate the cell height based on the number of lines in the "Students with Highest Mark" cell
        $studentsHeight = count(explode("<br>", $studentsHTML)) * 5 + 5; // Add extra 5 pixels

        // Determine the maximum height between "Students with Highest Mark" and "Highest Mark" cells
        $cellHeight = max(15, $studentsHeight); // Increase the minimum cell height

        // Create a table row with alternating row colors
        $pdf->Cell($colWidths[0], $cellHeight, $course, 1, 0, 'L', $rowColor ? 1 : 0);
        
        // Check if there are multiple students, and adjust the border style accordingly
        $borderStyle = 'LTRB'; // Default border style
        if (strpos($studentsHTML, '<br>') !== false) {
            // Multiple students, remove bottom border
            $borderStyle = 'LTRT';
        }
        
        // Center justify text horizontally and left justify vertically for "Students with Highest Mark" cell
        $pdf->writeHTMLCell($colWidths[1], $cellHeight, '', '', $studentsHTML, 1, 0, 'C', true, 'L', $rowColor ? 1 : 0, $borderStyle);

        // Set vertical alignment to center for the "Highest Mark" cell
        $pdf->Cell($colWidths[2], $cellHeight, $highestMark, 1, 1, 'C', $rowColor ? 1 : 0);
    }

    $pdf->Output('highest_marks.pdf', 'I'); // Output the PDF inline in the browser
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (file_exists("results.json")) {
        $json_data = file_get_contents("results.json");
        $data = json_decode($json_data, true);
        generatePDF($data);
    } else {
        echo "Results data not found.";
    }
}
?>
