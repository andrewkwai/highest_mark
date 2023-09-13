// Function to fetch results data and populate the table
function fetchAndDisplayResults() {
    $.getJSON("results.json", function(data) {
        var table = $('#resultsTable').DataTable();
        table.clear().draw(); // Clear existing rows
        $.each(data, function(course, courseData) {
            var students = courseData["students"].join("<br>");
            table.row.add([course, students, courseData["highest_mark"]]);
        });
        table.draw();
    });
}

// Function to trigger XLS download
function downloadXLS() {
    var table = $('#resultsTable').DataTable();

    // Create an array of arrays to represent the table data
    var tableData = table
        .rows()
        .data()
        .toArray();

    // Replace <br> with newline (\n) in the students' cell
    tableData.forEach(function(row) {
        row[1] = row[1].replace(/<br>/g, '\n');
    });

    // Create a new workbook and add a worksheet
    var wb = XLSX.utils.book_new();
    var ws = XLSX.utils.aoa_to_sheet(tableData);

    // Add the worksheet to the workbook
    XLSX.utils.book_append_sheet(wb, ws, "Results");

    // Save the XLS file
    XLSX.writeFile(wb, 'highest_marks.xlsx');
}

function downloadAndOpenPDF() {
    // Open the FPDF PDF generation script in a new tab
    window.open('generate_pdf.php', '_blank');
}

// Attach click event handlers to the buttons
$(document).ready(function() {
    $('#downloadXLS').on('click', function() {
        downloadXLS();
    });

    $('#downloadPDF').on('click', function() {
        downloadAndOpenPDF();
    });

    // Fetch and display results when the page loads
    fetchAndDisplayResults();
});
