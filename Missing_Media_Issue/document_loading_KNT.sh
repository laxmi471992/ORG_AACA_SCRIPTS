#!/bin/bash
set -x

# ============================================================================
# DOCUMENT LOADING SCRIPT
# ============================================================================
#
# Author: KEANT Technologies
# Description: Accepts a remote path and local file name, validates inputs, then
#              SFTP-downloads the file from the BAY server into the local working
#              directory and applies permissions to the downloaded file.
#
# CHANGELOG:
# ----------------------------------------------------------------------------
# Version | Date       | Author              | Description
# ----------------------------------------------------------------------------
#         |            |                     |
# ----------------------------------------------------------------------------
#
# Args: <remote_path> <local_name>

# Input parameters and destination directory
dest_directory="/var/www/html/bi/dist/BAYREADFILE"
remote_path="$1"
local_name="$2"

# Normalize /BAY/judmedia to the SFTP E: drive mapping
if [ "${remote_path#"/BAY/judmedia/"}" != "$remote_path" ]; then
	remote_path="/E:/media/judmedia/${remote_path#"/BAY/judmedia/"}"
fi


# Validate required parameters
if [ -z "$remote_path" ] || [ -z "$local_name" ]; then
	echo "Usage: $0 <remote_path> <local_name>" >&2
	exit 2
fi

# Ensure we are in the destination directory before downloading
cd "$dest_directory" || exit 1

# Download the remote file to the specified local name
sftp -i /var/www/.ssh/id_rsa PipeBay@192.168.21.26 <<EOF
lcd $dest_directory
get "$remote_path" "$local_name"
exit
EOF

# Apply permissive permissions for downstream access
chmod 777 "$local_name"