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
$execution_time_bs = null;
$execution_time_ss = null;
$totalPemasukan = 0;  // Variabel untuk menghitung total pemasukan
$totalPengeluaran = 0;  // Variabel untuk menghitung total pengeluaran
$totalSaldo = 0;  // Variabel untuk menghitung saldo (pemasukan - pengeluaran)

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

    // Bandingkan waktu eksekusi Binary Search
    $start_time_bs = microtime(true);
    $filteredData = binarySearch($allData, $start_date, $end_date);
    $end_time_bs = microtime(true);
    $execution_time_bs = $end_time_bs - $start_time_bs;

    // Bandingkan waktu eksekusi Sequential Search (tanpa menampilkan data)
    $start_time_ss = microtime(true);
    sequentialSearch($allData, $start_date, $end_date); // Data tidak ditampilkan
    $end_time_ss = microtime(true);
    $execution_time_ss = $end_time_ss - $start_time_ss;

    // Hitung total pemasukan dan pengeluaran
    foreach ($filteredData as $data) {
        // Menghapus "Rp" dan tanda titik ribuan untuk konversi ke angka
        $pemasukan = str_replace(['Rp', '.'], '', $data['pemasukan']);
        $pengeluaran = str_replace(['Rp', '.'], '', $data['pengeluaran']);
        $totalPemasukan += (float)$pemasukan; // Menambahkan nilai pemasukan ke total
        $totalPengeluaran += (float)$pengeluaran; // Menambahkan nilai pengeluaran ke total
    }

    // Hitung total saldo
    $totalSaldo = $totalPemasukan - $totalPengeluaran;
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

// Fungsi Sequential Search untuk mencari index data berdasarkan periode
function sequentialSearch($data, $startDate, $endDate)
{
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
                        if ($execution_time_bs !== null) {
                            ?>
                            <!-- Card untuk Menampilkan Total Pemasukan, Pengeluaran, dan Saldo -->
                            <div class="card mt-4">
                                <div class="card-body">
                                    <h5 class="card-title">Ringkasan Keuangan</h5>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <div class="card text-white bg-success">
                                                <div class="card-body">
                                                    <h5 class="card-title">Total Pemasukan</h5>
                                                    <p class="card-text text-white">Rp <?php echo number_format($totalPemasukan, 0, ',', '.'); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="card text-white bg-danger">
                                                <div class="card-body">
                                                    <h5 class="card-title">Total Pengeluaran</h5>
                                                    <p class="card-text text-white">Rp <?php echo number_format($totalPengeluaran, 0, ',', '.'); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="card text-white bg-primary">
                                                <div class="card-body">
                                                    <h5 class="card-title">Saldo Akhir</h5>
                                                    <p class="card-text text-white">Rp <?php echo number_format($totalSaldo, 0, ',', '.'); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <p class="font-weight-bold">Waktu eksekusi Binary Search: <?php echo number_format($execution_time_bs, 6); ?> detik</p>
                                        <p class="font-weight-bold">Waktu eksekusi Sequential Search: <?php echo number_format($execution_time_ss, 6); ?> detik</p>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include "footer.php"; ?>
