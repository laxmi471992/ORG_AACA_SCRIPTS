#!/bin/bash
set -euo pipefail

# ============================================================================
# DOCUMENT LOADING CLEANUP SCRIPT
# ============================================================================
# Author: Vishal Kulkarni           Date: 13 FEB 2026
# Description:
# - Cleans the SFTP downloaded PDF data at /var/www/html/bi/dist/BAYREADFILE.
# - Deletes PDF files (case-insensitive .pdf) older than 7 days from current date.
# - Intended for daily cron execution to keep only one week's PDFs on server.
# - Logs deletions with timestamp, size, and status to
#   /home/pipewayweb/log/doc_loading_cleanup_log.txt.
# - Prunes the log file to keep only the last 7 days of entries.
# - Writes an end-of-run summary with deleted file count and total bytes.
#
# CHANGELOG:
# ----------------------------------------------------------------------------
# Change Tag | Date       | Author              | Description. 
# ----------------------------------------------------------------------------
# 
# ----------------------------------------------------------------------------


# ============================================================================
# DECLARATIONS and EXCEPTION HANDLERS SETUP
# ============================================================================

# Target directory where documents are downloaded via SFTP.
pdf_directory="/var/www/html/bi/dist/BAYREADFILE"

# Log file to track deletions.
log_directory="/home/pipewayweb/log"
log_file="${log_directory}/doc_loading_cleanup_log.txt"

# Log an error with context.
log_error() {
	local message="$1"
	local ts
	ts=$(date "+%Y-%m-%d %H:%M:%S")
	echo "${ts} | ERROR | ${message}" >> "$log_file" 2>/dev/null || true
	printf "%s | ERROR | %s\n" "$ts" "$message" >&2
}

# Trap unexpected errors and log them.
on_error() {
	local exit_code=$?
	local line_no=$1
	log_error "Script failed at line ${line_no} with exit code ${exit_code}."
	exit "$exit_code"
}
trap 'on_error $LINENO' ERR

# ============================================================================
# MAINLINE SCRIPT STARTS HERE
# ============================================================================

# Ensure the target directory exists before cleanup.
if [[ ! -d "$pdf_directory" ]]; then
	echo "Directory not found: $pdf_directory" >&2
	exit 1
fi

# Ensure the log directory exists.
if [[ ! -d "$log_directory" ]]; then
	echo "Log directory not found: $log_directory" >&2
	exit 1
fi

# Ensure the log file is writable.
if ! touch "$log_file" 2>/dev/null; then
	echo "Log file not writable: $log_file" >&2
	exit 1
fi

# Keep only the last 7 days of log entries.
cutoff_date=$(date -d "7 days ago" "+%Y-%m-%d")
tmp_log=$(mktemp)
awk -v cutoff="$cutoff_date" '$1 >= cutoff' "$log_file" > "$tmp_log"
mv "$tmp_log" "$log_file"

# Remove PDFs older than 7 days (case-insensitive extension match) and log.
total_deleted_bytes=0
deleted_files_count=0
find "$pdf_directory" -type f -iname "*.pdf" -mtime +7 -print0 | while IFS= read -r -d '' file; do
	deleted_at=$(date "+%Y-%m-%d %H:%M:%S")
	file_size_bytes=$(stat -c "%s" "$file" 2>/dev/null || echo "0")
	if rm -f "$file"; then
		status="DELETED"
		total_deleted_bytes=$((total_deleted_bytes + file_size_bytes))
		deleted_files_count=$((deleted_files_count + 1))
	else
		status="FAILED"
		log_error "Failed to delete ${file}"
	fi
	echo "${deleted_at} | ${status} | ${file_size_bytes} bytes | ${file}" >> "$log_file"
done

# Log end-of-run summary.
summary_time=$(date "+%Y-%m-%d %H:%M:%S")
echo "${summary_time} | SUMMARY | deleted_files=${deleted_files_count} | total_deleted_bytes=${total_deleted_bytes}" >> "$log_file"

# ============================================================================
# END OF SCRIPT 
# ============================================================================