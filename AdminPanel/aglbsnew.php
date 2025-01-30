<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Fungsi untuk membaca file Excel dan mengembalikan data sebagai array
function readExcelFile($filePath) {
    try {
        $spreadsheet = IOFactory::load($filePath);
        return $spreadsheet->getActiveSheet()->toArray();
    } catch (Exception $e) {
        return [];
    }
}

// Fungsi untuk mengecek apakah data terurut
function isSorted($data, $keyIndex) {
    for ($i = 1; $i < count($data); $i++) {
        if (strtotime($data[$i][$keyIndex]) < strtotime($data[$i - 1][$keyIndex])) {
            return false;
        }
    }
    return true;
}

// Fungsi untuk mengurutkan data
function sortData(&$data, $keyIndex) {
    usort($data, function ($a, $b) use ($keyIndex) {
        return strtotime($a[$keyIndex]) - strtotime($b[$keyIndex]);
    });
}

// Fungsi binary search
function binarySearch($data, $targetDate, $keyIndex) {
    $low = 0;
    $high = count($data) - 1;
    $results = [];
    while ($low <= $high) {
        $mid = (int)(($low + $high) / 2);
        $midDate = strtotime($data[$mid][$keyIndex]);

        if ($midDate >= strtotime($targetDate['start']) && $midDate <= strtotime($targetDate['end'])) {
            // Tambahkan data yang cocok
            $results[] = $data[$mid];
            // Cek elemen di sekitarnya
            for ($i = $mid - 1; $i >= 0 && strtotime($data[$i][$keyIndex]) >= strtotime($targetDate['start']); $i--) {
                $results[] = $data[$i];
            }
            for ($i = $mid + 1; $i < count($data) && strtotime($data[$i][$keyIndex]) <= strtotime($targetDate['end']); $i++) {
                $results[] = $data[$i];
            }
            break;
        } elseif ($midDate < strtotime($targetDate['start'])) {
            $low = $mid + 1;
        } else {
            $high = $mid - 1;
        }
    }
    return $results;
}

// Fungsi untuk mengekspor data ke file Excel
function exportToExcel($data, $fileName = 'laporan.xlsx') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray($data, null, 'A1');
    $writer = new Xlsx($spreadsheet);
    $writer->save($fileName);
    return $fileName;
}

// Proses pencarian
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    // Validasi input
    if (!$startDate || !$endDate) {
        die("Tanggal awal dan akhir harus diisi.");
    }

    // Menentukan direktori tempat file dokumen berada
    $directory = "../uploads/";
    $files = glob($directory . "*.xlsx");

    // Cari file yang cocok berdasarkan bulan dan tahun
    $fileFound = null;
    foreach ($files as $file) {
        if (strpos($file, date('Y_m', strtotime($startDate))) !== false) {
            $fileFound = $file;
            break;
        }
    }

    if (!$fileFound) {
        die("File tidak ditemukan untuk rentang waktu tersebut.");
    }

    // Baca data dari file Excel
    $data = readExcelFile($fileFound);

    // Pastikan data terurut berdasarkan kolom tanggal (indeks 0)
    $keyIndex = 0; // Misalnya kolom tanggal berada di kolom pertama
    if (!isSorted($data, $keyIndex)) {
        sortData($data, $keyIndex);
    }

    // Lakukan binary search
    $targetDate = ['start' => $startDate, 'end' => $endDate];
    $results = binarySearch($data, $targetDate, $keyIndex);

    if (empty($results)) {
        echo "Tidak ada data yang ditemukan.";
    } else {
        // Tampilkan hasil
        echo "<h3>Hasil Pencarian</h3>";
        echo "<table border='1'>";
        foreach ($results as $row) {
            echo "<tr><td>" . implode("</td><td>", $row) . "</tr>";
        }
        echo "</table>";

        // Ekspor ke Excel
        if (isset($_POST['export'])) {
            $fileName = 'hasil_pencarian.xlsx';
            exportToExcel($results, $fileName);
            echo "<p><a href='$fileName'>Download Laporan</a></p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian Data</title>
</head>
<body>
    <h2>Pencarian Data Pemasukan dan Penjualan</h2>
    <form method="POST" action="">
        <label for="start_date">Tanggal Awal:</label>
        <input type="date" name="start_date" required>
        <br><br>
        <label for="end_date">Tanggal Akhir:</label>
        <input type="date" name="end_date" required>
        <br><br>
        <input type="submit" name="search" value="Cari">
        <input type="submit" name="export" value="Cari dan Ekspor">
    </form>
</body>
</html>
