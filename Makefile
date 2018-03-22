#!/usr/bin/make -f

cc_green="\033[0;32m" #Change text to green.
cc_end="\033[0m" #Change text back to normal.

APP_DIR=${PWD}/app
APP_URI=http://agov.dev
APP_PASSWORD=password

DRUSH_CMD=drush -r ${APP_DIR}
DB_URL=mysql://drupal:drupal@127.0.0.1/local
INSTALL_OPTIONS=configurable_profile_dependencies.configurable_modules[agov_default_content]=0 \
	configurable_profile_dependencies.configurable_modules[agov_password_policy]=0 \
	configurable_profile_dependencies.configurable_modules[agov_workbench]=0 \
	configurable_profile_dependencies.configurable_modules[agls]=0 \
	configurable_profile_dependencies.configurable_modules[agov_scheduled_updates]=0 \
	configurable_profile_dependencies.configurable_modules[agov_sitemap]=0

PHPCS_STANDARD="vendor/drupal/coder/coder_sniffer/Drupal"
PHPCS_EXTENSIONS="php,module,inc,install,test,profile,theme"
PHPCS_EXCLUSIONS=""
PHPCS_REPORT_FILE="build/logs/checkstyle.xml"
PHPCS_DIRS=agov/modules/custom agov/agov.install agov/agov.profile agov/src agov/tests

CIRCLE_PHP=/home/ubuntu/.phpenv/shims/php
CIRCLE_PHP_VERSION?=5.5.11

EXPORT_MODULES=agov agov_standard_page agov_article agov_publication agov_default_content agov_password_policy agov_scheduled_updates agov_media agov_social_icons

TEST_FILTER=--module agov

.PHONY: list build make make-contrib make-core install test config-export

# Display a list of the commands
list:
	@$(MAKE) -pRrq -f $(lastword $(MAKEFILE_LIST)) : 2>/dev/null | awk -v RS= -F: '/^# File/,/^# Finished Make data base/ {if ($$1 !~ "^[#.]") {print $$1n}}' | sort | egrep -v -e '^[^[:alnum:]]' -e '^$@$$'

# Build steps for local dev
build: init mkdirs lint-php make

# Builds steps for CI
ci-build: mkdirs ci-vhost make

clean:
	chmod u+w ${APP_DIR}/sites/default || true
	sudo rm -rf ${APP_DIR} agov/modules/contrib agov/themes/contrib agov/agov

init:
	@echo ${cc_green}">>> Installing dependencies..."${cc_end}
	composer install --prefer-dist --no-progress

mkdirs:
	@echo ${cc_green}">>> Creating dirs..."${cc_end}
	mkdir -p ${APP_DIR}/profiles ${APP_DIR}/sites/default/files/tmp ${APP_DIR}/sites/default/private ${APP_DIR}/sites/simpletest build/logs/simpletest ${APP_DIR}/browser_output
	chmod -R 2775 ${APP_DIR}/sites/default/files ${APP_DIR}/sites/default/private ${APP_DIR}/sites/simpletest
	chmod -R 777 ${APP_DIR}/sites/simpletest
	chmod -R 777 ${APP_DIR}/browser_output
	ln -sv ${PWD}/agov ${APP_DIR}/profiles/agov

make-contrib:
	cd ${APP_DIR} && ../bin/drush make -y profiles/agov/drupal-org.make --no-core --contrib-destination=profiles/agov

make-core:
	cd ${APP_DIR} && ../bin/drush make -y profiles/agov/drupal-org-core.make --no-cache --prepare-install

make: make-contrib make-core
	cd ${APP_DIR} && composer install --prefer-dist --no-progress

install:
	cd ${APP_DIR} && ../bin/drush site-install agov -y --site-name=aGov --account-pass='${APP_PASSWORD}' --db-url=${DB_URL}

install-no-optionals:
	cd ${APP_DIR} && ../bin/drush site-install agov -y --site-name=aGov --account-pass='${APP_PASSWORD}' --db-url=${DB_URL} ${INSTALL_OPTIONS}

lint-php:
	@echo ${cc_green}">>> Linting PHP..."${cc_end}
	bin/phpcs --standard=${PHPCS_STANDARD} --extensions=${PHPCS_EXTENSIONS} --ignore=${PHPCS_EXCLUSIONS} ${PHPCS_DIRS}

fix-php:
	@echo ${cc_green}">>> Fixing PHP..."${cc_end}
	bin/phpcbf --standard=${PHPCS_STANDARD} --extensions=${PHPCS_EXTENSIONS} --ignore=${PHPCS_EXCLUSIONS} ${PHPCS_DIRS}

ci-lint: ci-lint-php

ci-lint-php:
	@echo ${cc_green}">>> Linting PHP..."${cc_end}
	bin/phpcs --report=checkstyle --report-file=${PHPCS_REPORT_FILE} --standard=${PHPCS_STANDARD} --extensions=${PHPCS_EXTENSIONS} --ignore=${PHPCS_EXCLUSIONS} ${PHPCS_DIRS}

config-export:
	${DRUSH_CMD} config-devel-export ${EXPORT_MODULES}

content-export:
	${DRUSH_CMD} dcem agov_default_content

ci-vhost:
	@echo ${cc_green}"PHP version: "${CIRCLE_PHP_VERSION}${cc_end}
	sudo cp vhost /etc/apache2/sites-available/agov.conf
	sudo sed -i -e 's@##app.uri##@${APP_URI}@g' -e 's@##app.dir##@${APP_DIR}@g' -e 's@##php.version##@${CIRCLE_PHP_VERSION}@g' /etc/apache2/sites-available/agov
	a2ensite agov
	a2enmod rewrite
	sudo service apache2 restart

devify:
	chmod u+w ${APP_DIR}/sites/default
	cp ${APP_DIR}/sites/example.settings.local.php ${APP_DIR}/sites/default/settings.local.php
	${DRUSH_CMD} en -y simpletest config_devel

test:
ifdef TEST_FILE
	$(eval TEST_FILTER=--file $(TEST_FILE))
endif
ifdef TEST_CLASS
	$(eval TEST_FILTER=--class $(TEST_CLASS))
endif
	cd ${APP_DIR} && sudo -u www-data php ./core/scripts/run-tests.sh \
	--concurrency 8 \
	--verbose \
	--dburl ${DB_URL}  \
	--url ${APP_URI}/ \
	$(TEST_FILTER)

ci-test:
	sudo -u www-data ${CIRCLE_PHP} ./app/vendor/bin/phpunit --configuration ${PWD}/phpunit-circle.xml ./app/profiles/agov/tests
	# One remaining legacy Simpletest that is dependent on InstallerTestBase.
	sudo -u www-data ${CIRCLE_PHP} ./app/core/scripts/run-tests.sh --url ${APP_URI} --sqlite /tmp/test-db.sqlite --dburl sqlite://127.0.0.1//tmp/test-db.sqlite --class 'Drupal\agov\Tests\ConfigurableDependenciesTest'
