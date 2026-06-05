#!/usr/bin/env bash

distDocDir=$(cd $(dirname "$0"); pwd)
originalPath=$(dirname "${distDocDir}")
cd "${originalPath}"

# brew unlink php
# brew link php@7.4

# rm -rf vendor node_modules

echo "******************************************************"
echo "Target Directory: $(pwd)"
echo "******************************************************"
mv '__Did_you_run_composer_update.txt' spec/tempfile
composer update
pnpm clean --lockfile
npm_config_prefer_online=true pnpm install --lockfile-only
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
export PATH="$SCRIPT_DIR/../vendor/bin:$PATH"
mv spec/tempfile '__Did_you_run_composer_update.txt'
rm package-lock.json

cd spec/run
echo "******************************************************"
echo "Target Directory: $(pwd)"
echo "******************************************************"
#../../node_modules/.bin/pnpm install --frozen-lockfile
pnpm clean --lockfile
pm_config_prefer_online=true pnpm install --lockfile-only
rm package-lock.json

cd ../run_v8
echo "******************************************************"
echo "Target Directory: $(pwd)"
echo "******************************************************"
#../../node_modules/.bin/pnpm install --frozen-lockfile
pnpm clean --lockfile
pm_config_prefer_online=true pnpm install --lockfile-only
rm package-lock.json

cd ../run-safari
echo "******************************************************"
echo "Target Directory: $(pwd)"
echo "******************************************************"
#../../node_modules/.bin/pnpm install --frozen-lockfile
pnpm clean --lockfile
pm_config_prefer_online=true pnpm install --lockfile-only
rm package-lock.json

# brew unlink php@7.4
# brew link php
