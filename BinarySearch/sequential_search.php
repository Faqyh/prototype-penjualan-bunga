<?php
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

function formatTanggalExcel($tanggalRaw)
{
    $parts = explode(' ', $tanggalRaw);
    if (count($parts) > 1) {
        $tanggalOnly = trim($parts[1]);
        $dateObject = DateTime::createFromFormat('j/n/Y', $tanggalOnly);
        return $dateObject ? $dateObject->format('Y-m-d') : null;
    }
    return null;
}

// Sequential Search untuk mencari data berdasarkan tanggal
function sequentialSearch($data, $startDate, $endDate) {
    $result = [];
    $start_index = -1;
    $end_index = -1;

    // Cari index awal dan akhir
    for ($i = 0; $i < count($data); $i++) {
        $current_date = $data[$i]['tanggal'];

        if ($current_date >= $startDate) {
            if ($start_index === -1) {
                $start_index = $i;
            }
        }

        if ($current_date <= $endDate) {
            $end_index = $i; // Selalu update end_index untuk mendapatkan yang terakhir
        }
    }

    // Jika ditemukan, masukkan data dari index awal hingga akhir ke result
    if ($start_index !== -1 && $end_index !== -1) {
        for ($i = $start_index; $i <= $end_index; $i++) {
            $result[] = $data[$i];
        }
    }

    return $result;
}

echo "<h1>Data Penjualan Toko Bunga</h1>";

echo "<form method='POST'>
        <label for='start_date'>Tanggal Mulai:</label>
        <input type='date' name='start_date' required>
        <label for='end_date'>Tanggal Selesai:</label>
        <input type='date' name='end_date' required>
        <button type='submit'>Cari</button>
    </form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    if ($startDate > $endDate) {
        echo "<p style='color: red;'>Tanggal mulai harus lebih kecil dari tanggal selesai.</p>";
    } else {
        $allData = [];
        $uploadDir = 'uploads/';
        $files = glob($uploadDir . "*.xlsx");

        foreach ($files as $filePath) {
            try {
                $spreadsheet = IOFactory::load($filePath);
                for ($sheetIndex = 1; $sheetIndex <= 4; $sheetIndex++) {
                    $sheet = $spreadsheet->getSheet($sheetIndex);
                    $data = $sheet->toArray();

                    foreach ($data as $index => $row) {
                        if ($index == 0) continue;
                        $tanggalFormatted = formatTanggalExcel($row[1]);
                        if ($tanggalFormatted) {
                            $allData[] = [
                                'tanggal' => $tanggalFormatted,
                                'deskripsi' => $row[2],
                                'pemasukan' => $row[3],
                                'pengeluaran' => $row[4],
                                'saldo' => $row[5],
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                echo "<p style='color: red;'>Error memproses file " . basename($filePath) . ": " . $e->getMessage() . "</p>";
            }
        }

        // Mengurutkan semua data berdasarkan tanggal
        usort($allData, function ($a, $b) {
            return strtotime($a['tanggal']) - strtotime($b['tanggal']);
        });

        // Menampilkan seluruh data yang telah diurutkan
        // echo "<h2>Seluruh Data yang Diurutkan Berdasarkan Tanggal:</h2>";
        // echo "<table border='1' cellpadding='5'>";
        // echo "<thead><tr><th>NO</th><th>TANGGAL</th><th>DESKRIPSI</th><th>PEMASUKAN</th><th>PENGELUARAN</th><th>SALDO AKHIR</th></tr></thead>";
        // echo "<tbody>";
        
        // if (!empty($allData)) {
        //     foreach ($allData as $index => $data) {
        //         echo "<tr>";
        //         echo "<td>" . ($index + 1) . "</td>";
        //         echo "<td>{$data['tanggal']}</td>";
        //         echo "<td>{$data['deskripsi']}</td>";
        //         echo "<td>{$data['pemasukan']}</td>";
        //         echo "<td>{$data['pengeluaran']}</td>";
        //         echo "<td>{$data['saldo']}</td>";
        //         echo "</tr>";
        //     }
        // } else {
        //     echo "<tr><td colspan='6'>Tidak ada data yang ditemukan.</td></tr>";
        // }
        // echo "</tbody></table>";

        // Mengukur waktu eksekusi untuk Sequential Search
        $start_time = microtime(true);
        $filteredData = sequentialSearch($allData, $startDate, $endDate);
        $end_time = microtime(true);
        $execution_time = $end_time - $start_time;

        // Menampilkan data yang sudah difilter berdasarkan rentang tanggal
        echo "<h2>Data yang Ditemukan berdasarkan Rentang Tanggal:</h2>";
        echo "<table border='1' cellpadding='5'>";
        echo "<thead><tr><th>NO</th><th>TANGGAL</th><th>DESKRIPSI</th><th>PEMASUKAN</th><th>PENGELUARAN</th><th>SALDO AKHIR</th></tr></thead>";
        echo "<tbody>";

        if (!empty($filteredData)) {
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
        echo "<p>Waktu eksekusi Sequential Search: " . number_format($execution_time, 6) . " detik</p>";
    }
}
?>
