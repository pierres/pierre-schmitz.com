update:
	#!/usr/bin/env bash
	git ls-files | grep -Ev 'justfile|robots\.txt|favicon\.ico|\.git.*' | xargs rm -f
	find . -type d -empty -delete
	curl -s https://wordpress.org/latest.tar.gz | tar -xz --strip-components=1
	rm -rf wp-content/plugins/{akismet,hello.php}
	# wp-cli
	curl -so wp-cli https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
	chmod +x wp-cli
	# themes
	cd wp-content/themes
	rm -rf twenty*
	curl -so twentyseventeen.zip https://downloads.wordpress.org/theme/twentyseventeen.zip
	unzip twentyseventeen.zip
	rm twentyseventeen.zip
	cd - > /dev/null

deploy:
	./wp-cli core update-db

# vim: set ft=make :
