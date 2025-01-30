<?php
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "toko_bunga";

// Koneksi ke database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Menarik data dokumen dari database untuk mendapatkan file yang diupload
$sql = "SELECT * FROM dokumen";
$result = $conn->query($sql);

echo "<h1>Isi Dokumen yang Diunggah (Sheet 2 sampai Sheet 5)</h1>";

if ($result->num_rows > 0) {
    // Menampilkan nama file dan link ke file yang diupload
    while ($row = $result->fetch_assoc()) {
        echo "<h3>Nama File: {$row['nama_file']}</h3>";
        echo "<p><a href='{$row['lokasi_file']}' target='_blank'>Download File</a></p>";

        // Membaca file Excel yang diupload menggunakan PhpSpreadsheet
        $filePath = $row['lokasi_file'];
        try {
            $spreadsheet = IOFactory::load($filePath); // Membaca file Excel

            // Memeriksa sheet yang tersedia
            $sheetCount = $spreadsheet->getSheetCount();
            
            // Loop untuk membaca data dari sheet 2 hingga sheet 5
            for ($sheetIndex = 1; $sheetIndex <= 4; $sheetIndex++) { // Sheet 2 hingga sheet 5 (index 1 hingga 4)
                if ($sheetIndex < $sheetCount) {
                    $sheet = $spreadsheet->getSheet($sheetIndex);  // Mengambil sheet ke-2 hingga ke-5
                    $data = $sheet->toArray();  // Mengambil data dalam bentuk array

                    echo "<h4>Data dari Sheet " . ($sheetIndex + 1) . "</h4>";
                    echo "<table border='1'>";
                    foreach ($data as $row) {
                        echo "<tr>";
                        foreach ($row as $cell) {
                            echo "<td>{$cell}</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table><br>";
                }
            }

        } catch (Exception $e) {
            echo "<p style='color: red;'>Gagal membaca file Excel: {$e->getMessage()}</p>";
        }
    }
} else {
    echo "<p>Tidak ada dokumen yang diunggah.</p>";
}

$conn->close();
?>
