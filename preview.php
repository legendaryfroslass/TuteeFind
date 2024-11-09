<?php
	
	include 'includes/session.php';
	include 'includes/slugify.php';

	$output = array('error'=>false,'list'=>'');

	$sql = "SELECT * FROM tutor";
	$query = $conn->query($sql);

	while($row = $query->fetch_assoc()){
		$tutor = slugify($row['description']);
		$pos_id = $row['id'];
		if(isset($_POST[$tutor])){
			if($row['max_vote'] > 1){
				if(count($_POST[$tutor]) > $row['max_vote']){
					$output['error'] = true;
					$output['message'][] = '<li>You can only choose '.$row['max_vote'].' candidates for '.$row['description'].'</li>';
				}
				else{
					foreach($_POST[$tutor] as $key => $values){
						$sql = "SELECT * FROM tutee WHERE id = '$values'";
						$cmquery = $conn->query($sql);
						$cmrow = $cmquery->fetch_assoc();
						$output['list'] .= "
							<div class='row votelist'>
		                      	<span class='col-sm-4'><span class='pull-right'><b>".$row['description']." :</b></span></span> 
		                      	<span class='col-sm-8'>".$cmrow['firstname']." ".$cmrow['lastname']."</span>
		                    </div>
						";
					}

				}
				
			}
			else{
				$tutee = $_POST[$tutor];
				$sql = "SELECT * FROM tutee WHERE id = '$tutee'";
				$csquery = $conn->query($sql);
				$csrow = $csquery->fetch_assoc();
				$output['list'] .= "
					<div class='row votelist'>
                      	<span class='col-sm-4'><span class='pull-right'><b>".$row['description']." :</b></span></span> 
                      	<span class='col-sm-8'>".$csrow['firstname']." ".$csrow['lastname']."</span>
                    </div>
				";
			}

		}
		
	}

	echo json_encode($output);


?>