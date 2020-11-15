#! /usr/bin/env bash
# You can execute this file to create a new package for wallabag
# eg: `sh release.sh 2.3.3 /tmp wllbg-release prod`

VERSION=$1
TMP_FOLDER=$2
RELEASE_FOLDER=$3
ENV=$4

rm -rf $TMP_FOLDER/$RELEASE_FOLDER
mkdir $TMP_FOLDER/$RELEASE_FOLDER
git clone git@github.com:wallabag/wallabag.git $TMP_FOLDER/$RELEASE_FOLDER/$VERSION
cd $TMP_FOLDER/$RELEASE_FOLDER/$VERSION && SYMFONY_ENV=$ENV COMPOSER_MEMORY_LIMIT=-1 composer install -n --no-dev
cd $TMP_FOLDER/$RELEASE_FOLDER/$VERSION && php bin/console wallabag:install --env=$ENV -n
cd $TMP_FOLDER/$RELEASE_FOLDER/$VERSION && php bin/console assets:install --env=$ENV --symlink --relative
cd $TMP_FOLDER/$RELEASE_FOLDER && tar czf wallabag-$VERSION.tar.gz --exclude="var/cache/*" --exclude="var/logs/*" --exclude="var/sessions/*" --exclude=".git" $VERSION
echo "MD5 checksum of the package for wallabag $VERSION"
md5 $TMP_FOLDER/$RELEASE_FOLDER/wallabag-$VERSION.tar.gz
echo "Package to upload to the release server:"
echo $TMP_FOLDER/$RELEASE_FOLDER/wallabag-$VERSION.tar.gz
