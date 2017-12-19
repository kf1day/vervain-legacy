#/bin/sh

find -type f -iname '*.php' -exec sed 's/[ \t\r]\+$//' -i {} \;
