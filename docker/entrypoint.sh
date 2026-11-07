#!/usr/bin/env bash
set -e

mkdir -p /var/moodledata /var/moodledata/cache /var/moodledata/lang /tmp/moodle_localcache /tmp/moodle_temp

# Pre-seed Vietnamese language pack if not yet present in dataroot
if [ -f /opt/moodle-lang/vi.zip ] && [ ! -d /var/moodledata/lang/vi ]; then
    unzip -q -o /opt/moodle-lang/vi.zip -d /var/moodledata/lang/
fi

chown -R www-data:www-data /var/moodledata /tmp/moodle_localcache /tmp/moodle_temp
chmod -R 0777 /tmp/moodle_localcache /tmp/moodle_temp
chmod -R 0775 /var/moodledata

exec "$@"
