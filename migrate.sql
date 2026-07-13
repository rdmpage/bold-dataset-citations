-- migrate.sql — import the 280 BOLD DS-* DOIs that are in the DataCite Public Data
-- File 2025 (10.14454/t5qb-d995) but were absent from the 2024 harvest.
--
-- NOTE: the `dump_date` column already exists on `dataset` and the 2,340 existing
-- rows are already tagged '2024', so the ALTER/UPDATE steps are done. This script
-- only imports the new rows. (UPDATE below is a guarded no-op kept for safety.)
--
-- RUN FROM THE PROJECT ROOT (so the relative .import path resolves):
--     cd ~/Development/bold-dataset-citations
--     cp boldcite.db boldcite.db.bak-2026-07-07     # back up first!
--     # make sure nothing else has boldcite.db open (close DB browsers / the .bbprojectd)
--     sqlite3 boldcite.db < migrate.sql
--
-- Idempotent: re-running inserts nothing new (WHERE doi NOT IN ... guard).
--
-- After this, backfill name/url/json for the new stubs via the usual harvest:
--     php datacite-harvest/harvest-dois.php   (fetches DataCite JSON per DOI)
-- The rows land with id/doi/url set and name/json NULL until then.

.bail on
.echo on

-- (safety no-op) tag any still-untagged rows as the 2024 harvest
UPDATE dataset SET dump_date = '2024' WHERE dump_date IS NULL;

-- load the import CSV (id, doi, url) into a temp table
CREATE TEMP TABLE new_ds_import (id TEXT, doi TEXT, url TEXT);
.mode csv
.import --skip 1 missed_ds_2025_import.csv new_ds_import

-- insert only DOIs not already present; tag them as the 2025 DataCite dump
INSERT INTO dataset (id, doi, url, dump_date)
SELECT i.id, i.doi, i.url, '2025'
FROM new_ds_import i
WHERE i.doi NOT IN (SELECT doi FROM dataset WHERE doi IS NOT NULL);

DROP TABLE new_ds_import;

-- report: row counts by dump_date, and how many new rows still need metadata
.mode column
.headers on
SELECT dump_date, count(*) AS datasets FROM dataset GROUP BY dump_date ORDER BY dump_date;
SELECT count(*) AS new_rows_needing_harvest
FROM dataset WHERE dump_date = '2025' AND (json IS NULL OR json = '');
