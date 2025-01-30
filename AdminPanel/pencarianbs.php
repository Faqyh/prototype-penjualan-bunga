<?php
include "../koneksi.php";  // Koneksi ke database
include "header.php";      // Header halaman
require '../vendor/autoload.php';  // Autoload PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

function formatTanggalExcel($tanggalRaw)
{
    $parts = explode(' ', $tanggalRaw); // Pisahkan "Selasa 1/10/2024"
    if (count($parts) > 1) {
        $tanggalOnly = trim($parts[1]); // Ambil "1/10/2024"
        $dateObject = DateTime::createFromFormat('j/n/Y', $tanggalOnly);
        return $dateObject ? $dateObject->format('Y-m-d') : null;
    }
    return null;
}

// Inisialisasi default nilai $execution_time
$execution_time = null;

// Proses Pencarian Data Pemasukan Berdasarkan Periode
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);

    // Menyimpan data hasil pencarian
    $allData = [];

    // Menentukan direktori tempat file dokumen berada
    $directory = "../uploads/";
    $files = glob($directory . "*.xlsx*");

    foreach ($files as $file) {
        $spreadsheet = IOFactory::load($file);

        // Menyaring data berdasarkan periode
        $data = [];
        for ($sheetIndex = 1; $sheetIndex <= 4; $sheetIndex++) {
            try {
                $sheet = $spreadsheet->getSheet($sheetIndex);
                $sheetData = $sheet->toArray();
                foreach ($sheetData as $index => $row) {
                    if ($index == 0) continue; // Lewati Header

                    $tanggalFormatted = formatTanggalExcel($row[1]); // Konversi tanggal
                    if ($tanggalFormatted) {
                        $allData[] = [
                            'tanggal' => $tanggalFormatted,
                            'deskripsi' => $row[2], // Asumsi kolom deskripsi ada di index 2
                            'pemasukan' => $row[3], // Asumsi kolom pemasukan ada di index 3
                            'pengeluaran' => $row[4], // Asumsi kolom pengeluaran ada di index 4
                        ];
                    }
                }
            } catch (\Exception $e) {
                echo "<p style='color: red;'>Error memproses file " . basename($file) . ": " . $e->getMessage() . "</p>";
            }
        }
    }

    // Urutkan data berdasarkan tanggal sebelum pencarian
    usort($allData, function ($a, $b) {
        return strtotime($a['tanggal']) - strtotime($b['tanggal']);
    });

    $start_time = microtime(true); // Mulai pencatatan waktu eksekusi Binary Search

    // Gunakan Binary Search untuk mencari data dalam rentang tanggal
    $filteredData = binarySearch($allData, $start_date, $end_date);

    $end_time = microtime(true); // Selesai pencatatan waktu eksekusi Binary Search
    $execution_time = $end_time - $start_time; // Hitung waktu eksekusi Binary Search

    // Pastikan data hasil Binary Search tetap terurut
    usort($filteredData, function ($a, $b) {
        return strtotime($a['tanggal']) - strtotime($b['tanggal']);
    });
} else {
    $allData = [];
    $filteredData = [];
}

// Fungsi Binary Search untuk mencari index data berdasarkan periode
function binarySearch($data, $startDate, $endDate)
{
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

?>

<div class="main-content-inner">
    <div class="row">
        <!-- Tabel Data Pemasukan -->
        <div class="col-12 mt-5">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title">Pencarian Data Pemasukan Berdasarkan Rentang Tanggal</h4>

                    <!-- Form untuk Pencarian Data Pemasukan Berdasarkan Periode -->
                    <form method="POST">
                        <div class="form-group">
                            <label for="start_date">Periode Awal</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">Periode Akhir</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-rounded">Cari Data</button>
                    </form><br>

                    <!-- Tabel -->
                    <div class="data-tables datatable-primary">
                        <table id="dataTable2" class="text-center" style="width:100%">
                            <thead class="text-capitalize">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Deskripsi</th>
                                    <th>Pemasukan</th>
                                    <th>Pengeluaran</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Loop untuk menampilkan data hasil pencarian
                                if (!empty($filteredData)) {
                                    foreach ($filteredData as $index => $data) {
                                        echo "<tr>";
                                        echo "<td>" . ($index + 1) . "</td>";
                                        echo "<td>{$data['tanggal']}</td>";
                                        echo "<td>{$data['deskripsi']}</td>";
                                        echo "<td>{$data['pemasukan']}</td>";
                                        echo "<td>{$data['pengeluaran']}</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6'>Tidak ada data dalam rentang tanggal ini.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php
                        if ($execution_time !== null) {
                            echo "<p>Waktu eksekusi Binary Search: " . number_format($execution_time, 6) . " detik</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>