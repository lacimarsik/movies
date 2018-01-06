# movies

What if you are lucky to have a nice movie library on your machine, but also want to have a nice selection screen for your next movie such as is on Netflix or iTunes?

![Screenshot of movies](https://github.com/lacimarsik/movies/blob/master/screen.png)

Features of this simple (and far from well-coded) PHP script:
* mirrors all movies located in the current folder to your MySQL database, shows the title, year and spoken language next to the movie
* also supports downloading a JPEG poster, rating, movie library link (ČSFD) with more info, and checking and syncing of the subtitles (unfortunately, in the current version, all has to be done manually by finding out the info yourself and putting it in a file next to the movie.. however it's quite convenient for watching movies once-in-a-while)
* supports launching the movie, by simply giving you the VLC command (with subtitle synchronization parameter)
* supports remembering the past movies you watched

All you need to do is to checkout, and put the movie folders in the checkout-ed repo folder.
* Assign each movie 1 folder, with the naming: `Name [LANGUAGE] (YEAR)`, example `The Great Movie [EN] (2010)`. Put the `mp4` (`avi`, `mkv`) to this folder, along with `srt` subtitle file of any name
* Put `poster.jpg` with your preferred poster to that folder (preferred dimensions 127x180px)
* Put additional empty files to the folder, the script will use them to gather more information on the movie: `XX.rating` for rating, `XYZ.csfd` for CSFD.cz rating, `subtitles.checked` to label the subtitles as checked, `-42.sync` to say that the movie subtitles needs to be hastened 4.2 seconds (works with VLC player)
Run your `movies.php` on your local PHP server, click the movie, and enjoy!