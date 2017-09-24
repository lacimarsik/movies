# movies

What if you are lucky to have a nice movie librarie on your machine, but also want to have a nice selection screen for your next movie such as is on Netflix or iTunes?

![Screenshot of movies](https://github.com/lacimarsik/movies/blob/master/screen.png)

Features of this simple (and far from well-coded) PHP script:
* mirrors all movies located in the current folder to your MySQL database, shows the title, year and spoken language next to the movie
* also supports downloading a JPEG poster, rating, movie library link (ČSFD) with more info, and checking and syncing of the subtitles (unfortunately, in the current version, all has to be done manually by finding out the info yourself and putting it in a file next to the movie.. however it's quite convenient for watching movies once-in-a-while)
* supports launching the movie, by simply giving you the VLC command (with subtitle synchronization parameter)
* supports remembering the past movies you watched
