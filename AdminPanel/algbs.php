<?php
include "../koneksi.php";  // Koneksi ke database
include "header.php";      // Header halaman
require '../vendor/autoload.php';  // Autoload PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

// Proses Pencarian Data Pemasukan Berdasarkan Periode
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
    $periode_awal = mysqli_real_escape_string($conn, $_POST['periode_awal']);
    $periode_akhir = mysqli_real_escape_string($conn, $_POST['periode_akhir']);

    // Mengubah periode menjadi tanggal pertama dan terakhir pada bulan tersebut
    $periode_awal = date('Y-m-01', strtotime($periode_awal));
    $periode_akhir = date('Y-m-t', strtotime($periode_akhir));
    
    // Menyimpan data hasil pencarian
    $result_data = [];

    // Menentukan direktori tempat file dokumen berada
    $directory = "../uploads/";
    $files = glob($directory . "*.xlsx*");

    foreach ($files as $file) {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        
        // Menyaring data berdasarkan periode
        $data = $sheet->toArray();
        
        // Menyimpan data ke dalam array result_data
        foreach ($data as $row) {
            $date = $row[0]; // Asumsikan kolom pertama adalah tanggal
            if ($date >= $periode_awal && $date <= $periode_akhir) {
                $result_data[] = $row;
            }
        }
    }

    // Menampilkan data dalam bentuk tabel HTML
    if (!empty($result_data)) {
        echo "<table border='1'>";
        echo "<tr><th>Tanggal</th><th>Data 1</th><th>Data 2</th><th>Data 3</th></tr>"; // Sesuaikan header tabel dengan kolom data
        foreach ($result_data as $row) {
            echo "<tr>";
            foreach ($row as $cell) {
                echo "<td>" . htmlspecialchars($cell) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Tidak ada data yang ditemukan untuk periode tersebut.";
    }
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
                    <form action="pencarianbs.php" method="POST">
                        <div class="form-group">
                            <label for="periode_awal">Periode Awal (YYYY-MM-DD)</label>
                            <input type="date" name="periode_awal" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="periode_akhir">Periode Akhir (YYYY-MM-DD)</label>
                            <input type="date" name="periode_akhir" class="form-control" required>
                        </div>
                        <button type="submit" name="search" class="btn btn-primary btn-rounded">Cari Data</button>
                    </form><br>

                    <!-- Tabel -->
                    <div class="data-tables datatable-primary">
                        <table id="dataTable2" class="text-center" style="width:100%">
                            <thead class="text-capitalize">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Dokumen</th>
                                    <th>Tanggal</th>
                                    <th>Pemasukan/Pengeluaran</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Loop untuk menampilkan data hasil pencarian
                                $no = 1;
                                foreach ($result_data as $file => $data) {
                                    foreach ($data as $row) {
                                        echo "<tr>";
                                        echo "<td>" . $no++ . "</td>";
                                        echo "<td>" . basename($file) . "</td>";
                                        echo "<td>" . date('Y-m-d', strtotime($row[0])) . "</td>"; // Anggap tanggal ada di kolom pertama
                                        echo "<td>" . $row[1] . "</td>"; // Anggap pemasukan/pengeluaran ada di kolom kedua
                                        echo "</tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>
