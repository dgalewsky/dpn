<?php
//
// Copy all files from our local 'repository' to iRods
//

$files = scandir('/dpn/repository');

if (!$files) {
    echo "No files in local repository - exiting.\n";
}

foreach ($files as $fname) {
        if($fname == ".") continue;
        if($fname == "..") continue;

        echo "File: $fname\n";

        $basename = basename($fname);

        $dirpart = substr($basename, 0, 2);

        echo "Dirpart $dirpart\n";

        echo "Pushing to iRODS\n";

        echo "iput -P -f /dpn/repository/$fname tdr/$dirpart\n";

        $handle = popen("iput -P -f /dpn/repository/$fname tdr/$dirpart", 'r');

        while(!feof($handle)) {
                $read = fread($handle, 1024);
                echo "$read";
        }

        pclose($handle);

        // Delete file from /dpn/repository

}
?>
