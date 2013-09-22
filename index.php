<?php 
	session_start();
	require_once('api/snapchat.php');
	$id = $_GET['id'];
	$content = "";
	
	if (!($_SESSION['name'] == null) && $id != "logout")
	{
		$content .= '
		<ul>
			<li><a href="?id=getSnaps">Get Snaplist</a></li>
			<li><a href="?id=getFriends">Get Friendlist</a></li>
			<li><a href="?id=sendSnaps">Send snaps</li>
			<li><a href="?id=logout">Logout</a></li>
		</ul>
		';
		echo $content;
	}
	
	if ($id == "0" || $id == null)
	{	echo '<title>Start</title>';
		$content .= '
			<form action="?id=loginF" method="POST">
				<label>Username</label><br>
				<input type="text" name="username" required="required" autofocus="autofocus"/><br>
				<label>Password</label><br>
				<input type="password" name="password" required="required"/><br>
				<input type="submit" value="Login"/>
			</form>
		';
		echo $content;
	}
	if ($id == "loginF")
	{
		echo '<title>Login</title>';
		$username = $_POST['username'];
		$password = $_POST['password'];
		
		$snapchat = new Snapchat($username,$password);
		echo $username;
		$_SESSION['name'] = $username;
		$_SESSION['pass'] = $password;
		
		$content .= '
		<ul>
			<li><a href="?id=getSnaps">Get Snaplist</a></li>
			<li><a href="?id=getFriends">Get Friendlist</a></li>
			<li><a href="?id=sendSnaps">Send snaps</li>
			<li><a href="?id=logout">Logout</a></li>
		</ul>
		';
		echo $content;
		
	}
	if ($id == "getSnaps")
	{
		echo '<title>View all snaps</title>';
		echo $_SESSION['name'];
		$snapchat = new Snapchat($_SESSION['name'],$_SESSION['pass']);
		$snaps = $snapchat->getSnaps();
		//print_r($snaps);
		echo '<br><br><br>';
		for ($i=0;$i<count($snaps);$i++)
		{
			echo $snaps[$i]->sender.' sent snap <a href="?id=download&snapid='.$snaps[$i]->id.'&sender='.$snaps[$i]->sender.'">#'.$snaps[$i]->id.'</a><br>';
			//echo 'test';
		}
	}
	if ($id == "getFriends")
	{
		echo '<title>See your friends</title>';
		//echo $_SESSION['name'];
		$snapchat = new Snapchat($_SESSION['name'],$_SESSION['pass']);
		$friends = $snapchat->getFriends();
		for ($i=0;$i<count($friends);$i++)
		{
			echo $friends[$i]->name.' is called: '.$friends[$i]->display.'<br>';
		}
	}
	if ($id == "logout")
	{
		echo '<title>Logout</title>';
		$snapchat = new Snapchat($_SESSION['name'],$_SESSION['pass']);
		$snapchat->logout();
		$_SESSION['name'] = null;
		$_SESSION['pass'] = null;
		session_destroy();
		echo 'logout!';
		$content .= '
		<ul>
			<li><a href="?id=">Login</a></li>
		</ul>
		';
		echo $content;
	}
	if ($id == 'download')
	{
		echo '<title>Download picture</title>';
		$snapchat = new Snapchat($_SESSION['name'],$_SESSION['pass']);
		$snapid = $_GET['snapid'];
		$sender = $_GET['sender'];
		$data = $snapchat->getMedia($snapid);
		$prePath = 'media/download/'.$sender.'_'.$snapid.'.jpg';
		if (file_exists($prePath))
		{
			$finalPath = 'media/download/'.$sender.'_'.$snapid.rand(0,100).'.jpg';
		}
		else
		{
			$finalPath = 'media/download/'.$sender.'_'.$snapid.'.jpg';
		}
		file_put_contents($finalPath, $data);
		echo '<a href="'.$finalPath.'">View pic...</a>';
	}
	
	if ($id == "sendSnaps")
	{
		echo '<title>Send snap</title>';
		$snapchat = new Snapchat($_SESSION['name'],$_SESSION['pass']);
		$content="";
		$content .= '
		<form action="?id=sendSnaps2" method="POST" enctype="multipart/form-data">
			<label>Receiver:</label><br>
			<select name="receiver" required="required">
				<option selected>Please choose</option>';
				
				$friends = $snapchat->getFriends();
		for ($i=0;$i<count($friends);$i++)
		{
			$content .=  '<option>'.$friends[$i]->name.'</option>';
		}
$content .= '</select><br>
			<label>Time:</label><br>
			<select name="time" required="required">
				<option selected>1</option>';
				
				for ($i=2;$i<=10;$i++)
				{
					$content .= '<option>'.$i.'</option>';
				}
				
				$content .= '
			</select><br>
			<label>Upload pic: <small>Has to be .jpg!!!!</small></label><br>
			<input type="file" name="datei" required="required"/><br>
			<input type="submit" value="Send"/>
		</form>
		';
		
		echo $content;
	}
	
	if ($id == "sendSnaps2")
	{
		echo "<title>Sent snap</title>";
		$snapchat = new Snapchat($_SESSION['name'],$_SESSION['pass']);
		$dateityp = GetImageSize($_FILES['datei']['tmp_name']);
		//print_r($dateityp);
		if ($dateityp[2] == 2)
		{
			if($_FILES['datei']['size'] < 5242880)
			{
			  move_uploaded_file($_FILES['datei']['tmp_name'], "media/upload/".$_FILES['datei']['name']);
			  $id = $snapchat->upload(
					Snapchat::MEDIA_IMAGE,
					file_get_contents("media/upload/".$_FILES['datei']['name'])
				);
				$send = $snapchat->send($id, array($_POST['receiver']), $_POST['time']);
				
				if ($send == true)
				{
					echo "Snap sent!";
				}
			}
			  
			else
			{
				echo "The file is too big!";
			}
		}
		else
		{
			echo "Error! Picture has to be jpg";
		}
	}
	
 ?>