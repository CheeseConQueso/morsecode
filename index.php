<html>
<head>
<body bgcolor=33CCFF alink="yellow" vlink="red" link="orange">
<head>
<title>Morse Code Machine</title>
</head>
<font face=arial size=4>
<?php
/*
SOURCE FILES & WORKING DEMO:
	MAIN SITE (CONTAINS MULTIPLE MIDI UTILITIES)
		http://valentin.dasdeck.com/midi/
	RTTL TO MIDI CONVERSION SITE
		http://valentin.dasdeck.com/midi/rttl2mid.php

=====================================
TODO LIST:
=====================================
[1]-ADD FORM FIELDS
	BPM
	OCTAVE
	INSTRUMENT
	REPEAT
	NOTE LENGTH
	DASHES AS TRIPLETS OR AS EIGTH NOTES

[2]-ADD MIDI PLAYER OPTIONS
	STEAL CODE FROM VALENTINE
	EMBED PLAYER INTO WEBPAGE
	
[3]-ADD OPTION TO SAVE
	SAVE ONLY MIDI FILE
	SAVE ONLY TABLATURE
	SAVE BOTH OF THEM
		(ZIP VS TARBALL - NEED TO DETERMINE OS AND/OR BROWSER)

[4]-MODIFY DB TABLE 
	MAKE CHANGES AS PROGRESS SEES FIT
		MAYBE DETECT BROWSER AND OS TO AUTOMATICALLY SELECT A COMPATIBLE PLAYER
	
[5]-TRY TO INCORPORATE A METRONOME
	IF POSSIBLE, 
		LET USER CHOOSE CLICK NOTE DURATION 
		LET USER CHOOSE CLICK SOUND
		LET USER DEFINE ACCENTS ON 3/4, 4/4, ETC...
		
[6]-TRY TO BUILD A SIMPLE DRUM BEAT
	DEFINE A STEADY HI-HAT
		OPTION FOR OPEN OR CLOSED SOUND
		OPTION FOR NOTE DURATION
	DEFINE A STEADY SNARE
		OPTION TO SUBDIVIDE & ACCENT (UP BEAT) BY 1/8TH OR 1/4 NOTES
	DEFINE A STEADY BASS DRUM
		OPTION TO SUBDIVIDE & ACCENT (DOWN BEAT) BY 1/8TH OR 1/4 NOTES
		
[7]-FIX THE TIME SIGNATURE CALCULATION (SEE SUBMISSIONS TABLE)
	ONLY RECORDING THE LAST LETTER'S TIMING
		=
*/
//20140408
//code from /home/besthome/public_html/10SurfRoad.com/morsecode/rttl2mid.php
//BEGINCODE

$PHP_SELF = $_SERVER['PHP_SELF'];



//ENDCODE

//Form submission GET variables
//	"note_length", "octave", "bpm" - deprecated, but should be revived after bugs worked out

//If "phrase" is submitted
//	Assign to variable
//	Convert string to lowercase letters
//	Remove special characters (ex: ",")
if($_POST["phrase"])		
{
	$phrase	= $_POST["phrase"]; 		
	$phrase = strtolower($phrase); 
	
	$phrase = trim(preg_replace('/[^a-z]+/i', ' ', $phrase));
}
//If "note_length" is submitted, assign to variable otherwise set default length of a 16th note
if($_POST["note_length"]){$note_length=$_POST["note_length"];}else{$note_length=16;}

//If "octave" is submitted, assign to variable, otherwise set default of 5
if($_POST["octave"]){$octave=$_POST["octave"];}else{$octave=5;}

//If "bpm" is submitted, assign to variable, otherwise set default of 200
if($_POST["bpm"]){$bpm=$_POST["bpm"];}else{$bpm=200;}

echo '<head>';
echo '<title>Morse Code Machine'; if($phrase){echo " - $phrase";}
echo '</title>';
echo '</head>';

$img_dir = "http://www.10surfroad.com/morsecode/_img/";

//Header
echo '<center><font face="arial black" size=3 color="orange"><b>::';
//echo '<a href="http://www.10surfroad.com/morsecode/x.php" style="text-dec oration:none;">';
echo '<a href="'.$PHP_SELF.'" style="text-dec oration:none;">';
echo '<font color="maroom">MORSE CODE MACHINE</font>';
//echo '<br>'.$PHP_SELF.'<br>';
echo '</a><font color="orange">::</font>';
echo '</b></font></center><hr color="white">';

//Form begin
//echo '<form name="input" action="'.$PHP_SELF.'?submit=1" method="get">';
echo '<form name="input" action="'.$PHP_SELF.'?submit=1" method="post">';

echo '<center>';

echo '<table border=1>';

	echo '<tr>';
		echo '<td width=234>';
			echo '<center><font face="arial black" size=3><b>ENTER WORD/PHRASE</b></font></center>';
		echo '</td>';
		echo '<td>';
			echo '<input type="text" name="phrase" value="'.$phrase.'">';
		echo '</td>';
		echo '<td>';
			echo '<font face="arial black" size=2><b>(LETTERS ONLY <=512 CHARACTERS)</b></font>';
		echo '</td>';
	echo '</tr>';
	
	// echo '<tr>';
		// echo '<td width=123>';
			// echo 'NOTE LENGTH';
		// echo '</td>';
		// echo '<td>';
			// echo '<input type="text" name="note_length" value="'.$note_length.'">';
		// echo '</td>';
		// echo '<td>';
			// echo 'NUMBERS ONLY >=1';
		// echo '</td>';
	// echo '</tr>';
	
	// echo '<tr>';
		// echo '<td width=123>';
			// echo 'BPM';
		// echo '</td>';
		// echo '<td>';
			// echo '<input type="text" name="bpm" value="'.$bpm.'">';
		// echo '</td>';
		// echo '<td>';
			// echo 'NUMBERS ONLY >=1';
		// echo '</td>';
	// echo '</tr>';
	
	echo '<tr>';
		echo '<td colspan=3>';
			echo '<center><input type="submit" value="Submit"></center>';
		echo '</td>';
	echo '</tr>';
		
echo '</table>';

echo '</center>';

echo '</form>';
//Form end

if($phrase)
{	
	$phrase_crunch = str_replace(" ","",$phrase);
	if((strlen($phrase))>512 || (!(preg_match('/^\p{N}*$/',$note_length))) || (!($note_length>0)) || (!(preg_match('/^\p{N}*$/',$bpm))) || (!($bpm>0)) || (!(ctype_alpha($phrase_crunch))))
	{
		echo "<hr><center><font size=5 color=red><b>INPUT ERROR:<br>One or more of these rules were broken.<br>Go back and try again.</font></center><hr>";
		echo "PHRASE must be letters (512 characters max)<br>"; 
		echo "NOTE LENGTH must be a number greater than 0<br>"; 
		echo "BPM must be a number greater than 0"; 
		
		exit;
	}	
	
	//require('./_inc/midi.class.php');		
	
	$connect = mysql_connect(localhost, besthome_query, query);	
	if (!$connect){echo '<p>Unable to connect at this time.</p>'; exit();}	
	if (!@mysql_select_db('besthome_morsecode')){exit('<p>Unable to locate the table ' . 'database at this time.</p>');}
	
	$WORDS 				= 	explode(" ",$phrase);
	$word_count 		= 	count($WORDS);
	
	$MORSE_CODE_ARRAY 	= 	array();	/*ex*/	//(..-.,---,.-.,/,.,-..-)
	
										//$SEQUENCE_ARRAY ex		/*ex*/	//        F                      O        	 M		 O	   R				.....etc
	$SEQUENCE_ARRAY 	= 	array();	//boolean: 0=blank, 1=note		/*ex*/	//(1,0,1,0,1,1,1,0,1	0,0,0	1,1,1,0,1,1,1,0,1,1,1	0,0,0	1,0,1,1,1,0,1				.....etc
	$TABLATURE_ARRAY	= 	array(); 	//format: [S#:F#]			/*ex*/	//([S2:F3],[S0:F0],[S2:F3],[S0:F0],[S2:F3],[S2:F3],[S2:F3],[S0:F0],[S2:F3]	.....etc)

	$RTTL_ARRAY		=	array();	//RTTL format ([filename]):([d=x],[o=x],[b=x]):([time note octave],[time note octave])
										//creep:d=16,o=5,b=112:d2,f2,g#2,b2,d3,f3,d3,b2,g#2,f2,d2

	//Loop through each word in the phrase
	foreach($WORDS as $key=>$word)
	{
		$CHAR_ARRAY = str_split($word);
		$char_count	= count($CHAR_ARRAY);
		
		if($key>0)
		{
			array_push($MORSE_CODE_ARRAY," / ");
			array_push($RTTL_ARRAY,",p,p,p,p,p,p,p,");
			for($i=0;$i<7;$i++)
			{
				array_push($SEQUENCE_ARRAY,0); 
				array_push($TABLATURE_ARRAY,"[S0:F0]");
			}
		}
		
		//Loop through each char in current $word
		foreach($CHAR_ARRAY as $key2=>$char)	
		{
			if($key2>0)
			{
				array_push($MORSE_CODE_ARRAY," ");
				array_push($RTTL_ARRAY,",p,p,p,");
				for($i=0;$i<3;$i++)
				{
					array_push($SEQUENCE_ARRAY,0); 
					array_push($TABLATURE_ARRAY,"[S0:F0]");
				}
			}
			$query = 'SELECT * FROM `morse_code` WHERE `character` = "'.$char.'"';
			mysql_real_escape_string($query);
			$result = @mysql_query($query);

			$char_found=0; //666

			//Parse each data row for char
			while($result_row = mysql_fetch_array($result))	
			{
				$char_found++; //666

				$character	=	$result_row['character'];
				$morse_code	=	$result_row['morse_code'];
				$sixteenths 	= 	$result_row['16ths'];
				$string		=	$result_row['string'];
				$fret		=	$result_row['fret'];
				$tab		=	$result_row['tab'];
				$note		=	$result_row['note'];
				$rttl		=	$result_row['rttl'];
				
				$ttl_sixteenths = $ttl_sixteenths+$sixteenths; //666
				$char_found=0; //666

				array_push($MORSE_CODE_ARRAY,$morse_code);
				array_push($RTTL_ARRAY,$rttl);
				
				$CHAR_PATTERN_ARRAY	=	str_split($morse_code);
				$char_pattern_count 	= 	count($CHAR_PATTERN_ARRAY);
				
				//Loop through each unit of char pattern
				foreach($CHAR_PATTERN_ARRAY as $key3=>$char_unit)	
				{
					//Morse code "dot" unit
					if($char_unit==".")
					{
						array_push($SEQUENCE_ARRAY,1); 
						array_push($TABLATURE_ARRAY,$tab);
					}
					
					//Morse code "dash" unit
					if($char_unit=="-")
					{
						for($i=0;$i<3;$i++)
						{
							array_push($SEQUENCE_ARRAY,1); 
							array_push($TABLATURE_ARRAY,$tab);
						}
					}
					
					//Morse code "rest" between units
					if($char_pattern_count!=1 && $key3<$char_pattern_count-1)
					{
						array_push($SEQUENCE_ARRAY,0); 
						array_push($TABLATURE_ARRAY,"[S0:F0]");
					}
				}
			}
		}
	}

	//Build bass tablature for phrase
	$tabs=1;
	if($tabs==1)
	{
		$G_ARRAY 	=	array();
		$D_ARRAY 	= 	array();
		$A_ARRAY 	= 	array();
		$E_ARRAY 	= 	array();
		
		foreach($TABLATURE_ARRAY as $tab_key=>$tab)
		{
			$query = 'SELECT string, fret FROM morse_code WHERE tab = "'.$tab.'" LIMIT 0,1';
			mysql_real_escape_string($query);
			$result = @mysql_query($query);
			
			while($result_row = mysql_fetch_array($result))
			{
				$string = $result_row['string'];
				$fret 	= $result_row['fret'];
				
				if($string==0)
				{
					array_push($G_ARRAY,"-");
					array_push($D_ARRAY,"-");
					array_push($A_ARRAY,"-");
					array_push($E_ARRAY,"-");
				}
				if($string==1)
				{
					array_push($G_ARRAY,"-");
					array_push($D_ARRAY,"-");
					array_push($A_ARRAY,"-");
					array_push($E_ARRAY,$fret);
				}
				if($string==2)
				{
					array_push($G_ARRAY,"-");
					array_push($D_ARRAY,"-");
					array_push($A_ARRAY,$fret);
					array_push($E_ARRAY,"-");
				}
				if($string==3)
				{
					array_push($G_ARRAY,"-");
					array_push($D_ARRAY,$fret);
					array_push($A_ARRAY,"-");
					array_push($E_ARRAY,"-");
				}
				if($string==4)
				{
					array_push($G_ARRAY,$fret);
					array_push($D_ARRAY,"-");
					array_push($A_ARRAY,"-");
					array_push($E_ARRAY,"-");
				}
			}
		}

		$TABS = array($G_ARRAY,$D_ARRAY,$A_ARRAY,$E_ARRAY);
		
		$tab_url 		= 	'./_tabs/'.$phrase.'.txt';
		$midi_url		= 	'./_midi/'.$phrase.'.mid';
		
		$tab_fullpath 	= 	'http://www.10surfroad.com/morsecode/_tabs/'.$phrase.'.txt';
		$midi_fullpath 	= 	'http://www.10surfroad.com/morsecode/_midi/'.$phrase.'.mid';	
			
		$tab_fullpath 	= 	str_replace(" ","%20",$tab_fullpath);
		$midi_fullpath 	= 	str_replace(" ","%20",$midi_fullpath);
			
		$fh = fopen($tab_url, 'w') or die("can't open file");
		
		//Determine the amount of newlines needed to fit on page properly
		$splits = ((ceil((count($E_ARRAY)/64)))+1);
		
		//Print tablature
		//Print first few bars until newline reached
		for($m=64;$m<($splits*64);$m+=64)
		{
			//Print tabs one string at a time starting with G, ending with E string
			for($i=0;$i<4;$i++)
			{
				//Print note unless at end of bar - then print "|" to separate bars
				for($n=$m-64;$n<$m;$n++)
				{
					if($n%16==0){fwrite($fh,"|");}
					fwrite($fh,$TABS[$i][$n]);
				}
				//At end of each newline, close the bar with "|"
				fwrite($fh,"|\n");
			}
			//Start newline of tabs
			fwrite($fh,"\n\n\n");
		}
		
		fclose($fh);
	}

	//echo 'Morse code for: "'.$phrase.'"<br>';
	//$CHAR_SET = str_split($phrase);
	
	//$char_pos = 0;
	
	//Echo Morse code notation for submitted phrase
	/*
	echo '<font face="terminal" size=3 color=maroon>';
	echo '<table border=1>';
	foreach($MORSE_CODE_ARRAY as $morse_code_key=>$morse_code)
	{
		echo '<tr>';
			echo '<td width=50>';
				echo '<font color="red" size=2>'.strtoupper($CHAR_SET[$char_pos]).'</font>';
			echo '</td>';

			echo '<td width=50>';
				echo '<font color="blacl" size=3><b>'.$morse_code.'</b></font>';
			echo '</td>';
		echo '</tr>';
		
		echo '<br>'; 
		
		$char_pos++;
		$morse_code_pattern = $morse_code_pattern.$morse_code;
	} 
	echo '</table>';
	echo '</font>';
	*/
	
	//echo '<br>';
	
	//Binary time-length notation for submitted phrase
	/*
 	foreach($SEQUENCE_ARRAY as $sequence_key=>$sequence)		
	{
		echo $sequence;
	} 
	
	echo "<br>"; 
	*/
	
	//2014-04-05
	//Calculate the time signature based on the sum of 
	//each letter by querying the morsecode table in a
	//foreach loop that goes through the word/phrase letter 
	//by letter.  add rests/blank space to the time 
	//signature at the end
	
	$time_signature = (count($SEQUENCE_ARRAY)%16)."/16";
	//echo "Time signature: ".$time_signature."<br>";	
	$extra_time 	= (count($SEQUENCE_ARRAY))%16;		
	//echo "Extra time: $extra_time/16<br><br>";
	
	//echo "<hr>";
	//echo 'rttl code for: '.$phrase.'<br>';
	
	$rttl_url	=	"http://valentin.dasdeck.com/midi/rttl2mid.php?rttl=";
	$rttl_string	=	"rttl_code:d=16,o=5,b=200:";
	foreach($RTTL_ARRAY as $rttl_key=>$rttl){$rttl_string=$rttl_string.$rttl;}
	
	$rttl_insert = $rttl_string;
	
	//Display RTTL tabs for copying & pasting
	//take this out once the valentine script is incorporated

	$rttl_prefix = "http://valentin.dasdeck.com/midi/rttl2mid.php?";
	$rttl_suffix = "&inst=0&player=default&autostart=on&visible=on";
	$rttl_valentines_day = $rttl_prefix;
	$rttl_valentines_day .= $rttl_string; 
	$rttl_valentines_day .= $rttl_suffix; 


	$query = 'SELECT string, fret FROM morse_code WHERE tab = "'.$tab.'" LIMIT 0,1';
	mysql_real_escape_string($query);
	$result = @mysql_query($query);
			
	while($result_row = mysql_fetch_array($result))
	{
	
		$string = $result_row['string'];
		$fret 	= $result_row['fret'];
		
		if($string==0)
		{
			array_push($G_ARRAY,"-");
			array_push($D_ARRAY,"-");
			array_push($A_ARRAY,"-");
			array_push($E_ARRAY,"-");
		}
		if($string==1)
		{
			array_push($G_ARRAY,"-");
			array_push($D_ARRAY,"-");
			array_push($A_ARRAY,"-");
			array_push($E_ARRAY,$fret);
		}
		if($string==2)
		{
			array_push($G_ARRAY,"-");
			array_push($D_ARRAY,"-");
			array_push($A_ARRAY,$fret);
			array_push($E_ARRAY,"-");
		}
		if($string==3)
		{
			array_push($G_ARRAY,"-");
			array_push($D_ARRAY,$fret);
			array_push($A_ARRAY,"-");
			array_push($E_ARRAY,"-");
		}
		if($string==4)
		{
			array_push($G_ARRAY,$fret);
			array_push($D_ARRAY,"-");
			array_push($A_ARRAY,"-");
			array_push($E_ARRAY,"-");
		}

	}
	
	
/*
	//BEGIN NEW TIME SIG CALC
	//2014-04-05
	//New time signature calculation method
	$newsig = 1;
	if(($newsig==1)&&($phrase))
	{
		echo '<hr><hr>';
		echo '<table border=3 bordercolor=red color=white>';
		echo '<tr><td colspan=3><center><font face="arial black" size=5 color="yellow"><b>PHRASE: '.$phrase.'</b></font></center></td></tr>';
		echo '<tr><td>Char</td><td>16ths</td><td>Ttl Time</td></tr>';
		$ttl_sixteenths = 0;
		$PHRASE_ARRAY = explode(' ', $phrase);
		foreach($PHRASE_ARRAY as $phrase_letter=>$letter)
		{
			echo '<tr>';
			echo '<td>'.$letter.'</td>';
			$sixteenths=0;
			$x=0;
			$char_query = 'SELECT 16ths FROM morse_code WHERE character = "'.$letter.'" AND rttl <> "p"';
			mysql_real_escape_string($char_query);
			$char_result = @mysql_query($char_query);
			while($char_result_row = mysql_fetch_array($char_result))
			{
				$x++;
				$sixteenths=$char_result_row['16ths'];
			}
			if($x==0){$sixteenths=1;}
			echo '<td>'.$sixteenths.'</td>';
			$ttl_sixteenths = $ttl_sixteenths + $char_time;
			echo '<td>'.$ttl_sixteeths.'</td></tr>';
		}
		
		echo '</table>';
		echo '<hr><hr>';
		//END NEW TIME SIG TEST
		*/
		
	/*
	echo '<center><hr color="white" width=300>'.$phrase.'<hr color="white" width=300></center>';
	
	echo '<br>';
	echo '<center>';
	//echo '<table border=2 width=234>';
	echo '<table style="table-layout: fixed; width: 100%" border=2>';
		echo '<tr>';
			//echo '<td colspan=2 bgcolor=white width=234>';
			echo '<td style="word-wrap: break-word" bgcolor=white>';
				echo '<center><font face="arial black" size=2>'.$rttl_string.'</font></center>';
			echo '</td>';
		echo '</tr>';
	echo '</table>';
	echo '</center>';
	*/
	
	echo '<br>';
	
	//20140408 - commented out - supposed to send link with php var directly to valentine, but doesnt work
	//echo '<a href="'.$rttl_valentines_day.'">RTTL BITCH</a>'; 
	
	echo '<br>';			//666
	// echo '<textarea rows="10" cols="80" name="comment" form="usrform">';
	// echo $rttl_string;
	// echo '</textarea><br>';
	
	//Prepare $rttl_string for HTTP protocol
	$rttl_string 	= 	str_replace(":","%3A",$rttl_string);
	$rttl_string 	= 	str_replace("=","%3D",$rttl_string);
	$rttl_string 	=	str_replace(",","%2C",$rttl_string);
	//$rttttl_string = $rttl_string.(str_replace(",","%2C",str_recplace(":","%3A",(str_replace("=","%3A",$rttl_string)))));
	
	$rttl_string 	= 	$rttl_string."&inst=0&player=ogg_html5&autostart=on&loop=on&visible=on";
	
	$rttl_url	=	$rttl_url.$rttl_string;
	
	
	
	//666
	
	$query = 'SELECT id FROM `submissions` WHERE `phrase` = "'.$phrase.'"';
	mysql_real_escape_string($query);
	$result = @mysql_query($query);
	while($result_row = mysql_fetch_array($result)){$id = $results_row['id'];}
	
	if (isset($_POST['player']))
	{
		$player 	= $_POST['player'];
		$autostart 	= isset($_POST['autostart']);
		$loop 		= isset($_POST['loop']);
		$visible 	= isset($_POST['visible']);
	}
	else
	{
		$player 	= 'mp3_flash';
		$autostart 	= true;
		$loop 		= false;
		$visible 	= true;
	}

	$inst = isset($_POST['inst'])?$_POST['inst']:0;
	$rttl = $rttl_insert;
	$plug = isset($_POST['plug'])?$_POST['plug']:'qt';

	require('midi_rttl.class.php');

	$midi = new MidiRttl();
	$instruments = $midi->getInstrumentList();
	
	
	$save_dir = '_midi/';
	//srand((double)microtime()*1000000);
	$file = $save_dir.$phrase.'.mid';

	$midi->importRttl($rttl_insert,$inst);
	$midi->saveMidFile($file, 0666);
	//$midi->playMidFile($file,$visible,$autostart,$loop,$player);
	
	/*
	<input type="button" name="download" value="Save as SMF (*.mid)" onclick="self.location.href='download.php?f=<?=urlencode($file)?>'" />	
	*/
	
	
	//echo '<a href="http://valentin.dasdeck.com/midi/rttl2mid.php" target=new><br>RTTL to MIDI</br></a><br>';
	echo '<center>';
	echo '<font face="arial black" size=5><b><a href="'.$file.'" target="new">Download the MIDI for '.$phrase.'</a></b></font><br>';
	echo '<font face="arial black" size=5><b><a href="'.$tab_url.'" target="new">View the tabs for '.$phrase.'</a></b></font><br><hr><br>';
	
	
	echo '</center>';
	
	//Display RTTL code (old version)
	/*
	echo "rttl_code:d=16,o=5,b=200:";
	foreach($RTTL_ARRAY as $rttl_key=>$rttl)
	{
		echo $rttl;
	} 
	*/
	//echo "<hr>";
	//echo "<font face=orange size=3><b>".$rttl_string."</b></font><br>";
	//echo "<font color=yellow>".$rttl_url."</font><br>";
	//echo '<a href="'.$rttl_url.'" target="new">Listen to the MIDI for '.$phrase.'</a>';
	//echo "<hr>";
	
	//echo '<a href="'.$tab_url.'" target="new">View the tabs for '.$phrase.'</a><br><hr><br>';
	
	$dupe=0;
	$query = 'SELECT id FROM `submissions` WHERE `phrase` = "'.$phrase.'"';
	mysql_real_escape_string($query);
	$result = @mysql_query($query);
	while($result_row = mysql_fetch_array($result)){$dupe++;}
	
	if($dupe==0)
	{
		$db = new MySQLi(localhost,besthome_query,query,besthome_morsecode);
		$stmt = $db->stmt_init();
		$insert_query = "INSERT INTO submissions (phrase,rttl,time_signature,tab_url,midi_url) VALUES (?,?,?,?,?)";
		$stmt->prepare($insert_query);
		$stmt->bind_param('sssss',$phrase,$rttl_insert,$ttl_sixteenths,$tab_fullpath,$midi_fullpath);
		$stmt->execute();
	}
	
	if($tabs==0 && $phrase)
	{
		//Begin sequencing
		//MORSE CODE PATTERN
		echo '<table border=1>';
			echo '<tr>';
				//Loop through $SEQUENCE_ARRAY array
				foreach($SEQUENCE_ARRAY as $sequence_key=>$sequence)
				{
					if($sequence==0){$bgcolor = "white";}else{$bgcolor = "black";}
					//if($sequence==1){$bgcolor = "black";}
					
					//echo '<td height=20 width=20 bgcolor="'.$bgcolor.'"><font color="white" size=1><center>'.$TABLATURE_ARRAY[$sequence_key].'</center></font></td>';
					echo '<td height=20 width=20 bgcolor="'.$bgcolor.'"></td>';
				}
			echo '</tr>';
		echo '</table>';


		//HIGHHAT PATTERN
		$notes = 0;
		$bgcolor = "white";
		echo '<table border=1>';
			echo '<tr>';
				while($notes<64)
				{
					if($notes==0||$notes==4||$notes==8||$notes==12||$notes==16||$notes==20||$notes==24||$notes==28||$notes==32||$notes==36||$notes==40||$notes==44||$notes==48||$notes==52||$notes==56||$notes==60){$bgcolor="black";}else{$bgcolor="white";}
					echo '<td height=20 width=20 bgcolor="'.$bgcolor.'"></td>';
					$notes++;
				}
			echo '</tr>';
		echo '</table>';

		//SNARE PATTERN
		$notes = 0;
		$bgcolor = "white";
		echo '<table border=1>';
			echo '<tr>';
				while($notes<64)
				{
					if($notes==8||$notes==24||$notes==40||$notes==56){$bgcolor="black";}else{$bgcolor="white";}
					echo '<td height=20 width=20 bgcolor="'.$bgcolor.'"></td>';
					$notes++;
				}
			echo '</tr>';
		echo '</table>';
	}
}

echo '<center>';
	echo '<table border=1 width=1000>';
	
		//Morse Code Rules
		echo '<tr>';
			echo '<td>';
				echo '<center><img src="'.$img_dir.'morse_chart.png" height=300></center>';
			echo '</td>';
			echo '<td>';
				echo '<font face=arial black size=4>';
				echo '<center><b>Morse Code Rules</b></center><br>';
				echo '<ul>';
					echo '<li>The length of a dot is <b>1</b> unit</li><br>';
					echo '<li>The length of a dash is <b>3</b> units</li><br>';
					echo '<li>The length between parts of the same letter are <b>1</b> unit</li><br>';
					echo '<li>The length between letters is <b>3</b> units</li><br>';
					echo '<li>The length between words is <b>7</b> units</li><br>';
				echo '</ul>';
				echo '</font>';
			echo '</td>';
		echo '</tr>';
		
		//My Rules
		echo '<tr>';
			echo '<td colspan=2>';
				echo '<font face=arial black size=4>';
				echo '<center><b>My Rules</b></center>';
				echo '<ul>';
					echo "<li>The note string is derived from the number of units in a letter's pattern</li><br>";
					echo "<li>The note fret is derived from the letter's sequence in the alphabet</li><br>";
					echo '<li>Notes are reduced to their lowest octave (This changes the string and fret assignment)</li><br>';
					//echo '<li>Notes for vowels are reduced to their lowest octave and then raised one octave</li><br>';
				echo '</ul>';
				echo '</font>';
			echo '</td>';
		echo '</tr>';
	echo '</table>';
echo '</center>';

//Graphical representation of rhythm patterns for each letter of the alphabet in Morse doe
/*
echo '<center>';
	echo '<table border=1 width=1000>';
		echo '<tr>';
			echo '<td>';
				echo '<center>';
					echo '<img src="'.$img_dir.'morse_grid.png" height=600>';
				echo '</center>';
			echo '</td>';
		echo '</tr>';
	echo '</table>';
echo '</center>';
*/

echo '<hr width=1000 color=maroon>';

/*
$query = 'SELECT `phrase`, `tab_url`, `midi_url`, `timestamp` FROM `submissions` ORDER BY `id` DESC';
mysql_real_escape_string($query);
$result = @mysql_query($query);

echo '<center>';
	echo '<table border=1>';
		echo '<tr>';
			echo '<td>';
				echo '<font face="arial black" size=4><b>PHRASE</b></font>';
			echo '</td>';
			echo '<td>';
				echo '<font face="arial black" size=4><b>TABS</b></font>';
			echo '</td>';
			echo '<td>';
				echo '<font face="arial black" size=4><b>MIDI</b></font>';
			echo '</td>';
			echo '<td>';
				echo '<font face="arial black" size=4><b>TIMESTAMP</b></font>';
			echo '</td>';
		echo '</tr>';
		
		while($result_row = mysql_fetch_array($result))
		{
			$db_phrase 	= $result_row['phrase'];
			$db_tab_url 	= $result_row['tab_url'];
			$db_midi_url 	= $result_row['midi_url'];
			$db_timestamp	= $result_row['timestamp'];
			
			echo '<tr>';
				echo '<td>';
					echo $db_phrase;
				echo '</td>';
				echo '<td>';
					echo $db_tab_url;
				echo '</td>';
				echo '<td>';
					echo $db_midi_url;
				echo '</td>';
				echo '<td>';
					echo $db_timestamp;
				echo '</td>';
			echo '</tr>';
		
		}
	echo '</table>';
echo '</center>';
*/
?>
</body>
</html>
