<?php
	require_once("commonCode.php");
	
	// ***************************
	// Requests made by the client
	
	$delimiterOptions = array('Tab' => "\t", 'Semicolon' => ';', 'Comma' => ',', 'Dot' => '.');
	// need this because the file upload method has to be done by get instead of post
	$action = (isset($_POST['action'])) ? $_POST['action'] : $_GET['action'];
	if(isset($action)){
		$resource = (isset($_POST['resource'])) ? $_POST['resource'] : $_GET['resource'];
		if(!isset($resource) && $action != 'getProject'){
			throw new Exception('Resource not specified');
		}
		
		$userLogin = (isset($_POST['userLogin'])) ? $_POST['userLogin'] : $_GET['userLogin'];
		$userPass = (isset($_POST['userPass'])) ? $_POST['userPass'] : $_GET['userPass'];
		$userId = getUserId($userLogin, $userPass);
		// Need this comparison in case $userId = 0.... Thats php for you...
		if($userId === false){
			throw new Exception('Wrong username or password');
		}
	
		$isResp = isResp($userId, $resource);
		if(!hasPermission($userId, $resource) && $isResp === false){
			throw new Exception("User is not allowed to use this resource");
		}
	
		switch($action){
			case "itemInsertHtml": // Regular user view
				$json->html = itemInsertHtml($userId, $resource, $isResp);
			break;
			
			case "itemManagementHtml": // Manager user view
				$entries = null;
				if(isset($_POST['usePostEntries']) && json_decode($_POST['usePostEntries'])){
					$entries = $_POST['entries'];
				}
				$json->html = itemManagementHtml($userId, $resource, $entries);
			break;
			
			case "associateEntriesAndItems": // Entry and item association
				$json->message = associateEntriesAndItems($userId, $resource, $_POST['entries'], json_decode($_POST['items'], true));
			break;
			
			case "getProject":
				// $sql = "select configParams_value from configParams where configParams_name = 'useProjects'";
				// $prep = dbHelp::query($sql);
				// $row = dbHelp::fetchRowByIndex($prep);
				$needProj = needProject();
				
				// $asUser = $_POST['asUser'];
				if(
					(($asUser = $_POST['asUser']) === null || $asUser == '')
					&&
					(($asUser = $_GET['asUser']) === null || $asUser == '')
				){
					$asUser = $userId;
				}
				
				$stateAndProject = getProjectFromItem($_POST['item'], $asUser);
				// only nag the user, in case the project is null, if the flag 'useProjects' exists and is true
				if($needProj === true && $json->project === null){
					throw new Exception('No project was associated to this item');
				}
				
				if($stateAndProject['isActive'] !== null && $stateAndProject['isActive'] == 0){
					throw new Exception('The project associated to this item has become inactive');
				}
				$json->project = $stateAndProject['project'];
			break;
			
			case "itemInsertOrRemove":
				if(isset($_POST['items']) && isset($_POST['remove'])){
					$json->message = itemInsertOrRemove($_POST['items'], $userId, $resource, json_decode($_POST['remove']), $isResp, $_GET['asUser']);
					$json->selectOptions = getItems($userId, $resource);
				}
			break;
			
			case "emailUsersFromItems":
				$json->mailToUsers = emailUsersFromItems($_POST['items']);
			break;
			
			case "updateSubmittedList":
				if($isResp === false){
					throw new Exception("User does not have permission for this action");
				}
				$json->selectOptions = updateSubmittedList($_POST['asUser'], $resource);
				// $projects_and_default = getProjectsAndDefault($_POST['asUser']);
				// $json->projects = $projects_and_default['projects'];
				// $json->default_project = $projects_and_default['default'];
				$json->default_project = get_default_project($_POST['asUser']);
				$json->projects = get_projects($_POST['asUser']);
			break;

			case "done":
				if($isResp === false){
					throw new Exception("User does not have permission for this action");
				}
				$json->message = done($_POST['entries']);
			break;

			case "upload":
				$json = upload($_POST['lineValue'], $_POST['columnValue'], $delimiterOptions[$_POST['selectDelimeter']], $userId, $resource, isset($_POST['emailRespCheck']), $isResp, $_GET['asUser']);
			break;

			case "getItems":
				$json->items = getItems($userId, $resource);
			break;
		}

		echo json_encode($json);
		exit;
	}
	
	// *************************
	// Html generation goes here
	echo "<link href='../agendo/css/item.css' rel='stylesheet' type='text/css' />";
	echo "<script type='text/javascript' src='../agendo/js/item.js'></script>";
	
	echo "<div id='itemInterfaceDiv'>";
	echo "</div>";
	

	// *********
	// Functions
	function itemInsertHtml($userId, $resource, $isResp){
		global $delimiterOptions;
	
		$html = "";
		$sql = "select resource_name from resource where resource_id = :0";
		$prep = dbHelp::query($sql, array($resource));
		$row = dbHelp::fetchRowByIndex($prep);
		
		$html .= "<a onclick='closeitemInsertDiv();' onmouseover='this.style.cursor=\"pointer\"' style='position:absolute;top:0px;right:5px;color:#bb3322;font-size:16px;'>";
			$html .= "x";
		$html .= "</a>";
		
		$color = "#666666";
		$html .= "<table>";
			$html .= "<tr>";
				$html .= "<td rowspan='2' style='text-align:left;color:".$color.";'>";
					if($isResp !== false){
						$html .= "<label style=''>";
							$html .= "Insert or remove item as user:";
						$html .= "</label>";

						$html .= "<br>";
					
						$sql = "select distinct user_id, user_firstname, user_lastname, user_login, user_login from user, permissions where user_id = permissions_user and permissions_resource = :0 || user_id = :1";
						$prep = dbHelp::query($sql, array($resource, $userId));
						$html .= "<select id='asUserList' class='userList' onchange='fillSubmittedListFromUser();'>";
						while($row = dbHelp::fetchRowByIndex($prep)){
							$html .= "<option value='".$row[0]."'>";
							$html .= $row[1]." ".$row[2]." (".$row[3].")";
							$html .= "</option>";
						}
						$html .= "</select>";
						
						$html .= "<br>";
						$html .= "<br>";
					}

					$html .= "<label>";
						$html .= "New item:";
					$html .= "</label>";
					
					$html .= "<br>";
					
					$html .= "<input class='inputText' type='text' id='itemName' style='float:left;'/>";
					
					$html .= "<br>";
					$html .= "<br>";
					
					$html .= "<label style='float:left;'>";
						$html .= "Submitted items not yet used:";
					$html .= "</label>";

					$html .= "<br>";
					
					// List where the submitted items will show
					$html .= "<select class='selectList' id='submittedItems' multiple='multiple' style='float:left;' onclick='showProject(this.value);'>";
					$html .= "</select>";
					
					// Fills the list with items submitted by the user, not used in a sequencing session
					$html .= "
						<script>
							fillItemsListOptions(".json_encode(getItems($userId, $resource)).");
						</script>
					";
					
				$html .= "</td>";
			
				$html .= "<td style='color:".$color.";text-align:right;vertical-align:top;'>";
					$marginLeft = 20;
					$html .= "
						<input type='button' 
							class='buttons'
							id='insertItemButton' 
							value='Insert Item' 
							onclick='itemInsertOrRemove()' 
							title='Inserts the new item'
							style='margin-top:10px;'
						/>
					";
				
					$html .= "<br>";
					
					$html .= "
						<input type='button' 
							class='buttons'
							id='removeItemButton' 
							value='Remove Item' 
							onclick='itemInsertOrRemove(true)' 
							title='Removes the selected item(s)'
							style='margin-top:10px;'
						/>
					";

					$html .= "<br>";
					
					$html .= "
						<input type='button' 
							class='buttons'
							id='uploadButton'
							value='Import Items' 
							title='Import items from file and send it as attachment to the resource manager'
							style='margin-top:10px;'
							onclick='upload();'
						/>
					";
					
					
					// $projects_and_default = getProjectsAndDefault($userId);
					$default_project = get_default_project($userId);
					$projects = get_projects($userId);
					// if(isset($projects[0])){
					if(use_projects()){
						if($default_project === false){ // should not be necessary, this check is also made in process.php when adding an entry
							$html .= "
								<script>
									showMessage('No projects or no default project associated to the current user department, adding items will not be possible', true);
								</script>
							";
						}
						$html .= "<br>";
						$html .= "<div class='projects' style='margin-top: 10px;'>";
							$html .= "Project: ";
							
							$html .= "<br>";
							$html .= "<select id='projectList' style='width:100%;'>";
							foreach($projects as $project){
								$isSelected = "";
								// if($project['id'] == $default_project){
								if($project['id'] == $default_project['id']){
									$isSelected = "selected='selected'";
								}
								$html .= "<option value='".$project['id']."' ".$isSelected." title='".$project['name']."'>".$project['name']."</option>";
							}
							$html .= "</select>";
						$html .= "</div>";
							
						
						// to be implemented later?
						// $html .= "<br>";
						// $html .= "
							// <input type='button'
								// class='buttons'
								// id='backButton'
								// value='Save'
								// onclick='associateItemToProj();'
								// title='Associates a project to the selected item'
								// style='margin-top: 10px;'
							// />
						// ";
						
					}
					
				$html .= "</td>";
			$html .= "</tr>";
			
			$html .= "<tr>";
				// Shows the possible states
				$html .= "<td style='vertical-align:bottom;text-align:right;color:".$color.";'>";
					if($isResp !== false){
						$html .= "
							<input type='button' 
								class='buttons'
								id='backButton' 
								value='Back' 
								onclick='back();' 
								title='Return to the entry association screen'
								style='margin-bottom: 10px;'
							/>
						";
						
						$html .= "<br>";
					}
					
					$heightAndWidth = 14;
					$topMargin = 5;
					$rightMargin = 5;
					$sql = "select item_state_id, item_state_name from item_state where item_state_id != 3"; // remove the unecessary states in the query here
					$prep = dbHelp::query($sql);
					while($row = dbHelp::fetchRowByIndex($prep)){
						$html .= "<div style='text-align:right;float:right;margin-left:20px;'>";
							$html .= "<label style='margin-right:".$rightMargin."px;'>".$row[1]."</label>";
							$html .= "<div class='optionState".$row[0]."' style='float:right;width:".$heightAndWidth."px;height:".$heightAndWidth."px;'></div>";
						$html .= "</div>";
					}
					$html .= "<br>";
					
				$html .= "</td>";
			$html .= "</tr>";
				
			$html .= "<tr>";
				$html .= "<td style='color:".$color.";text-align:left;'>";
					$html .= "
						<label>
							<input type='checkbox' 
								id='selectAllItemsCheck' 
								onclick='selectAllItems();' 
								title='Selects all items that are not in use'
							/>
							&nbspSelect All
						</label>
					";
				$html .= "</td>";
				
				$html .= "<td>";
				$html .= "</td>";
			$html .= "</tr>";

			$html .= "<tr>";
				$html .= "<td style='color:".$color.";text-align:center;' colspan='2'>";
					$html .= "<iframe id='submitIframe' name='submitIframe' style='display:none;'></iframe>";
					// $html .= "<iframe id='submitIframe' name='submitIframe'></iframe>";

								// $html .= "<br>";
					$html .= "
						<form id='uploadFileForm' method='post' enctype='multipart/form-data' style='margin:auto;text-align:center;' target='submitIframe'>
					";
							
					$html .= "<div style='text-align:right;position:relative;'>";
								$html .= "
									<label title='Delimiter character used to distinguish columns'>
										Delimiter:&nbsp
										<select id='selectDelimeter' name='selectDelimeter' style='width:80px;margin-top:5px;'>";
											foreach($delimiterOptions as $key => $value){
												$html .= "
													<option value='".$key."'>
														".$key." ('".$value."')
													</option>
												";
											}
								$html .= "
										</select>
									</label>
								";
						$html .= "
							<input type='file'
								name='file'
								id='file' 
							/>
						";
					$html .= "</div>";
								
						$html .= "<br>";
								
						// $html .= "<div style='margin-top:20px;'>";		
						$html .= "<div style='text-align:left;position:relative;'>";		
							// $html .= "<div style='float:left;text-align:left;'>";
								$html .= "
									<label>
										Info starts at row:&nbsp
										<input type='input' 
											id='lineValue'
											name='lineValue'
											value='1' 
											style='width:20px;'
										/>
									</label>
								";

								// $html .= "<br>";
								$html .= "&nbsp";
										
								$html .= "
									<label>
										Info is in column:&nbsp
										<input type='input' 
											id='columnValue'
											name='columnValue'
											value='0' 
											style='width:20px;margin-top:5px;'
										/>
									</label>
								";
								
						$html .= "
							<label style='position:absolute;bottom:0px;right:0px;' title='Email the selected file to the resource manager'>
								<input type='checkbox' 
									id='emailRespCheck' 
									name='emailRespCheck' 
									checked
								/>
								&nbspEmail
							</label>
						";
							// $html .= "</div>";
							
							// $html .= "<div style='float:right;text-align:right;border:1px solid black;'>";

							// $html .= "</div>";
						$html .= "</div>";
					$html .= "</form>";
				$html .= "</td>";
			$html .= "</tr>";
		$html .= "</table>";
			
		return $html;
	}
	
	function itemInsertOrRemove($items, $userId, $resource, $remove, $isResp, $asUser){
		if(!isset($items) || sizeOf($items) == 0){
			throw new Exception("No items were detected");
		}
		else{
			if($remove){
				$extraSql = "";
				$sql = "delete from item where item_id = :0 and item_resource = :1";
				$sqlArray = array("nothingYet", $resource);
				if($isResp === false){
					$sqlArray[2] = $userId;
					$sql .= " and item_user = :2";
				}
				
				foreach($items as $item){
					if(empty($item)){
						throw new Exception("Empty item name was found");
					}
					$sqlArray[0] = $item;
					$prep = dbHelp::query($sql, $sqlArray);
				}
				return "Item(s) Removed";
			}
			else{
				if(empty($items[0])){
					throw new Exception("Empty item name");
				}
				
				$user = $userId;
				if($isResp !== false && isset($asUser)){
					$user = $asUser;
				}
				
				$project = $_POST['project'];
				if(!isset($project) || $project == ''){
					$project = $_GET['project'];
				}
				
				// $projects_and_default = getProjectsAndDefault($user);
				
				$sql = "insert into item values(null, :0, :1, 1, :2, null)";
				$sqlArray = array($items[0], $user, $resource);
				// if($projects_and_default['projects'] !== null){
				if(use_projects()){
					// $project_ok = inProjects($project, $projects_and_default['projects']);
					// if($project_ok === false){
					if(!valid_project($user, $project)){
						throw new Exception('User not allowed to use this project or project not defined');
					}
					$sqlArray[] = $project;
					$sql = "insert into item values(null, :0, :1, 1, :2, :3)";
				}
				
				
				$prep = dbHelp::query($sql, $sqlArray);
				return "Item Inserted";
			}
		}
	}
	
	function itemManagementHtml($userId, $resource, $entries = null){
		$html .= "<a onclick='closeitemInsertDiv();' onmouseover='this.style.cursor=\"pointer\"' style='position:absolute;top:0px;right:5px;color:#bb3322;font-size:16px;'>";
			$html .= "x";
		$html .= "</a>";
		
		$html .= "<div style='text-align:left;float:left;'>";
			$html .= "<label>";
				$html .= "Available items:";
			$html .= "</label>";
			
			$html .= "<br>";

			$html .= "<select class='selectListManager' id='submittedItems' multiple='multiple'>";
			$html .= "</select>";
		$html .= "</div>";
				$html .= "&nbsp";
	
		$html .= "<div style='margin-top:20px;margin-left:20px;margin-right:20px;float:left;'>";
			$html .= "<input type='button' value=' <<- ' title='Remove all' onclick='swapAll(\"lockedItems\", \"submittedItems\");'/>";
			$html .= "&nbsp";
			$html .= "<input type='button' value=' ->> ' title='Add all' onclick='swapAll(\"submittedItems\", \"lockedItems\");'/>";
			$html .= "<br>";
			$html .= "<input type='button' value=' <- ' title='Remove selected' onclick='swapSelected(\"lockedItems\", \"submittedItems\");'/>";
			$html .= "&nbsp";
			$html .= "<input type='button' value=' -> ' title='Add selected' onclick='swapSelected(\"submittedItems\", \"lockedItems\");'/>";
			$html .= "<br>";
			$html .= "<input class='buttons' type='button' id='newItemButton' value='New Item' onclick='newItem();' style='margin-top:25px;'/>";
			$html .= "<br>";
			$html .= "
				<input 
					class='buttons' 
					type='button' 
					id='saveButton' 
					value='Save' 
					title='Save current sample list' 
					onclick='saveItemList();' 
					style='margin:auto;margin-top:17px;'
				/>
			";
		
			
		$html .= "</div>";

		$html .= "<div style='float:left;text-align:left;'>";
			$html .= "<label>";
				$html .= "Locked items:";
			$html .= "</label>";
			
			$html .= "<br>";

			$html .= "<select class='selectListManager' id='lockedItems' multiple='multiple'>";
			$html .= "</select>";
		$html .= "</div>";

		$html .= "<br>";
		
		$marginTop = 10;
		$html .= "
			<input 
				class='buttons' 
				type='button' 
				id='cancelButton' 
				value='Delete' 
				title='Cancels the operation, closes the menu and deletes the entry(ies)' 
				onclick='closeitemInsertDiv(true);' 
				style='float:left;margin-top:".$marginTop."px;'
			/>
		";
		
		$html .= "
			<input 
				class='buttons' 
				type='button' 
				id='emailButton' 
				onclick='emailUsersFromItems();' 
				value='Email' 
				title='Email the users of the selected locked items' 
				style='float:right;margin-top:".$marginTop."px;'
			/>
		";
	
		// $html .= "<br>";
		
		// $html .= "
			// <input 
				// class='buttons' 
				// type='button' 
				// id='doneButton' 
				// onclick='done();' 
				// value='Confirm' 
				// title='Changes the state of the entry to confirmed and closes this menu'
				// style='margin:auto;margin-top:".$marginTop."px;'
			// />
		// ";
		
		$html .= "<br>";
		
		$html .= "<div style='float:left;text-align:left;width:100%;margin-top:".$marginTop."px;'>";
			$html .= "<label>";		
				$html .= "Email subject:";		
			$html .= "</label>";
			
			$html .= "<br>";
			
			$html .= "<input type='input' id='emailSubject' value='Sequencing' style='width:100%;'/>"; // get the value from the bd later?

			$html .= "<br>";
			$html .= "<br>";
			
			$html .= "<label>";		
				$html .= "Email text:";		
			$html .= "</label>";
			
			$html .= "<br>";

			$html .= "<textarea style='width:100%;' id='emailBody'>";
				$html .= "The samples you submitted were used.";
			$html .= "</textarea>";
		$html .= "</div>";

		// Fills the list with items submitted by the user, not used in a sequencing session
		$html .= "
			<script>
				fillItemsListOptions(".json_encode(getItems($userId, $resource, $entries)).");
			</script>
		";
		
		return $html;
	}
	
	
	// Returns a user id either from the session var or from the login and password text fields
	function getUserId($userLogin, $userPass){
		if(isset($_SESSION['user_id'])){
			return $_SESSION['user_id'];
		}
		
		$sql = "select user_id from ".dbHelp::getSchemaName().".user where user_login = :0 and user_passwd = :1";
		$prep = dbHelp::query($sql, array($userLogin, cryptPassword($userPass)));
		if(isset($userLogin) && isset($userPass) && dbHelp::numberOfRows($prep) > 0){
			$row = dbHelp::fetchRowByIndex($prep);
			return $row[0];
		}
		
		return false;
	}
	
	// Returns the items id, name and state associated to the user and resource (only items with the state 1 and 2)
	function getItems($userId, $resource, $entriesArray = null){
		$itemsArray = array();
		
		$select = "
			select 
				item_id
				, item_name
				, item_state 
		";
		
		$from = "
			from 
				item
		";
		
		$where = "
			where
				item_resource = :0
				and item_state in (1, 2)
		";
		
		$orderBy = "
			order by 
				item_state
				, item_name
		";
			
		$sqlArray = array($resource);
		$inData = dbHelp::inDataFromArray($entriesArray);
		if(isResp($userId, $resource) !== false && $inData !== false){ // its the resource manager and theres associated entries
			$select .= "
				, user_firstname
				, user_lastname
			";
			
			$from .= "
				, user
			";
			
			$where = "
				where
					item_resource = :0
					and item_state = 1
					and user_id = item_user
			";
			
			$fromEntry = $from."
				, item_assoc
			";
			
			$whereEntry = "
				where 
					item_assoc_entry in ".$inData['inData']." 
					and item_id = item_assoc_item
					and item_resource = :".($inData['size'])."
					and item_state in (2, 3)
					and user_id = item_user
			";
			$entriesArray[] = $resource; // pushing the resource in the entries array to then send to PDO for the sql query
			$sqlEntry = $select." ".$fromEntry." ".$whereEntry." ".$orderBy;
			$prepEntry = dbHelp::query($sqlEntry, $entriesArray);
			while($rowEntry = dbHelp::fetchRowByIndex($prepEntry)){
				$itemsArray[$rowEntry[0]] = array('name' => $rowEntry[1]." - ".$rowEntry[3]." ".$rowEntry[4], 'state' => $rowEntry[2]);
			}
			
			$sql = $select." ".$from." ".$where." ".$orderBy;
			$prep = dbHelp::query($sql, $sqlArray);
			while($row = dbHelp::fetchRowByIndex($prep)){
				$itemsArray[$row[0]] = array('name' => utf8_decode($row[1])." - ".$row[3]." ".$row[4], 'state' => $row[2]);
			}
		}
		else{ // regular user, needs sorting of the samples by user
			$where .= "
				and item_user = :1
			";
			$sqlArray[] = $userId;
			
			$sql = $select." ".$from." ".$where." ".$orderBy;
			$prep = dbHelp::query($sql, $sqlArray);
			while($row = dbHelp::fetchRowByIndex($prep)){
				$itemsArray[$row[0]] = array('name' => $row[1], 'state' => $row[2]);
			}
		}

		return $itemsArray;
	}
	
	// Makes the association between items and an entry, an entry as x number of items and changes the state of the items to locked (save button)
	function associateEntriesAndItems($userId, $resource, $entries, $items){
		if(isResp($userId, $resource) === false){
			throw new Exception("User not allowed for this operation");
		}
		
		if(!isset($items)){
			throw new Exception("There are no items");
		}

		if(!$inSql = dbHelp::inDataFromArray($entries)){
			throw new Exception("Couldn't get entries");
		}
		
		foreach($entries as $entry){ // all entries
			foreach($items as $itemState=>$itemArray){ // all item states
				foreach($itemArray as $item){ // all item that belong to a state
					updateOrInsertItemOnEntry($entry, json_decode($item, true), $itemState);
				}
			}
		}
		
		$sql = "update entry set entry_status = 2 where entry_id in ".$inSql['inData'];
		$prep = dbHelp::query($sql, $entries);
		
		return "Items saved";
	}
	
	// Aux function that either updates the state of an item or inserts it in the assoc table associated to an entry
	function updateOrInsertItemOnEntry($entry, $item, $itemState){
		$itemIdEntryArray = array($entry, $item['id']);
		$stateArray = array('locked' => 2, 'submitted' => 1);
		$sql = "select item_assoc_id, item_state from item, item_assoc where item_assoc_entry = :0 and item_assoc_item = :1";
		$prep = dbHelp::query($sql, $itemIdEntryArray);
		$row = dbHelp::fetchRowByIndex($prep);

		// if the item is in the locked section and not associated to an entry
		if($stateArray[$itemState] == 2){
			if(!isset($row[0])){
				$sql = "insert into item_assoc values(null, :0, :1)";
				dbHelp::query($sql, $itemIdEntryArray);
			}
			
			$sql = "update item set item_state = 2 where item_id = :0";
			dbHelp::query($sql, array($item['id']));
		}
		// if the item is in the locked section and not associated to an entry
		elseif(isset($row[0]) && $stateArray[$itemState] == 1){
			// changes the item status to available
			$sqlItem = "update item set item_state = 1 where item_id = :0 and item_state != 1";
			$prepItem = dbHelp::query($sqlItem, array($item['id']));
			
			// deletes the association of that item with the entry
			$sqlItem = "delete from item_assoc where item_assoc_id = :0";
			$prepItem = dbHelp::query($sqlItem, array($row[0]));
		}
	}
	
	function emailUsersFromItems($items){
		if(!$inSql = dbHelp::inDataFromArray($items)){
			throw new Exception("Couldn't get items data");
		}
		
		$mailToUsers = "";
		$sql = "select distinct user_email from item, user where user_id = item_user and item_id in ".$inSql['inData'];
		$prep = dbHelp::query($sql, $items);
		if(dbHelp::numberOfRows($prep) > 0){
			$row = dbHelp::fetchRowByIndex($prep);
			$mailToUsers .= $row[0];
			
			while($row = dbHelp::fetchRowByIndex($prep)){
				$mailToUsers .= ",".$row[0];
			}
			return $mailToUsers;
		}
		else{
			throw new Exception("No users found");
		}
		
	}
	
	function updateSubmittedList($asUser, $resource){
		return getItems($asUser, $resource);
	}
	
	function done($entries){
		if(!$inSql = dbHelp::inDataFromArray($entries)){
			throw new Exception("Couldn't get entries");
		}
		
		// sets the entry as confirmed
		$sql = "update entry set entry_status = 1 where entry_id in ".$inSql['inData'];
		$prep = dbHelp::query($sql, $entries);
		if(dbHelp::numberOfRows($prep) == 0){
			throw new Exception("No changes were made to the entry(ies)");
		}
		
		// sets the items as used
		$sql = "update item, item_assoc set item_state = 3 where item_assoc_entry in ".$inSql['inData']." and item_id = item_assoc_item";
		$prep = dbHelp::query($sql, $entries);

		return "Entry(ies) confirmed";
	}
	
	function upload($line, $column, $delimiter, $userId, $resource, $emailRespCheck, $isResp, $asUser){
		$maxFileSize = 100000; // bytes

		// try catch needed here due to an iframe being used
		try{
			// mac fix for line end problems
			ini_set("auto_detect_line_endings", true);
			
			// mimetype filtering disabled for now, possible security issues?
			// if(
				// strpos($_FILES["file"]["type"], "excel") === false
				// && $_FILES["file"]["type"] != "text/plain"
				// && $_FILES["file"]["type"] != "text/csv"
			// ){
				// throw new Exception("Invalid file format");
			// }
			
			if($_FILES["file"]["size"] > $maxFileSize){
				throw new Exception("File too big");
			}
			
			if($_FILES["file"]["error"] > 0){
				throw new Exception($_FILES["file"]["error"]);
			}

			// Opens the temp file
			$fileData = fopen($_FILES["file"]["tmp_name"], 'r');
			
			// Checks if the file opened successfully
			if($fileData){
				// Moves the "cursor" to the desired line
				for($i=0; $i<$line; $i++){
					fgetss($fileData);
				}
			
				$json->message = "Items imported successfully";
				$i = 0;
				while($line = fgetss($fileData)){
					$lineArray = explode($delimiter, trim($line));
					$item = str_replace("\"", "", $lineArray[$column]); // remove possible "
					// itemInsertOrRemove(array($item), $userId, $resource, false, $isResp, $_GET['asUser']);
					itemInsertOrRemove(array($item), $userId, $resource, false, $isResp, $asUser);
					$i++;
				}
				fclose($fileData);
				
				if($i == 0){
					throw new Exception('Could not find any items');
				}
				
				if($emailRespCheck){
					$sql = "select user_email, user_firstname, user_lastname, user_login from ".dbHelp::getSchemaName().".user where user_id = :0";
					$prep = dbHelp::query($sql, array($userId));
					$rowUser = dbHelp::fetchRowByIndex($prep);

					$sql = "select user_email, resource_name from ".dbHelp::getSchemaName().".user, resource where user_id = resource_resp and resource_id = :0";
					$prep = dbHelp::query($sql, array($resource));
					$rowResp = dbHelp::fetchRowByIndex($prep);

					$message = "User ".$rowUser[1]." ".$rowUser[2]."(".$rowUser[3].") imported the samples in this file.";
					$subject = "Item(s) upload for resource '".$rowResp[1]."'";
					$mailObject = getMailObject($subject, $rowResp[0], $message, $rowUser[1]." ".$rowUser[2], $rowUser[0]);
					$mailObject->AddAttachment($_FILES["file"]["tmp_name"], $rowUser[1]."_".$rowUser[2].".csv");
					sendMailObject($mailObject);
					$json->message .= " and the selected file was emailed";
				}
			}
		}
		catch(Exception $e){
			$json->message = $e->getMessage();
			$json->isError = true;
		}

		return $json;
	}
	
	function getProjectFromItem($item, $user_id){
		$project = null;
		$sql = "
			select
				proj_dep_assoc_active, project_id
			from
				item join project on project_id = item_project
				join proj_dep_assoc on proj_dep_assoc_project = project_id
				join ".dbHelp::getSchemaName().".user on proj_dep_assoc_department = user_dep
			where
				item_id = :0
				and user_id = :1
		";
		$prep = dbHelp::query($sql, array($item, $user_id));
		$row = dbHelp::fetchRowByIndex($prep);
		return array('isActive' => $row[0], 'project' => $row[1]);
	}
?>