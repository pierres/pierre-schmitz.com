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
	# plugins
	cd wp-content/plugins
	## disable-remove-google-fonts
	rm -rf disable-remove-google-fonts
	curl -so disable-remove-google-fonts.zip https://downloads.wordpress.org/plugin/disable-remove-google-fonts.zip
	unzip disable-remove-google-fonts.zip
	rm disable-remove-google-fonts.zip
	## easy-wp-smtp
	rm -rf easy-wp-smtp
	curl -so easy-wp-smtp.zip https://downloads.wordpress.org/plugin/easy-wp-smtp.zip
	unzip easy-wp-smtp.zip
	rm easy-wp-smtp.zip
	## ..
	cd - > /dev/null

deploy:
	./wp-cli core update-db
	./wp-cli plugin activate disable-remove-google-fonts easy-wp-smtp
	systemctl restart php-fpm@pierre.service

# vim: set ft=make :
