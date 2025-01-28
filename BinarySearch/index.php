<?php
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Fungsi Binary Search (tetap sama)
function binarySearch($array, $target) {
    $low = 0;
    $high = count($array) - 1;

    while ($low <= $high) {
        $mid = floor(($low + $high) / 2);
        if ($array[$mid] == $target) {
            return $mid;
        } elseif ($array[$mid] < $target) {
            $low = $mid + 1;
        } else {
            $high = $mid - 1;
        }
    }
    return -1;
}

echo "<h1>Data Penjualan Toko Bunga</h1>";

// Form upload dan pencarian tanggal
echo "<form method='POST' enctype='multipart/form-data'>
        <label for='excel_files'>Upload File Excel (.xlsx):</label>
        <input type='file' name='excel_files[]' multiple accept='.xlsx' required><br><br>
        <label for='start_date'>Tanggal Mulai:</label>
        <input type='date' name='start_date' value='" . (isset($_POST['start_date']) ? $_POST['start_date'] : '') . "' required>
        <label for='end_date'>Tanggal Selesai:</label>
        <input type='date' name='end_date' value='" . (isset($_POST['end_date']) ? $_POST['end_date'] : '') . "' required>
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
            echo "<p style='color: red;'>Error pada upload file ke-" . ($i + 1) . ". Pastikan file berformat .xlsx.</p>";
        }
    }

    if (!empty($validFiles)) {
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];

        if ($startDate && $endDate && $startDate > $endDate) {
            echo "<p style='color: red;'>Tanggal mulai harus lebih kecil dari tanggal selesai.</p>";
        } else {
            foreach ($validFiles as $filePath) {
                try {
                    $spreadsheet = IOFactory::load($filePath);

                    // Loop hanya pada sheet ke-2 sampai ke-5
                    for ($sheetIndex = 1; $sheetIndex <= 4; $sheetIndex++) {
                        $sheet = $spreadsheet->getSheet($sheetIndex);
                        if (!$sheet) continue;

                        $sheetName = $sheet->getTitle();
                        $data = $sheet->toArray();
                        $tanggalArray = [];
                        $dataTanggal = [];

                        foreach ($data as $index => $row) {
                            if ($index == 0) continue;
                            $tanggalRaw = $row[1];
                            $parts = explode(' ', $tanggalRaw);
                            if (count($parts) > 1) {
                                $tanggalOnly = trim($parts[1]);
                                $dateObject = DateTime::createFromFormat('j/n/Y', $tanggalOnly);
                                if ($dateObject) {
                                    $tanggalFormatted = $dateObject->format('Y-m-d');
                                    $tanggalArray[] = $tanggalFormatted;
                                    if (!isset($dataTanggal[$tanggalFormatted])) {
                                        $dataTanggal[$tanggalFormatted] = [];
                                    }
                                    $dataTanggal[$tanggalFormatted][] = $index;
                                }
                            }
                        }

                        sort($tanggalArray);

                        echo "<h2>File: " . basename($filePath) . ", Sheet: {$sheetName}</h2>";
                        echo "<table border='1' cellpadding='5'>";
                        echo "<thead><tr><th>NO</th><th>TANGGAL</th><th>DESKRIPSI</th><th>PEMASUKAN</th><th>PENGELUARAN</th><th>SALDO AKHIR</th></tr></thead>";
                        echo "<tbody>";

                        $foundData = false;
                        if ($startDate && $endDate) {
                            $currentDate = $startDate;
                            while ($currentDate <= $endDate) {
                                $indexFound = binarySearch($tanggalArray, $currentDate);

                                if ($indexFound != -1) {
                                    $foundData = true;
                                    $tanggalDitemukan = $tanggalArray[$indexFound];
                                    foreach ($dataTanggal[$tanggalDitemukan] as $indexData) {
                                        $row = $data[$indexData];
                                        echo "<tr>";
                                        foreach ($row as $cell) {
                                            echo "<td>{$cell}</td>";
                                        }
                                        echo "</tr>";
                                    }
                                }
                                $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                            }
                        }

                        if (!$foundData) {
                            echo "<tr><td colspan='6'>Tidak ada data yang sesuai pada rentang tanggal ini.</td></tr>";
                        }

                        echo "</tbody></table>";
                    }
                } catch (\Exception $e) {
                    echo "<p style='color: red;'>Error memproses file " . basename($filePath) . ": " . $e->getMessage() . "</p>";
                }
            }
        }
    }
}