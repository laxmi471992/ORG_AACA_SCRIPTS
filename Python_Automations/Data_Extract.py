"""
@description
- Connects to MySQL and exports all rows from a single table to an Excel file.
- Uses secure defaults for host/user/database and supports TLS configuration.
- Prompts user only for table name and output file name in interactive usage.
- Appends .xlsx automatically when output extension is not provided.
- Handles common runtime failures (auth/network/missing table/file errors) with clear messages.

CHANGELOG
- 2026-03-21: Added secure connection defaults and password prompt fallback.
- 2026-03-21: Limited prompts to table and output file name.
- 2026-03-21: Added connection timeout/retry support for unstable networks.
- 2026-03-21: Added structured exception handling for common database and file errors.
- 2026-03-21: Added module-level documentation and concise section comments.
"""

import argparse
import os
import re
import sys
import time
from getpass import getpass

import pandas as pd
import pymysql


DEFAULT_OUTPUT_DIR = "/Users/ashishdiwakar/ASHISH/KEANT_Technologies/AACANet/Work/Data_Extract"
DEFAULT_DB_HOST = "192.168.13.167"
DEFAULT_DB_USER = "pipewaydb"
DEFAULT_DB_NAME = "aaca_live"
DEFAULT_DB_PASSWORD_ENV = "MYSQL_PASSWORD"
DEFAULT_CONNECT_TIMEOUT = 30
DEFAULT_CONNECT_RETRIES = 3


# Data access layer: validate target table, open MySQL connection, and return rows.
def fetch_table_data(
	host: str,
	port: int,
	user: str,
	password: str,
	database: str,
	table_name: str,
	ssl_disabled: bool,
	ssl_ca: str,
	ssl_verify_cert: bool,
	connect_timeout: int,
	connect_retries: int,
) -> pd.DataFrame:
	"""Fetch all rows from the provided MySQL table into a DataFrame."""
	# Keep table interpolation safe by allowing only simple identifier format.
	if not re.fullmatch(r"[A-Za-z0-9_]+", table_name):
		raise ValueError("Invalid table name. Only letters, numbers, and underscore are allowed.")
	# Fail fast if a CA path is provided but does not exist.
	if ssl_ca and not os.path.isfile(ssl_ca):
		raise FileNotFoundError(f"SSL CA file not found: {ssl_ca}")

	# Build TLS options unless user explicitly disabled TLS.
	ssl_options = None
	if not ssl_disabled:
		ssl_options = {"check_hostname": True}
		if ssl_ca:
			ssl_options["ca"] = ssl_ca
		ssl_options["verify_mode"] = "required" if ssl_verify_cert else "none"

	# Retry for transient network/connectivity failures.
	last_error = None
	for attempt in range(1, connect_retries + 1):
		try:
			connection = pymysql.connect(
				host=host,
				port=port,
				user=user,
				password=password,
				database=database,
				charset="utf8mb4",
				cursorclass=pymysql.cursors.DictCursor,
				ssl=ssl_options,
				connect_timeout=connect_timeout,
				read_timeout=connect_timeout,
				write_timeout=connect_timeout,
			)

			try:
				# Query all rows from the selected table.
				query = f"SELECT * FROM `{table_name}`"
				return pd.read_sql(query, connection)
			finally:
				# Always release DB connection.
				connection.close()
		except pymysql.err.OperationalError as exc:
			last_error = exc
			error_code = exc.args[0] if exc.args else None

			# Authentication/authorization/database problems are not transient.
			if error_code in {1044, 1045, 1049}:
				raise

			if attempt < connect_retries:
				# Backoff helps on unstable links/VPN.
				time.sleep(min(3 * attempt, 10))
				continue
			raise

	raise last_error if last_error else RuntimeError("MySQL connection failed with unknown error.")


# Output layer: normalize output name and write DataFrame to .xlsx.
def export_to_excel(df: pd.DataFrame, output_dir: str, output_name: str) -> str:
	"""Write DataFrame to an Excel file and return file path."""
	os.makedirs(output_dir, exist_ok=True)
	# Enforce expected Excel extension for consistent output handling.
	if not output_name.lower().endswith(".xlsx"):
		output_name = f"{output_name}.xlsx"
	output_file = os.path.join(output_dir, output_name)
	df.to_excel(output_file, index=False)
	return output_file


# CLI layer: define runtime options and environment-backed defaults.
def get_args() -> argparse.Namespace:
	parser = argparse.ArgumentParser(
		description="Extract all rows from a MySQL table into an Excel file."
	)
	parser.add_argument("--host", default=os.getenv("MYSQL_HOST", DEFAULT_DB_HOST))
	parser.add_argument("--port", type=int, default=int(os.getenv("MYSQL_PORT", "3306")))
	parser.add_argument("--user", default=os.getenv("MYSQL_USER", DEFAULT_DB_USER))
	parser.add_argument(
		"--password",
		default=os.getenv(DEFAULT_DB_PASSWORD_ENV, ""),
		help=(
			"MySQL password. Prefer setting via env var MYSQL_PASSWORD "
			"instead of passing on command line."
		),
	)
	parser.add_argument("--database", default=os.getenv("MYSQL_DATABASE", DEFAULT_DB_NAME))
	parser.add_argument("--table", help="MySQL table name")
	parser.add_argument("--output-file", help="Excel output file name (without .xlsx is allowed)")
	parser.add_argument("--output-dir", default=DEFAULT_OUTPUT_DIR)
	parser.add_argument(
		"--connect-timeout",
		type=int,
		default=int(os.getenv("MYSQL_CONNECT_TIMEOUT", str(DEFAULT_CONNECT_TIMEOUT))),
		help="MySQL connect/read/write timeout in seconds.",
	)
	parser.add_argument(
		"--connect-retries",
		type=int,
		default=int(os.getenv("MYSQL_CONNECT_RETRIES", str(DEFAULT_CONNECT_RETRIES))),
		help="Number of connection retry attempts on transient failures.",
	)
	parser.add_argument(
		"--ssl-disabled",
		action="store_true",
		help="Disable TLS/SSL for MySQL connection (not recommended).",
	)
	parser.add_argument(
		"--ssl-ca",
		default=os.getenv("MYSQL_SSL_CA", ""),
		help="Path to CA certificate for TLS verification.",
	)
	parser.add_argument(
		"--ssl-no-verify",
		action="store_true",
		help="Use TLS but skip certificate verification (less secure).",
	)
	parser.add_argument(
		"--interactive",
		action="store_true",
		help="Prompt for table and output file name in terminal.",
	)
	return parser.parse_args()


# Prompt helper: read required/optional string input with default support.
def _prompt_text(prompt: str, current: str = "", required: bool = False) -> str:
	while True:
		display_default = f" [{current}]" if current else ""
		value = input(f"{prompt}{display_default}: ").strip()
		if value:
			return value
		if current:
			return current
		if not required:
			return ""
		print(f"{prompt} is required.")


# Interactive mode only asks for table and output file name.
def prompt_for_inputs(args: argparse.Namespace) -> argparse.Namespace:
	args.table = _prompt_text("Table", args.table or "", required=True)
	args.output_file = _prompt_text("Output file name", args.output_file or "", required=True)
	return args


# Main workflow: collect inputs, validate, fetch data, export file, and report errors.
def main() -> None:
	try:
		# Resolve runtime args and collect required interactive fields if needed.
		args = get_args()
		if args.interactive or not args.table or not args.output_file:
			args = prompt_for_inputs(args)
		# Never echo password in terminal; request hidden input if absent.
		if not args.password:
			args.password = getpass("MySQL password: ")

		# Validate required values before starting DB operations.
		if not args.database:
			raise ValueError("Database name is required. Pass --database or set MYSQL_DATABASE.")
		if not args.table:
			raise ValueError("Table name is required. Pass --table or use interactive prompts.")
		if not args.output_file:
			raise ValueError("Output file name is required. Pass --output-file or use interactive prompts.")
		if not args.password:
			raise ValueError("MySQL password is required. Set MYSQL_PASSWORD, pass --password, or enter when prompted.")
		if args.connect_timeout < 1:
			raise ValueError("--connect-timeout must be >= 1")
		if args.connect_retries < 1:
			raise ValueError("--connect-retries must be >= 1")

		# Run extraction and write output Excel file.
		df = fetch_table_data(
			host=args.host,
			port=args.port,
			user=args.user,
			password=args.password,
			database=args.database,
			table_name=args.table,
			ssl_disabled=args.ssl_disabled,
			ssl_ca=args.ssl_ca,
			ssl_verify_cert=not args.ssl_no_verify,
			connect_timeout=args.connect_timeout,
			connect_retries=args.connect_retries,
		)

		output_file = export_to_excel(df, args.output_dir, args.output_file)
		print(f"Done. Exported {len(df)} rows from '{args.table}' to: {output_file}")
	# Present user-friendly error messages for known failure classes.
	except ValueError as exc:
		print(f"Input error: {exc}")
		sys.exit(1)
	except FileNotFoundError as exc:
		print(f"File not found: {exc}")
		sys.exit(1)
	except PermissionError as exc:
		print(f"Permission denied: {exc}")
		sys.exit(1)
	except pymysql.err.OperationalError as exc:
		error_code = exc.args[0] if exc.args else None
		error_text = str(exc)
		if error_code in {1044, 1045}:
			print("MySQL authentication failed: invalid user/password or insufficient privileges.")
		elif error_code == 1049:
			print(f"Unknown database '{args.database}'. Verify --database or MYSQL_DATABASE.")
		elif "CERTIFICATE_VERIFY_FAILED" in error_text:
			print(
				"MySQL SSL certificate verification failed (self-signed certificate chain). "
				"Preferred fix: provide server CA using --ssl-ca <ca-cert-path> or MYSQL_SSL_CA. "
				"Fallback (less secure): use --ssl-no-verify."
			)
		elif error_code == 2003:
			print(
				f"Cannot connect to MySQL at {args.host}:{args.port}. "
				"Check VPN/network/firewall and server availability."
			)
		else:
			print(f"MySQL operational error: {exc}")
		sys.exit(1)
	except pymysql.err.ProgrammingError as exc:
		error_code = exc.args[0] if exc.args else None
		if error_code == 1146:
			print(f"Table '{args.table}' does not exist in database '{args.database}'.")
		else:
			print(f"MySQL query error: {exc}")
		sys.exit(1)
	except OSError as exc:
		print(f"File system error while writing output: {exc}")
		sys.exit(1)
	except Exception as exc:
		print(f"Unexpected error: {exc}")
		sys.exit(1)


if __name__ == "__main__":
	main()

	#For DEmo to team 
