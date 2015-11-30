<?php

/**
 * Full database migration
 *
 * Script to move big database beetween 2 hosts.
 * When you don't have access to SSH (and because phpMyAdmin suck over 2Mo DB...)
 * Creative Commons .
 *
 * Deliver 'as it is' Absolutly no warranty... you use it at your own risk (but it normaly works)
 * 
 * If you upgrade this scipt, I should be please to get your version, please email daha@waloomaloo.com
 *
 * @author David HAURILLON - ACAPULCO Production - WalooMaloo
 * 
 * @version 1.0
 */
session_start();



/**
 * configuration ==============================================================================================================
 */


// database source
 $db['source']['host']='localhost';
 $db['source']['database']='source_database';
 $db['source']['login']='root';
 $db['source']['password']='user_source';
 $db['source']['charset']='password';


// database destination
 $db['destination']['host']='localhost';
 $db['destination']['database']='destination_database';
 $db['destination']['login']='user_destination';
 $db['destination']['password']='pass';
 $db['destination']['charset']='utf8';


// number of row to copy at once
 define('NUMMBER_OF_ROW_TO_COPY',50);
// ===========================================================================================================================



// Sript URL
define('SELF_URL',$_SERVER[ 'HTTP_HOST'].$_SERVER[ 'REQUEST_URI']);




/**
 *  connexions aux BDD
 */
try{
    $db['source']['link'] = new PDO('mysql:host='. $db['source']['host'].';dbname='. $db['source']['database'].';charset='. $db['source']['charset'],  $db['source']['login'],  $db['source']['password']);
}catch (Exception $e){
        die('Erreur connexion BDD 1: ' . $e->getMessage());
}

try{
    $db['destination']['link'] = new PDO('mysql:host='. $db['destination']['host'].';dbname='. $db['destination']['database'].';charset='. $db['destination']['charset'],  $db['destination']['login'],  $db['destination']['password']);
}catch (Exception $e){
        die('Erreur connexion BDD 2: ' . $e->getMessage());
}



/**
 * liste tables BDD
 */
try {   
        $tableList = array();
        $result = $db['source']['link']->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tableList[$row[0]] =array();
             $tableList[$row[0]] ['name']= $row[0];
        }
       // print_r($tableList);
    }
    catch (PDOException $e) {
        echo $e->getMessage();
    }



if(!isset($_GET['ajaxRequest'])){  // no ajax ****************************************************************************************************************************

	// get create tables SQL query
	foreach ($tableList as $k => $v) {
	try {   
	       $result = $db['source']['link']->query("SHOW CREATE TABLE ".$k);
	        while ($row = $result->fetch(PDO::FETCH_NUM)) {
	            $tableList[$k]['create'] = $row[1];
	        }
	    }
	    catch (PDOException $e) {
	        echo $e->getMessage();
	    }
	}

	// get col name
	foreach ($tableList as $k => $v) {
	try {   
	       $result = $db['source']['link']->query("SHOW COLUMNS FROM ".$k);
	        while ($row = $result->fetch(PDO::FETCH_NUM)) {
	            $tableList[$k]['col'][] = $row[0];
	        }
	    }
	    catch (PDOException $e) {
	        echo $e->getMessage();
	    }
	}

	// get col name
	foreach ($tableList as $k => $v) {
	try {   
	       $result = $db['source']['link']->query("SELECT count(*) FROM ".$k);
	        while ($row = $result->fetch(PDO::FETCH_NUM)) {
	            $tableList[$k]['count'] = $row[0];
	        }
	    }
	    catch (PDOException $e) {
	        echo $e->getMessage();
	    }
	}



	// first html display > full page render ****************************************************************************************************************************
	?>

	<html>
		<header>
			<title>DB move</title>
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
	
			<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

			<!-- Optional theme -->
			<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

			<!-- Latest compiled and minified JavaScript -->
			<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

		</header>
		
		<body>
		
		<h1>Copy from <?php echo $db['source']['host'].' : '.$db['source']['database'];?> to <?php echo $db['destination']['host'].' : '.$db['destination']['database'];?></h1>


<ul class="nav nav-tabs" id="myTab">
  <li ><a data-toggle="tab" href="#Tables">Tables</a></li>
  <li><a data-toggle="tab" href="#Create">Create</a></li>
  <li class="active"><a data-toggle="tab" href="#Transfert">Transfert</a></li>
  <li ><a data-toggle="tab" href="#Debug">Debug</a></li>
 </ul>


<div class="tab-content" id="myTabContent">

<div id="Tables" class="tab-pane ">
		<h2>Tables:</h2>
		<ul>
			<?php
				foreach ($tableList as $k => $table) {
					echo '<li>'.$k.'</li>';
				}
			?>
		</ul>

		<h2>Tables structures:</h2>
		<ul>
			<?php
				foreach ($tableList as $k => $create_table) {
					echo '<li><h3>'.$k.'</h3><pre>'.$create_table['create'].'</pre></li>';
					# code...
				}
			?>
		</ul>
</div>



<div id="Create" class="tab-pane ">

		<h2>Creates tables</h2>
		<ul>
			<?php
				foreach ($tableList as $k => $create_table) {
				echo '<li><strong>Create: '.$k.'</strong>';

				try {   
				        $result = $db['destination']['link']->query($create_table['create']);
				       // print_r($tableList);
				    }
				    catch (PDOException $e) {
				    	echo "error => ";
				        echo $e->getMessage();
				    }

					echo '</li>';
								# code...
					}
			?>
		</ul>
</div>


<div id="Transfert" class="tab-pane fade active in">

		<h2>Transfert: <span id="tranfertDetail"></span></h2>
		<button clas=="btn btn-alert alert alert-danger" id="stopBTN">STOP</button>
		<ul id="table_list">
			<?php
			//print_r($tableList);
				foreach ($tableList as $k => $table) {
					echo '<li class="li_transfert">'.$k.' ....... <span class="output_ajax" id="'.$k.'" data-done="0" data-total="'.$table['count'].'" ><span class="info">'.$table['count'].'</span></span></li>';
				}
			?>
		</ul>
		<div id="error_log"></div>
</div>



<div id="Debug" class="tab-pane ">

<h2>*********** DEBUG ***********************</h2>

<h3> $db </h3>
<pre>
	<?php print_r($db); ?>
</pre>


<h3> $tableList </h3>
<pre>
	<?php print_r($tableList); ?>
</pre>

</div>





</div>


<?php

/**
 * JAVASCRIPT ***************************************************************************************
 */

?>


	<script type="text/javascript">

	//url to script ajax callback
	var self_url="<?php echo 'http://'.SELF_URL;?>";
	// array with items to do
	var toDo=new Array();
	// global status of task
	var allDone=false;
	// number of item to do
	var nToDo=0;
	// counter
	var current=0;


	jQuery( document ).ready(function() {


	jQuery("#stopBTN").click(function(){
		allDone=true;
	jQuery("#error_log").append('<div class="alert alert-danger" role="alert"><strong>STOP</strong><br />Canceled by user</div>');
	});


	jQuery( ".output_ajax" ).each(function( index ) {
	 	console.log( index + ": " + jQuery( this ).attr('id') );
	 	// ajax_transfert({'un':'','deux':''});
	 	toDo.push({'id': jQuery( this ).attr('id'),
	 	           'total':jQuery( this ).data('total'),
	 	           'done':false,
	 	           'nDone':0
	 	       	  });
	});

	nToDo=toDo.length;

	console.log('nb table to do:'+nToDo);
	console.log(toDo);

	doNext();

	});


		// go to next copy request
		function doNext(){
			if(allDone==false){
				console.log('do next  ......');
				if(toDo[current]['total']>toDo[current]['nDone'] && toDo[current]['total']!=0){
				     console.log('.....'+' - item done: '+toDo[current]['nDone']+" / "+toDo[current]['total']);
					// call next copy
					ajax_transfert();
				}else{
					// go to next table
				  	jQuery("#"+toDo[current]['id']).html('<span class="label label-success">OK</span>'+toDo[current]['total']+" Items  / 100%");
					nextTable();
				}
			}


		}


		// go to next table to copy
		function nextTable(){
			jQuery("#tranfertDetail").html((current+1)+' / '+nToDo);
 			console.log('call next table done: '+(current+1)+' / '+nToDo);
			if(current<(nToDo-1)){

				jQuery( "#"+toDo['id'] ).html('ok');
				toDo[current]['done']=true;
				console.log('end table '+toDo[current]['id']);
				current++;
				console.log('start table '+toDo[current]['id']);
				console.log(toDo[current]);
				doNext();
			}else{
				allDone=true;
			}

		}


		function ajax_transfert(){

			var jqxhr = $.ajax({url:self_url+"?ajaxRequest=true&table="+toDo[current]['id']+"&from="+toDo[current]['nDone'],
								dataType:"json" })
			  .done(function(response) {
			  	//console.log('ajax done');
			  	console.log(response);

			  	if(response['status']=='ok'){
				  	toDo[current]['nDone']=response['nDone'];
				  	var percent=toDo[current]['nDone']/toDo[current]['total']*100;
				  	jQuery("#"+toDo[current]['id']).html(toDo[current]['nDone']+" / "+toDo[current]['total']+"  => "+percent+"%");
				  	
				  	doNext();
				}else{
				  	jQuery("#error_log").append(response['error']);
				  	doNext();
				}
			    //alert( "success" );
			  })
			  .fail(function() {
			    //alert( "error" );
			  })
			  .always(function() {
			    //alert( "complete" );
			  });
		}


	</script>


		</body>
	</html>










<?php

$_SESSION['tableList']=$tableList;

} else{// eof / isset($_GET['ajaxRequest']) => ajax call start here


	$tableList=$_SESSION['tableList'];

	$table=$_GET['table'];
	$from=$_GET['from'];

	// ajax actions
	if(is_numeric($from) && array_key_exists($table, $tableList)){
		$retour=data_copy($tableList[$table],$from);
		echo json_encode($retour);
	}else{
		die('wrong parameters');
	}
}






function data_copy($table,$from){
	global $db;
	$retour=array();
	$retour['status']='ok';
	try {   
	        $result = $db['source']['link']->query("SELECT * FROM ".$table['name']." LIMIT ".$from.','.NUMMBER_OF_ROW_TO_COPY);
	        while ($row = $result->fetch(PDO::FETCH_NUM)) {
	        	foreach ($row as $k => $v) {
	        		$row[$k]=str_replace("'", "\'", $v);
	        	}
	            $dataSet[]=implode("','", $row);
	        }
	        $data=" ('".implode("'),('", $dataSet)."')";
	    }
	    catch (PDOException $e) {
	        //echo $e->getMessage();
			$retour['status']='ko';
			$retour['error']='<div class="alert alert-warning" role="alert"><strong>SELECT'.$table['name'].'</strong><br />'."SELECT * FROM ".$table['name']." LIMIT ".$from.'<br /><br />'.$e->getMessage().'</div>';
	    }	

	$sql='INSERT INTO `'.$table['name'].'` (`'.implode('`,`', $table['col']).'`) VALUES '.$data;
	try {   
	       $r = $db['destination']['link']->query($sql);
		}catch(PDOException $e) {
			$retour['status']='ko';
			$retour['error']='<div class="alert alert-danger" role="alert"><strong>INSERT: '.$table['name'].'</strong><br />'.$sql.'<br /><br />'.$e->getMessage().'</div>';

		}

		  
	$retour['nDone']=$from+NUMMBER_OF_ROW_TO_COPY;

		  // debug infos...
		  //  $retour['sql']=$sql;
		  //  $retour['result']=$result;
		//	$retour['table']=$table;

	return $retour;

}


?>
