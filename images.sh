#!/bin/sh

# images.sh
#
# Fetches and injects the latest image
# files from Heroes of the Storm game
# data via heroes-talents

# Make sure Git is available
if [ ! "`which git`" ]; then
    echo "ERROR: Git not found!"
    exit 1
fi

# Locate the necessary directories
cd `dirname "$0"`
ROOTDIR=`pwd`
DESTINATION="$ROOTDIR"/public/images
if [ ! -d "$DESTINATION" ]; then
	echo "ERROR: Unable to locate destination!"
	exit 1
fi

# Create the temporary workspace
WORKSPACE=`mktemp -d`
cd "$WORKSPACE"

# Attempt to clone the repo
git clone --depth 1 https://github.com/HeroesToolChest/heroes-images.git
RESULT=$?
if [ $RESULT -ne 0 ]; then
	echo "ERROR: Unable to clone heroes-images repo!"
	exit $RESULT
fi

# Make sure the source is present
SOURCE="$WORKSPACE"/heroes-images/heroesimages
if [ ! -d "$SOURCE" ]; then
	echo "ERROR: Unable to locate source!"
	exit 1
fi

# Clear out any previous versions
rm -rf "$DESTINATION"/heroesimages

# Move the content
mv "$SOURCE" "$DESTINATION"
RESULT=$?
if [ $RESULT -ne 0 ]; then
	echo "ERROR: Failed to move images! Project may be compromised."
	exit $RESULT
fi

# Clean up
rm -rf "$WORKSPACE"

exit 0
