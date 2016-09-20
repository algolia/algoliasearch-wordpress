#!/usr/bin/env bash
set -e

find . -iname "*.php" > list
xgettext --from-code=utf-8 --copyright-holder="Algolia" --package-name=algolia --language=PHP -f list --keyword=esc_html_e --keyword=esc_html__ --keyword=__ --keyword=_e -o languages/algolia.pot
rm list


