#!/usr/bin/env bash
set -e

cd wordpress/wp-content/plugins/algolia

find . -iname "*.php" > list
# new template
# xgettext --from-code=utf-8 --copyright-holder="Algolia" --package-name=algolia --language=PHP --add-comments -f list --keyword=esc_html__ --keyword=__ --keyword=_e -o languages/algolia.pot


# update template
xgettext --from-code=utf-8 --copyright-holder="Algolia" --package-name=algolia --language=PHP --add-comments -f list --keyword=esc_html__ --keyword=__ --keyword=_e --join-existing -o languages/algolia.pot
rm list
cd ../../../../

