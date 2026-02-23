set shell := ["cmd.exe", "/c"]

# Drop table, reload schema and fixtures
reset-db:
    symfony console app:reset-db

# Resets DB and start a dev server
run:
    symfony console app:reset-db && symfony serve
