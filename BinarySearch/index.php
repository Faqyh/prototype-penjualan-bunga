<?php
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

function formatTanggalExcel($tanggalRaw) {
    $parts = explode(' ', $tanggalRaw); // Pisahkan "Selasa 1/10/2024"
    if (count($parts) > 1) {
        $tanggalOnly = trim($parts[1]); // Ambil "1/10/2024"
        $dateObject = DateTime::createFromFormat('j/n/Y', $tanggalOnly);
        return $dateObject ? $dateObject->format('Y-m-d') : null;
    }
    return null;
}

// Binary Search untuk mencari data berdasarkan tanggal
function binarySearch($data, $startDate, $endDate) {
    $low = 0;
    $high = count($data) - 1;
    $startIndex = -1;
    $endIndex = -1;

    // Cari indeks pertama dalam rentang menggunakan binary search
    while ($low <= $high) {
        $mid = floor(($low + $high) / 2);
        $currentDate = $data[$mid]['tanggal'];

        if ($currentDate >= $startDate && $currentDate <= $endDate) {
            $startIndex = $mid;
            $high = $mid - 1; // Coba cari yang lebih kecil
        } elseif ($currentDate < $startDate) {
            $low = $mid + 1;
        } else {
            $high = $mid - 1;
        }
    }

    if ($startIndex == -1) return []; // Tidak ada data dalam rentang

    // Cari indeks terakhir dalam rentang menggunakan binary search
    $low = 0;
    $high = count($data) - 1;
    while ($low <= $high) {
        $mid = floor(($low + $high) / 2);
        $currentDate = $data[$mid]['tanggal'];

        if ($currentDate >= $startDate && $currentDate <= $endDate) {
            $endIndex = $mid;
            $low = $mid + 1; // Coba cari yang lebih besar
        } elseif ($currentDate < $startDate) {
            $low = $mid + 1;
        } else {
            $high = $mid - 1;
        }
    }

    // **Meluas ke atas untuk mendapatkan semua data dengan tanggal awal**
    while ($startIndex > 0 && $data[$startIndex - 1]['tanggal'] == $data[$startIndex]['tanggal']) {
        $startIndex--;
    }

    // **Meluas ke bawah untuk mendapatkan semua data dengan tanggal akhir**
    while ($endIndex < count($data) - 1 && $data[$endIndex + 1]['tanggal'] == $data[$endIndex]['tanggal']) {
        $endIndex++;
    }

    return array_slice($data, $startIndex, ($endIndex - $startIndex + 1));
}


echo "<h1>Data Penjualan Toko Bunga</h1>";

// Form Upload dan Filter Rentang Tanggal
echo "<form method='POST' enctype='multipart/form-data'>
        <label for='excel_files'>Upload File Excel (.xlsx):</label>
        <input type='file' name='excel_files[]' multiple accept='.xlsx' required><br><br>
        <label for='start_date'>Tanggal Mulai:</label>
        <input type='date' name='start_date' required>
        <label for='end_date'>Tanggal Selesai:</label>
        <input type='date' name='end_date' required>
        <button type='submit'>Cari</button>
    </form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadedFiles = $_FILES['excel_files'];
    $fileCount = count($uploadedFiles['name']);

    $validFiles = [];
    for ($i = 0; $i < $fileCount; $i++) {
        if ($uploadedFiles['error'][$i] === UPLOAD_ERR_OK && pathinfo($uploadedFiles['name'][$i], PATHINFO_EXTENSION) === 'xlsx') {
            $tempPath = $uploadedFiles['tmp_name'][$i];
            $validFiles[] = $tempPath;
        } else {
            echo "<p style='color: red;'>Error pada file ke-" . ($i + 1) . ". Pastikan file berformat .xlsx.</p>";
        }
    }

    if (!empty($validFiles)) {
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];

        if ($startDate > $endDate) {
            echo "<p style='color: red;'>Tanggal mulai harus lebih kecil dari tanggal selesai.</p>";
        } else {
            $allData = [];

            foreach ($validFiles as $filePath) {
                try {
                    $spreadsheet = IOFactory::load($filePath);

                    // Loop hanya pada Sheet 2 sampai 5
                    for ($sheetIndex = 1; $sheetIndex <= 4; $sheetIndex++) {
                        $sheet = $spreadsheet->getSheet($sheetIndex);
                        $data = $sheet->toArray();

                        foreach ($data as $index => $row) {
                            if ($index == 0) continue; // Lewati Header

                            $tanggalFormatted = formatTanggalExcel($row[1]); // Konversi tanggal
                            if ($tanggalFormatted) {
                                $allData[] = [
                                    'tanggal' => $tanggalFormatted,
                                    'deskripsi' => $row[2], // Asumsi kolom deskripsi ada di index 2
                                    'pemasukan' => $row[3], // Asumsi kolom pemasukan ada di index 3
                                    'pengeluaran' => $row[4], // Asumsi kolom pengeluaran ada di index 4
                                    'saldo' => $row[5], // Asumsi kolom saldo ada di index 5
                                ];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    echo "<p style='color: red;'>Error memproses file " . basename($filePath) . ": " . $e->getMessage() . "</p>";
                }
            }

            // Urutkan berdasarkan tanggal **ascending**
            usort($allData, function($a, $b) {
                return strtotime($a['tanggal']) - strtotime($b['tanggal']);
            });

            // Gunakan Binary Search untuk mencari data dalam rentang tanggal
            $filteredData = binarySearch($allData, $startDate, $endDate);

            // Pastikan data hasil Binary Search tetap terurut
            usort($filteredData, function($a, $b) {
                return strtotime($a['tanggal']) - strtotime($b['tanggal']);
            });

            // **Menampilkan Data**
echo "<table border='1' cellpadding='5'>";
echo "<thead><tr><th>NO</th><th>TANGGAL</th><th>DESKRIPSI</th><th>PEMASUKAN</th><th>PENGELUARAN</th><th>SALDO AKHIR</th></tr></thead>";
echo "<tbody>";

if (!empty($filteredData)) {
    // Data sudah diurutkan sesuai tanggal, jadi kita cukup menampilkan tanpa perlu membalik urutan lagi
    foreach ($filteredData as $index => $data) {
        echo "<tr>";
        echo "<td>" . ($index + 1) . "</td>";
        echo "<td>{$data['tanggal']}</td>";
        echo "<td>{$data['deskripsi']}</td>";
        echo "<td>{$data['pemasukan']}</td>";
        echo "<td>{$data['pengeluaran']}</td>";
        echo "<td>{$data['saldo']}</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>Tidak ada data dalam rentang tanggal ini.</td></tr>";
}

echo "</tbody></table>";

        }
    }
}
