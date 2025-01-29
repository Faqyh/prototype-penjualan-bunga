<?php
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "toko_bunga";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];
    $uploadDir = 'uploads/';
    $filePath = $uploadDir . basename($file['name']);

    // Periksa apakah file sudah ada di direktori
    if (file_exists($filePath)) {
        echo "<p style='color: red;'>File sudah ada, tidak perlu diunggah lagi.</p>";
    } else {
        // Proses upload file
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Menyimpan informasi file ke database
            $stmt = $conn->prepare("INSERT INTO dokumen (nama_file, lokasi_file) VALUES (?, ?)");
            $stmt->bind_param("ss", $file['name'], $filePath);
            $stmt->execute();
            echo "<p>File berhasil diunggah.</p>";
        } else {
            echo "<p style='color: red;'>Gagal mengunggah file.</p>";
        }
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("SELECT lokasi_file FROM dokumen WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();
    
    if ($file && unlink($file['lokasi_file'])) {
        $stmt = $conn->prepare("DELETE FROM dokumen WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo "<p>File berhasil dihapus.</p>";
    } else {
        echo "<p style='color: red;'>Gagal menghapus file.</p>";
    }
}

$result = $conn->query("SELECT * FROM dokumen");

echo "<h1>Manajemen Dokumen</h1>";
echo "<form method='POST' enctype='multipart/form-data'>
        <input type='file' name='excel_file' accept='.xlsx' required>
        <button type='submit'>Upload</button>
      </form>";

echo "<h2>Daftar Dokumen</h2>";
echo "<table border='1'>
        <tr>
            <th>ID</th>
            <th>Nama File</th>
            <th>Aksi</th>
        </tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$row['id']}</td>
            <td><a href='{$row['lokasi_file']}' target='_blank'>{$row['nama_file']}</a></td>
            <td><a href='?delete={$row['id']}' onclick='return confirm(\"Hapus file ini?\")'>Hapus</a></td>
          </tr>";
}
echo "</table>";
$conn->close();
?>
