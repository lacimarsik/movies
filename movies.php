<html>
<head>
	<link rel="stylesheet" type="text/css" href="movies.css">	
	<link rel="stylesheet" type="text/css" href="bootstrap.css">	
</head>
<body>
<?php
include 'credentials.php';

$debug = false;
error_reporting(E_ERROR | E_PARSE);

function getRating($csfd_id) {
	$page = file_get_contents("https://www.csfd.cz/film/178905");
	$doc = new DOMDocument();
	$doc->loadHTML($page);
	$node = $doc->getElementById('rating');
	if(!$node) {
		echo "ERROR: An element with id rating was not found <br />";
	}
	return $doc->saveHTML($node);
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Run movie if asked for
if ($_GET['id']) {
	$sql= "SELECT * FROM movies WHERE id = " . $_GET['id'];
	$result = $conn->query($sql);
	while ($row = mysqli_fetch_assoc($result)) {
		$movie_path = $row['title'] . "[" . $row['language'] . "] (" . $row['year'] . ")";
		if ($row['sync'] == "No") {
			$sync = "";
		} else {
			$sync = "--sub-delay " . $row['sync'] . " ";
		}
		$dir = new DirectoryIterator($movie_path);
		foreach ($dir as $fileinfo) {
			$filename = $fileinfo->getFilename();
			$ext = pathinfo($fileinfo, PATHINFO_EXTENSION);
			if (($ext == "avi") || ($ext == "mp4") || ($ext == "mkv")) {
?>
<div class='run alert alert-success'>
	<button class="btn btn-default js-textareacopybtn">Copy</button>
	<input type="text" class="command js-copytextarea" value="vlc --fullscreen <?php echo $sync . getcwd() . "/'" . $movie_path . "'/'" . $filename . "'"?>">
	<script src="movies.js"></script>
</div>
<?php
			}
		}
	}
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
if ($debug) {
	echo "Connected successfully<br /><br />";
}

// Make movies inactive first (not present on the disk)
$sql0= "UPDATE movies SET active = 0, replay = 0, poster = 'No', downloaded = 'No'";
$conn->query($sql0);

$dir = new DirectoryIterator(dirname(__FILE__));
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot()) {
	$filename = $fileinfo->getFilename();
	$ext = pathinfo($fileinfo, PATHINFO_EXTENSION);

	if (!(($ext == "php") || ($ext == "jpg") || ($ext == "png") || ($ext == "css") || ($ext == "sh") || ($ext == "js") || ($ext == "md") || (strpos($filename,"Z_seen") !== false) || (strpos($filename,".git") !== false) || (strpos($filename,".gitignore") !== false) || (strpos($filename,".idea") !== false))) {
		$title = substr($filename, 0, strpos($filename, '['));
		$language = substr($filename, strpos($filename, '[') + 1, 2);
		$year = substr($filename, strpos($filename, '(') + 1, 4);
		if ($fileinfo->isDir()) {
			$downloaded = "No";
			$subtitles = "No";
			$subtitles_checked = "No";
			$poster = "No";
			$csfd = "No";
			$rating = "No";
			$active = "1";
			$replay = "0";
			$series = "0";
			$sport = "0";
			$sync = "No";
			$dir2 = new DirectoryIterator($fileinfo->getFilename());
			foreach ($dir2 as $fileinfo2) {
				$filename2 = $fileinfo2->getFilename();
				$ext2 = pathinfo($fileinfo2, PATHINFO_EXTENSION);
				if (($ext2 == "avi") || ($ext2 == "mp4")) {
					$downloaded = "Yes";
				}
				if (($ext2 == "srt") || ($ext2 == "sub")) {
					$subtitles = "Yes";
					// Not working yet: Convert subtitles to UTF-8 if necessary
					//exec("iconv -f windows-1250 -t utf-8 '" getcwd() . $filename .  "'/'" . $filename2 . "' > tempsrt");
				}
				if ($ext2 == "jpg") {
					$poster = "Yes";
				}
				if ($ext2 == "csfd") {
					$csfd = substr($filename2, 0, strpos($filename2, '.'));
				}
				if ($ext2 == "rating") {
					$rating = substr($filename2, 0, strpos($filename2, '.'));
				}
				if ($ext2 == "checked") {
					$subtitles_checked = "Yes";
				}
				if ($ext2 == "replay") {
					$replay = "1";
				}
				if ($ext2 == "series") {
					$series = "1";
				}
				if ($ext2 == "sport") {
					$sport = "1";
				}
				if ($ext2 == "sync") {
					$sync = substr($filename2, 0, strpos($filename2, '.'));
				}
				// Not working yet: Get rating from CSFD
				//if ($csfd != "No") {
				//	$rating = getRating($csfd);
				//}
			}
		}

		if ($debug) {
			echo 'title: ' . $title . '<br />';
			echo 'year: ' . $year . '<br />';
			echo 'language: ' . $language . '<br />';
			echo 'downloaded: ' . $downloaded . '<br />';
			echo 'subtitles: ' . $subtitles . '<br />';
			echo 'poster: ' . $poster . '<br />';
			echo 'csfd: ' . $csfd . '<br />';
			echo 'rating: ' . $rating . '<br />';
			echo 'replay: ' . $replay . '<br />';
			echo 'series: ' . $series . '<br />';
			echo 'sport: ' . $sport . '<br />';
		}

		$sql1 = "SELECT id FROM movies WHERE title = '" . $title . "'";
		$result1 = $conn->query($sql1);
	
		if ($result1->num_rows > 0) {
			$sql2 = "UPDATE movies SET title = '" . $title . "', year = '" . $year . "', language = '" . $language . "', subtitles = '" . $subtitles . "', csfd = '" . $csfd . "', active = '" . $active . "', replay = '" . $replay . "', series = '" . $series . "', sport = '" . $sport . "', downloaded = '" . $downloaded . "', rating = '" . $rating . "', poster = '" . $poster . "', subtitles_checked = '" . $subtitles_checked . "', sync = '" . $sync . "' WHERE title = '" . $title . "'";
			$result2 = $conn->query($sql2);
		} else {
			$sql2 = "INSERT INTO movies VALUES ('', '" . $title . "', '" . $year . "', '" . $language . "', '" . $subtitles . "', '" . $csfd . "', '" . $active . "', '" . $replay . "', '" . $series . "', '" . $sport . "', '" . $downloaded . "', '" . $rating . "', '" . $poster . "', '" . $subtitles_checked . "', '" . $sync . "')";
			$result2 = $conn->query($sql2);
		}
	}
    }
}

?>
<h1 class="top">Movies</h1>
<div class="container new">
	<div class="row">
<?php
// Showing the movies
$sql3 = "SELECT * FROM movies WHERE active = '1' AND replay = '0' AND series = '0' AND sport = '0'";
$result3 = $conn->query($sql3);

while ($row = mysqli_fetch_assoc($result3)) {
	if ($row['poster'] == "Yes") {
		$poster_path = $row['title'] . "[" . $row['language'] . "] (" . $row['year'] . ")/poster.jpg";
	} else {
		$poster_path = "default-poster.jpg";
	}
	
?>
		<div class="col-md-3 col-sm-4 col-xs-6 movie">
			<a href="?id=<?php echo $row['id']?>"><img class="img-responsive poster" src="<?php echo $poster_path; ?>" /></a>
			<div class="info">
				<?php echo "<strong>" . $row['title'] . "</strong>";?><br />
				<?php echo $row['year'];?><br />
				<?php echo $row['language']; if ($row['subtitles'] == "Yes") { echo ", CZ subtitles"; } if ($row['subtitles_checked'] == "Yes") { echo ", <span class='checked'>checked</span>"; }?><br />
				<?php echo "<a target='_blank' href='https://www.csfd.cz/film/" . $row['csfd']. "'>ČSFD</a>: " . $row['rating'] . "%";?><br />
			</div>
		</div>
<?php
}
?>
	</div>
</div>

<h1>Series</h1>
<div class="container seen">
	<div class="row">

<?php
// Showing the movies
$sql3 = "SELECT * FROM movies WHERE active = '1' AND replay = '0' AND series = '1' AND sport = '0'";
$result3 = $conn->query($sql3);

while ($row = mysqli_fetch_assoc($result3)) {
	$poster_path = "./" . $row['title'] . "[" . $row['language'] . "] (" . $row['year'] . ")/poster.jpg";
	
?>
		<div class="col-md-3 col-sm-4 col-xs-6 movie">
			<a href="?id=<?php echo $row['id']?>"><img class="img-responsive poster" src="<?php echo $poster_path; ?>" /></a>
			<div class="info">
				<?php echo $row['title'];?><br />
				<?php echo $row['year'];?><br />
				<?php echo $row['language']; if ($row['subtitles'] == "Yes") { echo ", CZ subtitles"; } if ($row['subtitles_checked'] == "Yes") { echo ", <span class='checked'>checked</span>"; }?><br />
				<?php echo "<a target='_blank' href='https://www.csfd.cz/film/" . $row['csfd']. "'>ČSFD</a>: " . $row['rating'] . "%";?><br />
			</div>
		</div>
<?php
}
?>
	</div>
</div>

<h1>Sport</h1>
<div class="container seen">
	<div class="row">

<?php
// Showing the movies
$sql3 = "SELECT * FROM movies WHERE active = '1' AND replay = '0' AND series = '0' AND sport = '1'";
$result3 = $conn->query($sql3);

while ($row = mysqli_fetch_assoc($result3)) {
	$poster_path = "./" . $row['title'] . "[" . $row['language'] . "] (" . $row['year'] . ")/poster.jpg";
	
?>
		<div class="col-md-3 col-sm-4 col-xs-6 movie">
			<a href="?id=<?php echo $row['id']?>"><img class="img-responsive poster" src="<?php echo $poster_path; ?>" /></a>
			<div class="info">
				<?php echo $row['title'];?><br />
				<?php echo $row['year'];?><br />
				<?php echo $row['language']; if ($row['subtitles'] == "Yes") { echo ", CZ subtitles"; } if ($row['subtitles_checked'] == "Yes") { echo ", <span class='checked'>checked</span>"; }?><br />
				<?php echo "<a target='_blank' href='https://www.csfd.cz/film/" . $row['csfd']. "'>ČSFD</a>: " . $row['rating'] . "%";?><br />
			</div>
		</div>
<?php
}
?>
	</div>
</div>

<h1>Replay movies</h1>
<div class="container seen">
	<div class="row">

<?php
// Showing the movies
$sql3 = "SELECT * FROM movies WHERE active = '1' AND replay = '1' AND series = '0' AND sport = '0'";
$result3 = $conn->query($sql3);

while ($row = mysqli_fetch_assoc($result3)) {
	$poster_path = "./" . $row['title'] . "[" . $row['language'] . "] (" . $row['year'] . ")/poster.jpg";
	
?>
		<div class="col-md-3 col-sm-4 col-xs-6 movie">
			<a href="?id=<?php echo $row['id']?>"><img class="img-responsive poster" src="<?php echo $poster_path; ?>" /></a>
			<div class="info">
				<?php echo $row['title'];?><br />
				<?php echo $row['year'];?><br />
				<?php echo $row['language']; if ($row['subtitles'] == "Yes") { echo ", CZ subtitles"; } if ($row['subtitles_checked'] == "Yes") { echo ", <span class='checked'>checked</span>"; }?><br />
				<?php echo "<a target='_blank' href='https://www.csfd.cz/film/" . $row['csfd']. "'>ČSFD</a>: " . $row['rating'] . "%";?><br />
			</div>
		</div>
<?php
}
?>
	</div>
</div>

<h1>Replay series</h1>
<div class="container seen">
	<div class="row">

<?php
// Showing the movies
$sql3 = "SELECT * FROM movies WHERE active = '1' AND replay = '1' AND series = '1' AND sport = '0'";
$result3 = $conn->query($sql3);

while ($row = mysqli_fetch_assoc($result3)) {
	$poster_path = "./" . $row['title'] . "[" . $row['language'] . "] (" . $row['year'] . ")/poster.jpg";
	
?>
		<div class="col-md-3 col-sm-4 col-xs-6 movie">
			<a href="?id=<?php echo $row['id']?>"><img class="img-responsive poster" src="<?php echo $poster_path; ?>" /></a>
			<div class="info">
				<?php echo $row['title'];?><br />
				<?php echo $row['year'];?><br />
				<?php echo $row['language']; if ($row['subtitles'] == "Yes") { echo ", CZ subtitles"; } if ($row['subtitles_checked'] == "Yes") { echo ", <span class='checked'>checked</span>"; }?><br />
				<?php echo "<a target='_blank' href='https://www.csfd.cz/film/" . $row['csfd']. "'>ČSFD</a>: " . $row['rating'] . "%";?><br />
			</div>
		</div>
<?php
}
?>
	</div>
</div>

<h1>Replay sport</h1>
<div class="container seen">
	<div class="row">

<?php
// Showing the movies
$sql3 = "SELECT * FROM movies WHERE active = '1' AND replay = '1' AND series = '0' AND sport = '1'";
$result3 = $conn->query($sql3);

while ($row = mysqli_fetch_assoc($result3)) {
	$poster_path = "./" . $row['title'] . "[" . $row['language'] . "] (" . $row['year'] . ")/poster.jpg";
	
?>
		<div class="col-md-3 col-sm-4 col-xs-6 movie">
			<a href="?id=<?php echo $row['id']?>"><img class="img-responsive poster" src="<?php echo $poster_path; ?>" /></a>
			<div class="info">
				<?php echo $row['title'];?><br />
				<?php echo $row['year'];?><br />
				<?php echo $row['language']; if ($row['subtitles'] == "Yes") { echo ", CZ subtitles"; } if ($row['subtitles_checked'] == "Yes") { echo ", <span class='checked'>checked</span>"; }?><br />
				<?php echo "<a target='_blank' href='https://www.csfd.cz/film/" . $row['csfd']. "'>ČSFD</a>: " . $row['rating'] . "%";?><br />
			</div>
		</div>
<?php
}
?>
	</div>
</div>

<script>
function show() { document.getElementById("hide").style.display = "block"; document.getElementById("showbutton").style.display = "none"; document.getElementById("hidebutton").style.display = "inline"; }
function hide() { document.getElementById("hide").style.display = "none"; document.getElementById("showbutton").style.display = "inline"; document.getElementById("hidebutton").style.display = "none"; }
</script>

<div class="buttons">
<a id="showbutton" class="pointer" onclick="show()"><strong>SHOW MORE</strong></a><a id="hidebutton" class="pointer" style="display: none;" onclick="hide()"><strong>HIDE</strong></a>
</div>
<br />

<div id="hide" style="display: none;">
<h1>Already seen movies</h1>
<div class="container seen">
	<div class="row">

<?php
// Showing the movies
$sql3 = "SELECT * FROM movies WHERE active = '0' AND replay = '0' AND series = '0'";
$result3 = $conn->query($sql3);

while ($row = mysqli_fetch_assoc($result3)) {
	$poster_path = "Z_seen/" . $row['title'] . "[" . $row['language'] . "] (" . $row['year'] . ")/poster.jpg";
	
?>
		<div class="col-md-3 col-sm-4 col-xs-6 movie">
			<img class="img-responsive poster" src="<?php echo $poster_path; ?>" />
			<div class="info">
				<?php echo $row['title'];?><br />
				<?php echo $row['year'];?><br />
				<?php echo $row['language']; if ($row['subtitles'] == "Yes") { echo ", CZ subtitles"; } if ($row['subtitles_checked'] == "Yes") { echo ", <span class='checked'>checked</span>"; }?><br />
				<?php echo "<a target='_blank' href='https://www.csfd.cz/film/" . $row['csfd']. "'>ČSFD</a>: " . $row['rating'] . "%";?><br />
			</div>
		</div>
<?php
}
?>
	</div>
</div>

<h1>Already seen series</h1>
<div class="container seen">
	<div class="row">

<?php
// Showing the movies
$sql3 = "SELECT * FROM movies WHERE active = '0' AND replay = '0' AND series = '1' AND sport = '0'";
$result3 = $conn->query($sql3);

while ($row = mysqli_fetch_assoc($result3)) {
	$poster_path = "Z_seen/" . $row['title'] . "[" . $row['language'] . "] (" . $row['year'] . ")/poster.jpg";
	
?>
		<div class="col-md-3 col-sm-4 col-xs-6 movie">
			<img class="img-responsive poster" src="<?php echo $poster_path; ?>" />
			<div class="info">
				<?php echo $row['title'];?><br />
				<?php echo $row['year'];?><br />
				<?php echo $row['language']; if ($row['subtitles'] == "Yes") { echo ", CZ subtitles"; } if ($row['subtitles_checked'] == "Yes") { echo ", <span class='checked'>checked</span>"; }?><br />
				<?php echo "<a target='_blank' href='https://www.csfd.cz/film/" . $row['csfd']. "'>ČSFD</a>: " . $row['rating'] . "%";?><br />
			</div>
		</div>
<?php
}
?>
	</div>
</div>

<h1>Already seen sport</h1>
<div class="container seen">
	<div class="row">

<?php
// Showing the movies
$sql3 = "SELECT * FROM movies WHERE active = '0' AND replay = '0' AND series = '0' AND sport = '1'";
$result3 = $conn->query($sql3);

while ($row = mysqli_fetch_assoc($result3)) {
	$poster_path = "Z_seen/" . $row['title'] . "[" . $row['language'] . "] (" . $row['year'] . ")/poster.jpg";
	
?>
		<div class="col-md-3 col-sm-4 col-xs-6 movie">
			<img class="img-responsive poster" src="<?php echo $poster_path; ?>" />
			<div class="info">
				<?php echo $row['title'];?><br />
				<?php echo $row['year'];?><br />
				<?php echo $row['language']; if ($row['subtitles'] == "Yes") { echo ", CZ subtitles"; } if ($row['subtitles_checked'] == "Yes") { echo ", <span class='checked'>checked</span>"; }?><br />
				<?php echo "<a target='_blank' href='https://www.csfd.cz/film/" . $row['csfd']. "'>ČSFD</a>: " . $row['rating'] . "%";?><br />
			</div>
		</div>
<?php
}
?>
	</div>
</div>

<?php
$conn->close();
?>

</div>
</body>
</html>

