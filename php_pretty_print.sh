#/bin/sh

find -type f -iname '*.php' -exec sed '1s/^\xEF\xBB\xBF//;s/[ \t\r]\+$//;$a\' -i {} \;
