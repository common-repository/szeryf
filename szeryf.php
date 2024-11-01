<?
/*
Plugin Name: Szeryf
Plugin URI: http://więcek.pl/projekty/szeryf
Description: Wtyczka pozwala dynamicznie zmieniać ścieżkę dostępu do załączników.
Version: 0.1.3
Author: Łukasz Więcek
Author URI: http://więcek.pl/
*/

function SzeryfUstawienia()
	{
	if($_POST['ZainstalujSzeryfa'])
		{
		function wpsz_remove_marker($filename, $marker)
			{
			if(!file_exists($filename) || SzeryfACLSafe($filename))
				{
				if(!file_exists($filename))
					{
					return '';
					}
				else
					{
					$markerdata = explode("\n", implode('', file($filename)));
					}

				$f = fopen( $filename, 'w');
				$foundit = false;
				if($markerdata)
					{
					$state = true;
					foreach($markerdata as $n => $markerline)
						{
						if(strpos($markerline, '# BEGIN '.$marker)!==false)
							$state = false;
						if($state)
							{
							if($n+1<count($markerdata))
								fwrite( $f, "{$markerline}\n");
							else
								fwrite( $f, "{$markerline}");
							}
						if(strpos($markerline, '# END '.$marker)!==false)
							{
							$state = true;
							}
						}
					}
				return true;
				}
			else
				{
				return false;
				}
			}

		function SzeryfACLSafe($path)
			{
			if($path{strlen($path)-1}=='/')
				return SzeryfACLSafe($path.uniqid(mt_rand()).'.tmp');
			else if (is_dir($path))
				return SzeryfACLSafe($path.'/'.uniqid(mt_rand()).'.tmp');
			$rm = file_exists($path);
			$f = @fopen($path, 'a');
			if ($f===false)
				return false;
			fclose($f);
			if (!$rm)
				unlink($path);
			return true;
			}
		
		$nowy_klucz = substr(md5(time()), 0, 20);
		add_option('klucz_szeryfa', $nowy_klucz, ' ', 'yes');
		
		$upload_path = get_option('upload_path');
		$home_path = get_home_path();
		$home_root = parse_url(get_bloginfo('url'));
		$home_root = trailingslashit($home_root['path']);
		$inst_root = trailingslashit(str_replace(get_option('siteurl'), '', WP_CONTENT_URL));
		
		if($_POST['new_upload_path']!='')
			{
			rename($home_path.'/'.$upload_path, $home_path.'/'.$_POST['new_upload_path']);
			update_option('upload_path', $_POST['new_upload_path']);
			add_option('katalog_szeryfa', $upload_path, ' ', 'no');
			
			global $table_prefix;
			$sql = "SELECT ID,post_content FROM ".$table_prefix."posts";
			$query = mysql_query($sql);
			while($p = mysql_fetch_array($query))
				{
				$pID = $p['ID'];
				$nc = str_replace($upload_path,$_POST['new_upload_path'],$p['post_content']);
				$sqlu = "UPDATE ".$table_prefix."posts SET `post_content` = '".$nc."' WHERE `ID` LIKE '".$pID."'";
				mysql_query($sqlu);
				}
			$upload_path = $_POST['new_upload_path'];
			}

		$wprules = implode("\n", extract_from_markers($home_path.'.htaccess', 'WordPress'));
		$wprules = str_replace("RewriteEngine On\n", '', $wprules);
		$wprules = str_replace("RewriteBase $home_root\n", '', $wprules);
		
		$szrules = implode("\n", extract_from_markers($home_path.'.htaccess', 'Szeryf') );

		$rules = "<IfModule mod_rewrite.c>\n";
		if(!function_exists('wp_cache_clean_cache')) $rules .= "RewriteEngine On\n";
		if(!function_exists('wp_cache_clean_cache')) $rules .= "RewriteBase $home_root\n";
		$rules .= "RewriteRule ^".$nowy_klucz."/(.*)$ /".$upload_path."/$1 [L]\n";
		$rules .= "</IfModule>\n";
		
		wpsz_remove_marker($home_path.'.htaccess', 'WordPress');
		insert_with_markers($home_path.'.htaccess', 'Szeryf', explode("\n", $rules));
		insert_with_markers($home_path.'.htaccess', 'WordPress', explode("\n", $wprules));
		
		if(function_exists('wp_cache_clean_cache')) wp_cache_clean_cache($file_prefix);
		}

	if($_POST['GenerujKlucz'])
		{
		GenerujKlucz();
		}
	?>
	<div class="wrap">
		<?
		if(get_option('klucz_szeryfa'))
			{
			?>
			<h2>Konfiguracja Szeryfa</h2>
			<p><strong>Klucz szeryfa:</strong> <?php echo get_option('klucz_szeryfa');?></p>
			<form action="" method="post" id="GeneratorKluczy">
			<p class="submit"><input type="submit" name="GenerujKlucz" value="Wygeneruj nowy klucz" /></p>
			</form>
			<?
			}

		if(!get_option('klucz_szeryfa'))
			{
			?>
			<h2>Wstępna konfiguracja Szeryfa</h2>
			
			<h3>Plik .htaccess</h3>
			<p>Do poprawnego działania wtyczki wymagane jest dopisanie kilku regułek w pliku <b>.htaccess</b>. Instalator wtyczki spróbuje sam wprowadzić w tym pliku odpowiednie zmiany.</p>
			
			<p>Jeżeli po instalacji wtyczki załączniki nie będą się ładować, upewnij się, że w pliku <b>.htaccess</b> w głownym katalogu Twojego bloga została dodana następująca sekcja <b>przed</b> sekcją <b># BEGIN Wordpress</b>:</p>
			
<pre style="margin: 20px 0 20px 30px;"># BEGIN Szeryf
&lt;IfModule mod_rewrite.c&gt;
RewriteRule ^063b092dc58c7c3741ba/(.*)$ /wp-content/uploads/$1 [L]
&lt;/IfModule&gt;
 
# END Szeryf</pre> 

			<p>Jeżeli sekcja ta nie została dodana automatycznie, będziesz musiał zrobić to ręcznie. Pamiętaj tylko, żeby przy ręcznym wstawianiu tej sekcji zamienić podany w niej klucz zmienić na ten, który zostanie wyświetlony po zainstalowaniu wtyczki.</p>

			<h3>Folder załączników</h3>
			<p>Wtyczka nie będzie chroniła przed hotlinkami, które zostały utworzone przed jej zainstalowaniem. Aby zapewnić pełną ochronę swoich załączników zalecana jest zmiana nazwy katalogu w którym trzymane są załączniki (<?php echo get_option('upload_path');?>) na inną. Aby zmienić nazwę katalogu w którym trzymane są załączniki, wpisz nową nazwę w polu poniżej. Wtyczka zmieni nazwę folderu na wybraną przez Ciebie, a także uaktualni konfigurację WordPressa i zmieni ścieżkę dostępu do załączników w treści wszystkich postów. Jeżeli nie chcesz zmieniać nazwy katalogu, pozostaw to pole puste.</p>
		
			<form action="" method="post" id="GeneratorKluczy">
			<table class="form-table"> 
			<tbody>
				<tr valign='top'> 
				<th scope='row'>Stara nazwa katalogu:</th> 
				<td><input name='old_upload_path' type='text' id='old_upload_path' readonly value='<?php echo get_option('upload_path');?>' size='39' /></td> 
			</tr>
			<tbody>
				<tr valign='top'> 
				<th scope='row'>Nowa nazwa katalogu:</th> 
				<td><input name='new_upload_path' type='text' id='new_upload_path' value='' size='39' /></td> 
			</tr>
			</table>
		
			<p class="submit"><input type="submit" name="ZainstalujSzeryfa" value="Zainstaluj Szeryfa" /></p>
			</form>
			<?
			}
		?>
	</div>
	<?
	}

function SzeryfContent($content)
	{
	$upload_path = get_option('upload_path');
	if($klucz_szeryfa = get_option('klucz_szeryfa'))
		{$content = str_replace($upload_path,$klucz_szeryfa,$content);}
	return $content;
	}

function SzeryfMenu()
	{
	add_options_page('Szeryf', 'Szeryf', 0, __FILE__, 'SzeryfUstawienia');
	}

function GenerujKlucz()
	{
	global $file_prefix;
	$nowy_klucz = substr(md5(time()), 0, 20);
	update_option('klucz_szeryfa', $nowy_klucz);

	$upload_path = get_option('upload_path');
	
	if(function_exists('wp_cache_clean_cache')) wp_cache_clean_cache($file_prefix);
	
	$home_path = get_home_path();
	$home_root = parse_url(get_bloginfo('url'));
	$home_root = trailingslashit($home_root['path']);

	$rules = "<IfModule mod_rewrite.c>\n";
	if(!function_exists('wp_cache_clean_cache')) $rules .= "RewriteEngine On\n";
	if(!function_exists('wp_cache_clean_cache')) $rules .= "RewriteBase $home_root\n";
	$rules .= "RewriteRule ^".$nowy_klucz."/(.*)$ /".$upload_path."/$1 [L]\n";
	$rules .= "</IfModule>\n";
	
	insert_with_markers($home_path.'.htaccess', 'Szeryf', explode("\n", $rules));
	}

add_filter('the_content', 'SzeryfContent', 0);
add_action('admin_menu','SzeryfMenu');
?>