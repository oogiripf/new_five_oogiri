<?php
	include("config/config.php");
	include("function.php");
	try{
		$pdo = new PDO("mysql:dbname=$dbname;host=$dbhostname", "$dbuser", "$dbpass");
		$st_setting = $pdo->query("SELECT * FROM {$setting_table}");
	}catch (PDOException $e){
		$sitename = "Bokegram";
		$pagetitle = "エラー - ".$sitename;
		include("header.php");
		echo error_message("MySQLエラー","接続できませんでした");
		exit;
	}
	$setting = $st_setting->fetch();
	$sitename = $setting['site_name'];
	$ranking_player = $setting['ranking_player'];
	$ranking_post = $setting['ranking_post'];
	$pagetitle = "ボケグラムについて - ".$sitename;
	include("header.php");


?>
<div id="main">
	<div class="article_box">
	<div class="container">
		<div class="titlelists">
			<div class="titlelists-title"><h2>ボケグラムについて</h2></div>
			<div class="article_body">
				<p>当アプリケーションはボケグラムを使用しています。</p>
				<p><a href="http://bokegram.web.fc2.com/" class="link_button">ボケグラムのダウンロード</a></p>
			</div>
			<div class="titlelists-title"><h2>License</h2></div>
			<div class="article_body">
				<p>以下のライブラリを使用しています。</p>
				<dl class="license">
				<dt>Bootstrap</dt>
				<dd>The MIT License (MIT)<br />
<br />
Copyright (c) 2011-2015 Twitter, Inc<br />
<br />
Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:<br />
<br />
The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.<br />
<br />
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.</dd>
				<dt>Bootstrap 3 Date/Time Picker</dt>
				<dd>The MIT License (MIT)<br />
<br />
Copyright (c) 2015 Jonathan Peterson (@Eonasdan)<br />
<br />
Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:<br />
<br />
The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.<br />
<br />
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.</dd>
					<dt>moment</dt>
				<dd>The MIT License (MIT)<br />
<br />
Copyright (c) 2011-2015 Tim Wood, Iskren Chernev, Moment.js contributors<br />
<br />
Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:<br />
<br />
The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.<br />
<br />
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.</dd>
			</dl>
		</div>
		</div>
	</div>
	</div>
</div>
<?php
	include("footer.php");
?>
