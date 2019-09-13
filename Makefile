.PHONY: update deploy

update:
	git ls-files | grep -Ev 'Makefile|robots\.txt|favicon\.ico|\.git.*' | xargs rm -f
	find . -type d -empty -delete
	curl -s https://wordpress.org/latest.tar.gz | tar -xz --strip-components=1
	curl -so wp-cli https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
	chmod +x wp-cli

deploy:
	sudo -u php-pierre ./wp-cli core update-db
