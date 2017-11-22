
all: clean update build

update:
	composer update --no-dev

build:
	box build

clean:
	rm -rf build/*
	rm -rf vendor

test:
	composer update --dev
	vendor/bin/phpunit
