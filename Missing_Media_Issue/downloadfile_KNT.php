<?php
/**
 * ============================================================================
 * DOCUMENT DOWNLOAD HANDLER
 * ============================================================================
 *
 * Author      KEANT Technologies
 * Description
 * This script takes nama and remote from the request, runs an SFTP download helper
 * to fetch the file into a local temp directory, verifies the file exists, then streams it
 * back to the user with proper headers and deletes the local copy afterward.
 * It also derives a MIME type based on the file extension and uses finfo to set
 * the response content type.
 * If the download fails or the file isn’t found, it returns a simple error message.
 *
 * CHANGELOG:
 * ----------------------------------------------------------------------------
 * Version | Date       | Author              | Description
 * ----------------------------------------------------------------------------
 *         |            |                     |
 * ----------------------------------------------------------------------------
 */
session_start();
error_reporting(1);

// Request inputs
$name = $_GET['nama'];
$path = $_GET['remote'];

// Normalize /BAY/judmedia to the SFTP E: drive mapping
if (strpos($path, '/BAY/judmedia/') === 0) {
  $path = '/E:/media/judmedia/' . substr($path, strlen('/BAY/judmedia/'));
}

$file_path = '/var/www/html/bi/dist/BAYREADFILE/' . $name;

// Run the SFTP fetch script with explicit parameters
$cmd = '/var/www/html/cron/document_loading_KNT.sh';
$remote_arg = escapeshellarg($path);
$name_arg = escapeshellarg($name);
exec("$cmd $remote_arg $name_arg 2>&1", $output, $return_var);
if ($return_var === 0) {
  if (file_exists($file_path)) {
    // Resolve a reliable MIME type for the downloaded file
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_path);
    finfo_close($finfo);

    // Prepare headers for download
    $encoded_filename = rawurlencode($name);
    $safe_filename = basename($name);

    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . $safe_filename . '"; filename*=UTF-8\'\'' . $encoded_filename);
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));

    // Stream file to client and clean up local copy
    ob_clean();
    flush();
    readfile($file_path);
    unlink($file_path);
    exit;
  } else {
    // File missing after successful script run
    echo 'File not found.';
    exit;
  }
} else {
  // Script execution failed
  echo 'Something went wrong!';
}
?>
