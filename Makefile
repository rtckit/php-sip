# Not much to see here ... just a bunch of developer helpers
REPOSITORY=rtckit/php-sip
RUN_CMD=docker run --name php-sip --rm -it -v `pwd`/reports:/opt/php-sip/reports:rw ${REPOSITORY}
RUN_PHP_CMD=${RUN_CMD} php -d memory_limit=-1

image:
	docker build -t ${REPOSITORY} -f ./etc/Dockerfile .

debug-image:
	docker build -t ${REPOSITORY}-debug -f ./etc/Dockerfile.xdebug .

test: image
	${RUN_PHP_CMD} -d memory_limit=-1 ./vendor/bin/phpunit -c ./etc/phpunit.xml.dist --debug

cover: image
	sudo rm -rf reports/coverage
	${RUN_PHP_CMD} ./vendor/bin/phpunit -c ./etc/phpunit.xml.dist --coverage-text --coverage-html=reports/coverage

stan: image
	${RUN_PHP_CMD} ./vendor/bin/phpstan analyse -c ./etc/phpstan.neon -n -vvv --ansi --level=max src

psalm: image
	${RUN_PHP_CMD} ./vendor/bin/psalm --config=./etc/psalm.xml --show-info=true
	# ${RUN_PHP_CMD} ./vendor/bin/psalm --config=./etc/psalm.xml --php-version=8.0

ci: stan psalm cover

examples: image
	${RUN_PHP_CMD} ./examples/01-parse-request.php
	${RUN_PHP_CMD} ./examples/02-render-request.php
	${RUN_PHP_CMD} ./examples/03-parse-response.php
	${RUN_PHP_CMD} ./examples/04-render-response.php
	${RUN_PHP_CMD} ./examples/05-stream-parse.php
	${RUN_PHP_CMD} ./examples/99-crude-benchmark.php

profiler: debug-image
	${RUN_CMD}-debug php -d memory_limit=-1 ./examples/99-crude-benchmark.php

clean:
	rm -rf `cat .gitignore`
