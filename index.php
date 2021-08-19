<?php

if(isset($_GET['action']) && !!$_GET['action'] && is_string($_GET['action'])){
	$actions = $_GET['action'];

	if(!is_array($actions)) {
		$actions = [$actions];
	}

	foreach($actions as $action) {
		if(in_array($action, ["play","pause","previous","next"])) {
			exec("playerctl $action", $result);
			sleep(1);
		}
		exec("playerctl metadata", $result);
		sleep(1); //sleep because the "status" results takes some time to be updated by playerctl
		exec("playerctl status", $result);
	}
	header('Content-Type: application/json');
	echo(json_encode($result));
	exit;
}
?>

<!DOCTYPE html>
<html id="theme" data-theme="dark">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title id="titleDom">RPI Deezer web controller</title>
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://unpkg.com/@picocss/pico@latest/css/pico.min.css">
	</head>
	<body>
		<main class="container">
			<article>
				<header class="grid">
					<hgroup>
						<h1 id="artist">Raspberry Pi</h1>
						<h3 id="title">Nothing is being played</h3>
						<h3 id="album">It's calm (✿´‿`)</h3>
					</hgroup>
					<div>
						<img id="img">
					</div>
				</header>
				<div class="grid">
					<div>
						<button id="prev" onclick="mediaAction('previous')" type="button"><<</button>
					</div>
					<div>
						<button id="play" onclick="mediaAction('play')" type="button">Play</button>
					</div>
					<div>
						<button  id="pause" onclick="mediaAction('pause')" type="button">Pause</button>
					</div>
					<div>
						<button  id="next" onclick="mediaAction('next')" type="button">>></button>
					</div>
				</div>
			</article>
			<button id="switcher" class="contrast switcher theme-switcher" onclick="setTheme()">\ (•◡•) /</button>
			
		</main>
		<footer>
			Made with <a href="https://picocss.com/"> Pico.css </a> - Fork me on <a href="https://github.com/ThibautPlg/RPI-web-controller">Github</a>
		</footer>

		<script>
			//Refresh the metadata every 30 seconds
			mediaAction("metadata"); //But do it a first time on landing :)
			setInterval(function(){mediaAction("metadata");}, 30000);

			function mediaAction(action) {  
				var xhr = new XMLHttpRequest();
				xhr.open( "GET", "index.php?action="+action, true );
				xhr.onload = function () {
					if (xhr.readyState === xhr.DONE) {
						if (xhr.status === 200) {
							data = xhr.response;
							if(!!data && data.length) {
								updateMetadata(data);
							}
						}
					}
				};
				xhr.send( null );
			}
			
			function updateMetadata(data) {
				data = JSON.parse(data);
				document.getElementById("artist").innerHTML = data[3].match(/.*artist(.*)/)[1];
				document.getElementById("title").innerHTML = data[1].match(/.*title(.*)/)[1];
				document.getElementById("titleDom").innerHTML = data[1].match(/.*title(.*)/)[1]+" - "+data[3].match(/.*artist(.*)/)[1];
				document.getElementById("album").innerHTML = data[2].match(/.*album(.*)/)[1];
				document.getElementById("img").src = data[4].match(/.*artUrl(.*)/)[1];

				if(!!data[5] && data[5] === "Playing") {
					document.getElementById("play").classList.add("secondary");
					document.getElementById("pause").classList.remove("secondary");
				} else {
					document.getElementById("pause").classList.add("secondary");
					document.getElementById("play").classList.remove("secondary");
				}
			}

			function setTheme(mood) {
				currentMood = document.getElementById("theme").getAttribute("data-theme");
				if (currentMood === "dark") {
					mood = "light";
					sprite = "(▀̿Ĺ̯▀̿ ̿)";
				} else {
					mood = !!mood ? mood : "dark";
					sprite = "◉_◉";
				}
				document.getElementById("switcher").innerHTML = sprite;
				document.getElementById("theme").setAttribute("data-theme", mood);
			}

		</script>
		<style>
			.switcher {
				position: fixed;
				right: calc(var(--spacing) / 2);
				bottom: var(--spacing);
				width: auto;
				margin-bottom: 0;
				padding: .75rem;
				border-radius: 2rem;
				box-shadow: var(--card-box-shadow);
				line-height: 1;
				text-align: right;
			}
			footer {
				position:relative; 
				bottom:0;
				margin: 0;
				padding: 5px !important;
			}
		</style>
	</body>
</html>