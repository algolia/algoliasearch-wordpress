#! /usr/bin/env bash

PROG="$0"
APPLICATION_ID=
API_KEY=
SEARCH_ONLY_API_KEY=
INDEX_PREFIX=worpdress_
BASE_URL=http://mywordpress.com/

env

cd `dirname "$0"`
docker build -t algolia/algoliasearch-wordpress . || exit 1
echo "=============================================================="
echo "||        DOCKER IMAGE SUCCESSFULLY REBUILT                 ||"
echo "=============================================================="
echo ""

usage() {
  echo "Usage:" >&2
  echo "$PROG -a APPLICATION_ID -k API_KEY -s SEARCH_ONLY_API_KEY [-p INDEX_PREFIX] [-b BASE_URL]" >&2
  echo "" >&2
  echo "Options:" >&2
  echo "   -a | --application-id               The application ID" >&2
  echo "   -k | --api-key                      The ADMIN API key" >&2
  echo "   -s | --search-only-api-key          The Search-only API key" >&2
  echo "   -p | --index-prefix                 The index prefix (default: magento_)" >&2
  echo "   -b | --base-url                     The base URL (default: http://myworpdressstore.com/)" >&2
  echo "   -h | --help                         Print this help" >&2
}

while [[ $# > 0 ]]; do
  case "$1" in
    -a|--application-id)
      APPLICATION_ID="$2"
      shift
    ;;
    -s|--search-only-api-key)
      SEARCH_ONLY_API_KEY="$2"
      shift
    ;;
    -k|--api-key)
      API_KEY="$2"
      shift
    ;;
    -p|--index-prefix)
      INDEX_PREFIX="$2"
      shift
      ;;
    -b|--base-url)
      case "$2" in
      */)
        BASE_URL="$2"
        ;;
      *)
        BASE_URL="$2/"
        ;;
      esac
      shift
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      echo "Unknown option '$1'." >&2
      echo ""
      usage
      exit 2
    ;;
  esac
  shift
done

ensure() {
  if [ -z "$2" ]; then
    echo "Missing option $1."
    echo ""
    usage
    exit 1
  fi
}

ensure "-a" "$APPLICATION_ID"
ensure "-k" "$API_KEY"
ensure "-s" "$SEARCH_ONLY_API_KEY"
ensure "-b" "$BASE_URL"

docker stop algoliasearch-wordpress > /dev/null 2>&1 || true
docker rm algoliasearch-wordpress > /dev/null 2>&1 || true

echo "     APPLICATION_ID: $APPLICATION_ID"
echo "            API_KEY: $API_KEY"
echo "SEARCH_ONLY_API_KEY: $SEARCH_ONLY_API_KEY"
echo "       INDEX_PREFIX: $INDEX_PREFIX"
echo "           BASE_URL: $BASE_URL"
echo ""

docker run -p 80:80 \
  -v "`pwd`/..":/var/www/htdocs/wp-content/plugins/algoliasearch-wordpress \
  -e APPLICATION_ID=$APPLICATION_ID \
  -e SEARCH_ONLY_API_KEY=$SEARCH_ONLY_API_KEY \
  -e API_KEY=$API_KEY \
  -e INDEX_PREFIX=$INDEX_PREFIX \
  -e BASE_URL=$BASE_URL \
  -d \
  --name algoliasearch-wordpress \
  -t algolia/algoliasearch-wordpress
