set shell := ["cmd.exe", "/c"]

# Reload fixtures
fix-load:
    symfony console doctrine:fixtures:load

# Drop table, reload schema and fixtures
reset-db:
    symfony console app:reset-db

# Loads fixtures and then start a dev server
run-dev:
    symfony console doctrine:fixtures:load && symfony serve

# Start a dev server
run:
    symfony serve
